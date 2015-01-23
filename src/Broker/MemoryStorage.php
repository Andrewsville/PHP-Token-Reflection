<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Broker;

use ApiGen\TokenReflection;
use ApiGen\TokenReflection\Exception;
use ApiGen\TokenReflection\Exception\BrokerException;
use ApiGen\TokenReflection\Exception\FileProcessingException;
use ApiGen\TokenReflection\Exception\RuntimeException;
use ApiGen\TokenReflection\Php;
use ApiGen\TokenReflection\Php\ReflectionClass;
use ApiGen\TokenReflection\Reflection\ReflectionFile;
use ApiGen\TokenReflection\Reflection\ReflectionNamespace;
use ApiGen\TokenReflection\ReflectionClassInterface;
use ApiGen\TokenReflection\ReflectionConstantInterface;
use ApiGen\TokenReflection\ReflectionFunctionInterface;
use ApiGen\TokenReflection\ReflectionNamespaceInterface;
use ApiGen\TokenReflection\Stream\FileStream;
use ApiGen\TokenReflection\Stream\StreamBase;


class MemoryStorage implements StorageInterface
{

	/**
	 * List of declared class names.
	 *
	 * @var array
	 */
	private $declaredClasses = [];

	/**
	 * @var array|ReflectionNamespaceInterface[]
	 */
	private $namespaces = [];

	/**
	 * @var ReflectionConstantInterface[]
	 */
	private $allConstants;

	/**
	 * @var array|ReflectionClassInterface[]
	 */
	private $allClasses;

	/**
	 * All tokenized functions cache.
	 *
	 * @var ReflectionFunctionInterface[]
	 */
	private $allFunctions;

	/**
	 * Token streams storage.
	 *
	 * @var array
	 */
	private $tokenStreams = [];

	/**
	 * Processed files storage.
	 *
	 * @var array
	 */
	private $files = [];

	/**
	 * @var Broker
	 */
	private $broker;


	/**
	 * Returns if a file with the given filename has been processed.
	 *
	 * @param string $fileName File name
	 * @return bool
	 */
	public function hasFile($fileName)
	{
		return isset($this->files[$fileName]);
	}


	/**
	 * Returns a file reflection.
	 *
	 * @param string $fileName File name
	 * @return ReflectionFile
	 * @throws BrokerException If the requested file has not been processed
	 */
	public function getFile($fileName)
	{
		if ( ! $this->hasFile($fileName)) {
			throw new BrokerException(sprintf('File "%s" has not been processed.', $fileName), BrokerException::DOES_NOT_EXIST);
		}
		return $this->files[$fileName];
	}


	/**
	 * Returns file reflections.
	 *
	 * @return array
	 */
	public function getFiles()
	{
		return $this->files;
	}


	/**
	 * Returns if there was such namespace processed (FQN expected).
	 *
	 * @param string $namespaceName
	 * @return bool
	 */
	public function hasNamespace($namespaceName)
	{
		return isset($this->namespaces[ltrim($namespaceName, '\\')]);
	}


	/**
	 * Returns a reflection object of the given namespace.
	 *
	 * @param string $namespaceName
	 * @return ReflectionNamespaceInterface
	 */
	public function getNamespace($namespaceName)
	{
		if ( ! isset($this->namespaces[ReflectionNamespace::NO_NAMESPACE_NAME])) {
			$this->namespaces[ReflectionNamespace::NO_NAMESPACE_NAME] = new ReflectionNamespace(ReflectionNamespace::NO_NAMESPACE_NAME, $this->broker);
		}
		$namespaceName = ltrim($namespaceName, '\\');
		if ( ! $this->hasNamespace($namespaceName)) {
			throw new BrokerException(sprintf('Namespace %s does not exist.', $namespaceName), BrokerException::DOES_NOT_EXIST);
		}
		return $this->namespaces[$namespaceName];
	}


	/**
	 * @return ReflectionNamespaceInterface[]
	 */
	public function getNamespaces()
	{
		return $this->namespaces;
	}


	/**
	 * Returns if there was such class processed (FQN expected).
	 *
	 * @param string $className
	 * @return bool
	 */
	public function hasClass($className)
	{
		$className = ltrim($className, '\\');
		if ($pos = strrpos($className, '\\')) {
			$namespace = substr($className, 0, $pos);
			if ( ! isset($this->namespaces[$namespace])) {
				return FALSE;
			}
			$namespace = $this->getNamespace($namespace);
			$className = substr($className, $pos + 1);
		} else {
			$namespace = $this->getNamespace(ReflectionNamespace::NO_NAMESPACE_NAME);
		}
		return $namespace->hasClass($className);
	}


	/**
	 * Returns a reflection object of the given class (FQN expected).
	 *
	 * @param string $className
	 * @return ReflectionClassInterface|NULL
	 */
	public function getClass($className)
	{
		if (empty($this->declaredClasses)) {
			$this->declaredClasses = array_flip(array_merge(get_declared_classes(), get_declared_interfaces()));
		}
		$className = ltrim($className, '\\');
		try {
			$namespaceReflection = $this->getNamespace(
				($boundary = strrpos($className, '\\'))
					// Class within a namespace
					? substr($className, 0, $boundary)
					// Class without a namespace
					: ReflectionNamespace::NO_NAMESPACE_NAME
			);
			return $namespaceReflection->getClass($className);

		} catch (Exception\BaseException $e) {
			if (isset($this->declaredClasses[$className])) {
				$reflection = new ReflectionClass($className, $this->broker);
				if ($reflection->isInternal()) {
					return $reflection;
				}
			}
			return NULL;
		}
	}


	/**
	 * Returns all classes from all namespaces.
	 *
	 * @param int $type Returned class types (multiple values may be OR-ed)
	 * @return ReflectionClassInterface[]
	 */
	public function getClasses($type = self::TOKENIZED_CLASSES)
	{
		if ($this->allClasses === NULL) {
			$this->allClasses = $this->parseClassLists();
		}
		$result = [];
		foreach ($this->allClasses as $classType => $classes) {
			if ($type & $classType) {
				$result = array_merge($result, $classes);
			}
		}
		return $result;
	}


	/**
	 * Returns if there was such constant processed (FQN expected).
	 *
	 * @param string $constantName
	 * @return bool
	 */
	public function hasConstant($constantName)
	{
		$constantName = ltrim($constantName, '\\');
		if ($pos = strpos($constantName, '::')) {
			$className = substr($constantName, 0, $pos);
			$constantName = substr($constantName, $pos + 2);
			if ( ! $this->hasClass($className)) {
				return FALSE;
			}
			$parent = $this->getClass($className);

		} else {
			if ($pos = strrpos($constantName, '\\')) {
				$namespace = substr($constantName, 0, $pos);
				if ( ! $this->hasNamespace($namespace)) {
					return FALSE;
				}
				$parent = $this->getNamespace($namespace);
				$constantName = substr($constantName, $pos + 1);

			} else {
				$parent = $this->getNamespace(ReflectionNamespace::NO_NAMESPACE_NAME);
			}
		}
		return $parent->hasConstant($constantName);
	}


	/**
	 * Returns a reflection object of a constant (FQN expected).
	 *
	 * @param string $constantName Constant name
	 * @return ApiGen\TokenReflection\IReflectionConstant
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If the requested constant does not exist.
	 */
	public function getConstant($constantName)
	{
		static $declared = [];
		if (empty($declared)) {
			$declared = get_defined_constants();
		}
		if ($boundary = strpos($constantName, '::')) {
			// Class constant
			$className = substr($constantName, 0, $boundary);
			$constantName = substr($constantName, $boundary + 2);
			return $this->getClass($className)->getConstantReflection($constantName);
		}
		try {
			$constantName = ltrim($constantName, '\\');
			if ($boundary = strrpos($constantName, '\\')) {
				$ns = $this->getNamespace(substr($constantName, 0, $boundary));
				$constantName = substr($constantName, $boundary + 1);

			} else {
				$ns = $this->getNamespace(ReflectionNamespace::NO_NAMESPACE_NAME);
			}
			return $ns->getConstant($constantName);

		} catch (Exception\BaseException $e) {
			if (isset($declared[$constantName])) {
				$reflection = new Php\ReflectionConstant($constantName, $declared[$constantName], $this->broker);
				if ($reflection->isInternal()) {
					return $reflection;
				}
			}
			throw new BrokerException(sprintf('Constant %s does not exist.', $constantName), BrokerException::DOES_NOT_EXIST);
		}
	}


	/**
	 * Returns all constants from all namespaces.
	 *
	 * @return ReflectionConstantInterface[]
	 */
	public function getConstants()
	{
		if ($this->allConstants === NULL) {
			$this->allConstants = [];
			foreach ($this->namespaces as $namespace) {
				foreach ($namespace->getConstants() as $constant) {
					$this->allConstants[$constant->getName()] = $constant;
				}
			}
		}
		return $this->allConstants;
	}


	/**
	 * Returns if there was such function processed (FQN expected).
	 *
	 * @param string $functionName
	 * @return bool
	 */
	public function hasFunction($functionName)
	{
		$functionName = ltrim($functionName, '\\');
		if ($pos = strrpos($functionName, '\\')) {
			$namespace = substr($functionName, 0, $pos);
			if ( ! isset($this->namespaces[$namespace])) {
				return FALSE;
			}
			$namespace = $this->getNamespace($namespace);
			$functionName = substr($functionName, $pos + 1);
		} else {
			$namespace = $this->getNamespace(ReflectionNamespace::NO_NAMESPACE_NAME);
		}
		return $namespace->hasFunction($functionName);
	}


	/**
	 * Returns a reflection object of a function (FQN expected).
	 *
	 * @param string $functionName
	 * @return ReflectionFunctionInterface
	 * @throws RuntimeException If the requested function does not exist.
	 */
	public function getFunction($functionName)
	{
		static $declared = [];
		if (empty($declared)) {
			$functions = get_defined_functions();
			$declared = array_flip($functions['internal']);
		}
		$functionName = ltrim($functionName, '\\');
		try {
			$namespaceReflection = $this->getNamespace(
				($boundary = strrpos($functionName, '\\'))
					// Function within a namespace
					? substr($functionName, 0, $boundary)
					// Function wihout a namespace
					: ReflectionNamespace::NO_NAMESPACE_NAME
			);
			return $namespaceReflection->getFunction($functionName);

		} catch (Exception\BaseException $e) {
			if (isset($declared[$functionName])) {
				return new Php\ReflectionFunction($functionName, $this->broker);
			}
			throw new BrokerException(sprintf('Function %s does not exist.', $functionName), BrokerException::DOES_NOT_EXIST);
		}
	}


	/**
	 * Returns all functions from all namespaces.
	 *
	 * @return ReflectionFunctionInterface[]
	 */
	public function getFunctions()
	{
		if ($this->allFunctions === NULL) {
			$this->allFunctions = [];
			foreach ($this->namespaces as $namespace) {
				foreach ($namespace->getFunctions() as $function) {
					$this->allFunctions[$function->getName()] = $function;
				}
			}
		}
		return $this->allFunctions;
	}


	/**
	 * Returns if the given file was already processed.
	 *
	 * @param string $fileName
	 * @return bool
	 */
	public function isFileProcessed($fileName)
	{
		return isset($this->tokenStreams[realpath($fileName)]);
	}


	/**
	 * Returns an array of tokens for a particular file.
	 *
	 * @param string $fileName
	 * @return StreamBase
	 * @throws BrokerException If the requested file was not processed.
	 */
	public function getFileTokens($fileName)
	{
		$realName = realpath($fileName);
		if ( ! isset($this->tokenStreams[$realName])) {
			throw new BrokerException(sprintf('File "%s" was not processed yet.', $fileName), BrokerException::DOES_NOT_EXIST);
		}
		return $this->tokenStreams[$realName] === TRUE ? new FileStream($realName) : $this->tokenStreams[$realName];
	}


	/**
	 * Adds a file to the backend storage.
	 *
	 * @return MemoryStorage
	 */
	public function addFile(StreamBase $tokenStream, ReflectionFile $file)
	{
		$this->tokenStreams[$file->getName()] = $tokenStream;
		$this->files[$file->getName()] = $file;
		$errors = [];
		foreach ($file->getNamespaces() as $fileNamespace) {
			try {
				$namespaceName = $fileNamespace->getName();
				if ( ! isset($this->namespaces[$namespaceName])) {
					$this->namespaces[$namespaceName] = new ReflectionNamespace($namespaceName, $file->getBroker());
				}
				$this->namespaces[$namespaceName]->addFileNamespace($fileNamespace);

			} catch (FileProcessingException $e) {
				$errors = array_merge($errors, $e->getReasons());

			} catch (\Exception $e) {
				echo $e->getTraceAsString();
				die($e->getMessage());
			}
		}
		// Reset all-*-cache
		$this->allClasses = NULL;
		$this->allFunctions = NULL;
		$this->allConstants = NULL;
		if ( ! empty($errors)) {
			throw new FileProcessingException($errors, $file);
		}
		return $this;
	}


	/**
	 * @param Broker $broker
	 * @return MemoryStorage
	 */
	public function setBroker(Broker $broker)
	{
		$this->broker = $broker;
		return $this;
	}


	/**
	 * Returns the reflection broker instance.
	 *
	 * @return Broker $broker Reflection broker
	 */
	public function getBroker()
	{
		return $this->broker;
	}


	/**
	 * Prepares and returns used class lists.
	 *
	 * @return array
	 */
	protected function parseClassLists()
	{
		// Initialize the all-classes-cache
		/** @var ReflectionClassInterface[][] $allClasses */
		$allClasses = [
			self::TOKENIZED_CLASSES => [],
			self::INTERNAL_CLASSES => [],
			self::NONEXISTENT_CLASSES => []
		];
		foreach ($this->namespaces as $namespace) {
			foreach ($namespace->getClasses() as $class) {
				$allClasses[self::TOKENIZED_CLASSES][$class->getName()] = $class;
			}
		}
		foreach ($allClasses[self::TOKENIZED_CLASSES] as $className => $class) {
			foreach (array_merge($class->getParentClasses(), $class->getInterfaces()) as $parent) {
				if ($parent->isInternal()) {
					$allClasses[self::INTERNAL_CLASSES][$parent->getName()] = $parent;
				} elseif ( ! $parent->isTokenized()) {
					$allClasses[self::NONEXISTENT_CLASSES][$parent->getName()] = $parent;
				}
			}
		}
		return $allClasses;
	}

}
