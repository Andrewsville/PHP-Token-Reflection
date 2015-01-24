<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Storage;

use ApiGen\TokenReflection;
use ApiGen\TokenReflection\Exception;
use ApiGen\TokenReflection\Exception\BrokerException;
use ApiGen\TokenReflection\Exception\RuntimeException;
use ApiGen\TokenReflection\Php\ReflectionClass;
use ApiGen\TokenReflection\Php\ReflectionConstant;
use ApiGen\TokenReflection\Php\ReflectionFunction;
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
	 * @var string[]
	 */
	private $declaredClasses = [];

	/**
	 * @var ReflectionNamespaceInterface[]
	 */
	private $namespaces = [];

	/**
	 * @var ReflectionConstantInterface[]
	 */
	private $allConstants;

	/**
	 * @var ReflectionClassInterface[]
	 */
	private $allClasses;

	/**
	 * @var ReflectionFunctionInterface[]
	 */
	private $allFunctions;

	/**
	 * @var array
	 */
	private $tokenStreams = [];

	/**
	 * @var array
	 */
	private $files = [];


	public function __construct()
	{
		$this->declaredClasses = array_flip(array_merge(get_declared_classes(), get_declared_interfaces()));
	}


	/**
	 * Returns if a file with the given filename has been processed.
	 *
	 * @param string $name
	 * @return bool
	 */
	public function hasFile($name)
	{
		return isset($this->files[$name]);
	}


	/**
	 * @param string $name
	 * @return ReflectionFile
	 */
	public function getFile($name)
	{
		if ( ! $this->hasFile($name)) {
			throw new BrokerException(sprintf('File "%s" has not been processed.', $name));
		}
		return $this->files[$name];
	}


	/**
	 * @return ReflectionFile[]
	 */
	public function getFiles()
	{
		return $this->files;
	}


	/**
	 * @param string $name
	 * @param ReflectionNamespace $reflectionNamespace
	 */
	public function addNamespace($name, ReflectionNamespace $reflectionNamespace)
	{
		$this->namespaces[$name] = $reflectionNamespace;
	}


	/**
	 * Returns if there was such namespace processed (FQN expected).
	 *
	 * @param string $name
	 * @return bool
	 */
	public function hasNamespace($name)
	{
		return isset($this->namespaces[ltrim($name, '\\')]);
	}


	/**
	 * Returns a reflection object of the given namespace.
	 *
	 * @param string $name
	 * @return ReflectionNamespaceInterface
	 */
	public function getNamespace($name)
	{
		$name = ltrim($name, '\\');
		if ( ! $this->hasNamespace($name)) {
			throw new BrokerException(sprintf('Namespace %s does not exist.', $name));
		}
		return $this->namespaces[$name];
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
	 * @param string $name
	 * @return bool
	 */
	public function hasClass($name)
	{
		$name = ltrim($name, '\\');
		if ($pos = strrpos($name, '\\')) {
			$namespace = substr($name, 0, $pos);
			if ( ! isset($this->namespaces[$namespace])) {
				return FALSE;
			}
			$namespace = $this->getNamespace($namespace);
			$name = substr($name, $pos + 1);

		} else {
			$namespace = $this->getNamespace(ReflectionNamespace::NO_NAMESPACE_NAME);
		}
		return $namespace->hasClass($name);
	}


	/**
	 * Returns a reflection object of the given class (FQN expected).
	 *
	 * @param string $name
	 * @return ReflectionClassInterface|NULL
	 */
	public function getClass($name)
	{
		$name = ltrim($name, '\\');
		try {
			$namespaceReflection = $this->getNamespace(
				($boundary = strrpos($name, '\\'))
					// Class within a namespace
					? substr($name, 0, $boundary)
					// Class without a namespace
					: ReflectionNamespace::NO_NAMESPACE_NAME
			);
			return $namespaceReflection->getClass($name);

		} catch (Exception\BaseException $e) {
			if (isset($this->declaredClasses[$name])) {
				$reflection = new ReflectionClass($name, $this);
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
	 * @param string $name
	 * @return bool
	 */
	public function hasConstant($name)
	{
		$name = ltrim($name, '\\');
		if ($pos = strpos($name, '::')) {
			$className = substr($name, 0, $pos);
			$name = substr($name, $pos + 2);
			if ( ! $this->hasClass($className)) {
				return FALSE;
			}
			$parent = $this->getClass($className);

		} else {
			if ($pos = strrpos($name, '\\')) {
				$namespace = substr($name, 0, $pos);
				if ( ! $this->hasNamespace($namespace)) {
					return FALSE;
				}
				$parent = $this->getNamespace($namespace);
				$name = substr($name, $pos + 1);

			} else {
				$parent = $this->getNamespace(ReflectionNamespace::NO_NAMESPACE_NAME);
			}
		}
		return $parent->hasConstant($name);
	}


	/**
	 * Returns a reflection object of a constant (FQN expected).
	 *
	 * @param string $name
	 * @return ReflectionConstantInterface
	 * @throws RuntimeException If the requested constant does not exist.
	 */
	public function getConstant($name)
	{
		static $declared = [];
		if (empty($declared)) {
			$declared = get_defined_constants();
		}
		if ($boundary = strpos($name, '::')) {
			// Class constant
			$className = substr($name, 0, $boundary);
			$name = substr($name, $boundary + 2);
			return $this->getClass($className)->getConstantReflection($name);
		}
		try {
			$name = ltrim($name, '\\');
			if ($boundary = strrpos($name, '\\')) {
				$ns = $this->getNamespace(substr($name, 0, $boundary));
				$name = substr($name, $boundary + 1);

			} else {
				$ns = $this->getNamespace(ReflectionNamespace::NO_NAMESPACE_NAME);
			}
			return $ns->getConstant($name);

		} catch (Exception\BaseException $e) {
			if (isset($declared[$name])) {
				$reflection = new ReflectionConstant($name, $declared[$name], $this);
				if ($reflection->isInternal()) {
					return $reflection;
				}
			}
			throw new BrokerException(sprintf('Constant %s does not exist.', $name));
		}
	}


	/**
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
	 * @param string $name
	 * @return bool
	 */
	public function hasFunction($name)
	{
		$name = ltrim($name, '\\');
		if ($pos = strrpos($name, '\\')) {
			$namespace = substr($name, 0, $pos);
			if ( ! isset($this->namespaces[$namespace])) {
				return FALSE;
			}
			$namespace = $this->getNamespace($namespace);
			$name = substr($name, $pos + 1);

		} else {
			$namespace = $this->getNamespace(ReflectionNamespace::NO_NAMESPACE_NAME);
		}
		return $namespace->hasFunction($name);
	}


	/**
	 * Returns a reflection object of a function (FQN expected).
	 *
	 * @param string $name
	 * @return ReflectionFunctionInterface
	 * @throws RuntimeException If the requested function does not exist.
	 */
	public function getFunction($name)
	{
		static $declared = [];
		if (empty($declared)) {
			$functions = get_defined_functions();
			$declared = array_flip($functions['internal']);
		}
		$name = ltrim($name, '\\');
		try {
			$namespaceReflection = $this->getNamespace(
				($boundary = strrpos($name, '\\'))
					// Function within a namespace
					? substr($name, 0, $boundary)
					// Function wihout a namespace
					: ReflectionNamespace::NO_NAMESPACE_NAME
			);
			return $namespaceReflection->getFunction($name);

		} catch (Exception\BaseException $e) {
			if (isset($declared[$name])) {
				return new ReflectionFunction($name, $this);
			}
			throw new BrokerException(sprintf('Function %s does not exist.', $name));
		}
	}


	/**
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
	 * @param string $name
	 * @return bool
	 */
	public function isFileProcessed($name)
	{
		return isset($this->tokenStreams[realpath($name)]);
	}


	public function addFile(StreamBase $tokenStream, ReflectionFile $file)
	{
		$this->tokenStreams[$file->getName()] = $tokenStream;
		$this->files[$file->getName()] = $file;
		foreach ($file->getNamespaces() as $fileNamespace) {
			$namespaceName = $fileNamespace->getName();
			if ( ! isset($this->namespaces[$namespaceName])) {
				$this->namespaces[$namespaceName] = new ReflectionNamespace($namespaceName, $file->getStorage());
			}
			$this->namespaces[$namespaceName]->addFileNamespace($fileNamespace);
		}
		// Reset all-*-cache
		$this->allClasses = NULL;
		$this->allFunctions = NULL;
		$this->allConstants = NULL;
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
