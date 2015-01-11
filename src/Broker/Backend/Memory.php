<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */
namespace ApiGen\TokenReflection\Broker\Backend;

use ApiGen\TokenReflection;
use ApiGen\TokenReflection\Stream\FileStream;
use ApiGen\TokenReflection\Exception;
use ApiGen\TokenReflection\Broker;
use ApiGen\TokenReflection\Php;
use ApiGen\TokenReflection\Dummy;


/**
 * Memory broker backend.
 *
 * Stores parsed reflection objects in memory.
 */
class Memory implements Broker\Backend
{

	/**
	 * List of declared class names.
	 *
	 * @var array
	 */
	private $declaredClasses = [];

	/**
	 * Namespaces storage.
	 *
	 * @var array
	 */
	private $namespaces = [];

	/**
	 * All tokenized constants cache.
	 *
	 * @var array
	 */
	private $allConstants;

	/**
	 * All tokenized classes cache.
	 *
	 * @var array
	 */
	private $allClasses;

	/**
	 * All tokenized functions cache.
	 *
	 * @var array
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
	 * Reflection broker.
	 *
	 * @var ApiGen\TokenReflection\Broker
	 */
	private $broker;

	/**
	 * Determines if token streams are stored within the backend.
	 *
	 * @var boolean
	 */
	private $storingTokenStreams;


	/**
	 * Returns if a file with the given filename has been processed.
	 *
	 * @param string $fileName File name
	 * @return boolean
	 */
	public function hasFile($fileName)
	{
		return isset($this->files[$fileName]);
	}


	/**
	 * Returns a file reflection.
	 *
	 * @param string $fileName File name
	 * @return ApiGen\TokenReflection\ReflectionFile
	 * @throws ApiGen\TokenReflection\Exception\BrokerException If the requested file has not been processed
	 */
	public function getFile($fileName)
	{
		if (!isset($this->files[$fileName])) {
			throw new Exception\BrokerException($this->getBroker(), sprintf('File "%s" has not been processed.', $fileName), Exception\BrokerException::DOES_NOT_EXIST);
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
	 * @param string $namespaceName Namespace name
	 * @return boolean
	 */
	public function hasNamespace($namespaceName)
	{
		return isset($this->namespaces[ltrim($namespaceName, '\\')]);
	}


	/**
	 * Returns a reflection object of the given namespace.
	 *
	 * @param string $namespaceName Namespace name
	 * @return ApiGen\TokenReflection\IReflectionNamespace
	 * @throws ApiGen\TokenReflection\Exception\BrokerException If the requested namespace does not exist.
	 */
	public function getNamespace($namespaceName)
	{
		if (!isset($this->namespaces[TokenReflection\ReflectionNamespace::NO_NAMESPACE_NAME])) {
			$this->namespaces[TokenReflection\ReflectionNamespace::NO_NAMESPACE_NAME] = new TokenReflection\ReflectionNamespace(TokenReflection\ReflectionNamespace::NO_NAMESPACE_NAME, $this->broker);
		}
		$namespaceName = ltrim($namespaceName, '\\');
		if (!isset($this->namespaces[$namespaceName])) {
			throw new Exception\BrokerException($this->getBroker(), sprintf('Namespace %s does not exist.', $namespaceName), Exception\BrokerException::DOES_NOT_EXIST);
		}
		return $this->namespaces[$namespaceName];
	}


	/**
	 * Returns all present namespaces.
	 *
	 * @return array
	 */
	public function getNamespaces()
	{
		return $this->namespaces;
	}


	/**
	 * Returns if there was such class processed (FQN expected).
	 *
	 * @param string $className Class name
	 * @return boolean
	 */
	public function hasClass($className)
	{
		$className = ltrim($className, '\\');
		if ($pos = strrpos($className, '\\')) {
			$namespace = substr($className, 0, $pos);
			if (!isset($this->namespaces[$namespace])) {
				return FALSE;
			}
			$namespace = $this->getNamespace($namespace);
			$className = substr($className, $pos + 1);
		} else {
			$namespace = $this->getNamespace(TokenReflection\ReflectionNamespace::NO_NAMESPACE_NAME);
		}
		return $namespace->hasClass($className);
	}


	/**
	 * Returns a reflection object of the given class (FQN expected).
	 *
	 * @param string $className CLass bame
	 * @return ApiGen\TokenReflection\IReflectionClass
	 */
	public function getClass($className)
	{
		if (empty($this->declaredClasses)) {
			$this->declaredClasses = array_flip(array_merge(get_declared_classes(), get_declared_interfaces()));
		}
		$className = ltrim($className, '\\');
		try {
			$ns = $this->getNamespace(
				($boundary = strrpos($className, '\\'))
					// Class within a namespace
					? substr($className, 0, $boundary)
					// Class without a namespace
					: TokenReflection\ReflectionNamespace::NO_NAMESPACE_NAME
			);
			return $ns->getClass($className);
		} catch (Exception\BaseException $e) {
			if (isset($this->declaredClasses[$className])) {
				$reflection = new Php\ReflectionClass($className, $this->broker);
				if ($reflection->isInternal()) {
					return $reflection;
				}
			}
			return new Dummy\ReflectionClass($className, $this->broker);
		}
	}


	/**
	 * Returns all classes from all namespaces.
	 *
	 * @param integer $type Returned class types (multiple values may be OR-ed)
	 * @return array
	 */
	public function getClasses($type = self::TOKENIZED_CLASSES)
	{
		if (NULL === $this->allClasses) {
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
	 * @param string $constantName Constant name
	 * @return boolean
	 */
	public function hasConstant($constantName)
	{
		$constantName = ltrim($constantName, '\\');
		if ($pos = strpos($constantName, '::')) {
			$className = substr($constantName, 0, $pos);
			$constantName = substr($constantName, $pos + 2);
			if (!$this->hasClass($className)) {
				return FALSE;
			}
			$parent = $this->getClass($className);
		} else {
			if ($pos = strrpos($constantName, '\\')) {
				$namespace = substr($constantName, 0, $pos);
				if (!$this->hasNamespace($namespace)) {
					return FALSE;
				}
				$parent = $this->getNamespace($namespace);
				$constantName = substr($constantName, $pos + 1);
			} else {
				$parent = $this->getNamespace(TokenReflection\ReflectionNamespace::NO_NAMESPACE_NAME);
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
				$ns = $this->getNamespace(TokenReflection\ReflectionNamespace::NO_NAMESPACE_NAME);
			}
			return $ns->getConstant($constantName);
		} catch (Exception\BaseException $e) {
			if (isset($declared[$constantName])) {
				$reflection = new Php\ReflectionConstant($constantName, $declared[$constantName], $this->broker);
				if ($reflection->isInternal()) {
					return $reflection;
				}
			}
			throw new Exception\BrokerException($this->getBroker(), sprintf('Constant %s does not exist.', $constantName), Exception\BrokerException::DOES_NOT_EXIST);
		}
	}


	/**
	 * Returns all constants from all namespaces.
	 *
	 * @return array
	 */
	public function getConstants()
	{
		if (NULL === $this->allConstants) {
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
	 * @param string $functionName Function name
	 * @return boolean
	 */
	public function hasFunction($functionName)
	{
		$functionName = ltrim($functionName, '\\');
		if ($pos = strrpos($functionName, '\\')) {
			$namespace = substr($functionName, 0, $pos);
			if (!isset($this->namespaces[$namespace])) {
				return FALSE;
			}
			$namespace = $this->getNamespace($namespace);
			$functionName = substr($functionName, $pos + 1);
		} else {
			$namespace = $this->getNamespace(TokenReflection\ReflectionNamespace::NO_NAMESPACE_NAME);
		}
		return $namespace->hasFunction($functionName);
	}


	/**
	 * Returns a reflection object of a function (FQN expected).
	 *
	 * @param string $functionName Function name
	 * @return ApiGen\TokenReflection\IReflectionFunction
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If the requested function does not exist.
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
			$ns = $this->getNamespace(
				($boundary = strrpos($functionName, '\\'))
					// Function within a namespace
					? substr($functionName, 0, $boundary)
					// Function wihout a namespace
					: TokenReflection\ReflectionNamespace::NO_NAMESPACE_NAME
			);
			return $ns->getFunction($functionName);
		} catch (Exception\BaseException $e) {
			if (isset($declared[$functionName])) {
				return new Php\ReflectionFunction($functionName, $this->broker);
			}
			throw new Exception\BrokerException($this->getBroker(), sprintf('Function %s does not exist.', $functionName), Exception\BrokerException::DOES_NOT_EXIST);
		}
	}


	/**
	 * Returns all functions from all namespaces.
	 *
	 * @return array
	 */
	public function getFunctions()
	{
		if (NULL === $this->allFunctions) {
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
	 * @param string $fileName File name
	 * @return boolean
	 */
	public function isFileProcessed($fileName)
	{
		return isset($this->tokenStreams[Broker::getRealPath($fileName)]);
	}


	/**
	 * Returns an array of tokens for a particular file.
	 *
	 * @param string $fileName File name
	 * @return ApiGen\TokenReflection\Stream\StreamBase
	 * @throws ApiGen\TokenReflection\Exception\BrokerException If the requested file was not processed.
	 */
	public function getFileTokens($fileName)
	{
		$realName = Broker::getRealPath($fileName);
		if (!isset($this->tokenStreams[$realName])) {
			throw new Exception\BrokerException($this->getBroker(), sprintf('File "%s" was not processed yet.', $fileName), Exception\BrokerException::DOES_NOT_EXIST);
		}
		return TRUE === $this->tokenStreams[$realName] ? new FileStream($realName) : $this->tokenStreams[$realName];
	}


	/**
	 * Adds a file to the backend storage.
	 *
	 * @param ApiGen\TokenReflection\Stream\StreamBase $tokenStream Token stream
	 * @param ApiGen\TokenReflection\ReflectionFile $file File reflection object
	 * @return ApiGen\TokenReflection\Broker\Backend\Memory
	 */
	public function addFile(TokenReflection\Stream\StreamBase $tokenStream, TokenReflection\ReflectionFile $file)
	{
		$this->tokenStreams[$file->getName()] = $this->storingTokenStreams ? $tokenStream : TRUE;
		$this->files[$file->getName()] = $file;
		$errors = [];
		foreach ($file->getNamespaces() as $fileNamespace) {
			try {
				$namespaceName = $fileNamespace->getName();
				if (!isset($this->namespaces[$namespaceName])) {
					$this->namespaces[$namespaceName] = new TokenReflection\ReflectionNamespace($namespaceName, $file->getBroker());
				}
				$this->namespaces[$namespaceName]->addFileNamespace($fileNamespace);
			} catch (Exception\FileProcessingException $e) {
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
		if (!empty($errors)) {
			throw new Exception\FileProcessingException($errors, $file);
		}
		return $this;
	}


	/**
	 * Sets the reflection broker instance.
	 *
	 * @param ApiGen\TokenReflection\Broker $broker Reflection broker
	 * @return ApiGen\TokenReflection\Broker\Backend\Memory
	 */
	public function setBroker(Broker $broker)
	{
		$this->broker = $broker;
		return $this;
	}


	/**
	 * Returns the reflection broker instance.
	 *
	 * @return ApiGen\TokenReflection\Broker $broker Reflection broker
	 */
	public function getBroker()
	{
		return $this->broker;
	}


	/**
	 * Sets if token streams are stored in the backend.
	 *
	 * @param boolean $store
	 * @return ApiGen\TokenReflection\Broker\Backend
	 */
	public function setStoringTokenStreams($store)
	{
		$this->storingTokenStreams = (bool) $store;
		return $this;
	}


	/**
	 * Returns if token streams are stored in the backend.
	 *
	 * @return boolean
	 */
	public function getStoringTokenStreams()
	{
		return $this->storingTokenStreams;
	}


	/**
	 * Prepares and returns used class lists.
	 *
	 * @return array
	 */
	protected function parseClassLists()
	{
		// Initialize the all-classes-cache
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
				} elseif (!$parent->isTokenized()) {
					$allClasses[self::NONEXISTENT_CLASSES][$parent->getName()] = $parent;
				}
			}
		}
		return $allClasses;
	}
}
