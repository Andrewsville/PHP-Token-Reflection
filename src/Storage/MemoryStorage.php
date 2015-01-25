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
use ApiGen\TokenReflection\Exception\ParserException;
use ApiGen\TokenReflection\Php\ReflectionClass;
use ApiGen\TokenReflection\Php\ReflectionConstant;
use ApiGen\TokenReflection\Php\ReflectionFunction;
use ApiGen\TokenReflection\Reflection\ReflectionFile;
use ApiGen\TokenReflection\Reflection\ReflectionNamespace;
use ApiGen\TokenReflection\ReflectionClassInterface;
use ApiGen\TokenReflection\ReflectionConstantInterface;
use ApiGen\TokenReflection\ReflectionFunctionInterface;
use ApiGen\TokenReflection\ReflectionNamespaceInterface;


class MemoryStorage implements StorageInterface
{

	/**
	 * @var ReflectionNamespaceInterface[]
	 */
	private $namespaces = [];

	/**
	 * @var ReflectionConstantInterface[]
	 */
	private $constants;

	/**
	 * @var ReflectionClassInterface[]
	 */
	private $classes;

	/**
	 * @var ReflectionFunctionInterface[]
	 */
	private $functions;

	/**
	 * @var array
	 */
	private $files = [];

	/**
	 * @var string[]
	 */
	private $declaredClasses;

	/**
	 * @var string[]
	 */
	private $declaredConstants;

	/**
	 * @var string[]
	 */
	private $declaredFunctions;


	/**
	 * {@inheritdoc}
	 */
	public function hasFile($name)
	{
		return isset($this->files[$name]);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getFile($name)
	{
		if ( ! $this->hasFile($name)) {
			throw new ParserException(sprintf('File "%s" has not been processed.', $name));
		}
		return $this->files[$name];
	}


	/**
	 * {@inheritdoc}
	 */
	public function getFiles()
	{
		return $this->files;
	}


	/**
	 * {@inheritdoc}
	 */
	public function addNamespace($name, ReflectionNamespace $reflectionNamespace)
	{
		$this->namespaces[$name] = $reflectionNamespace;
	}


	/**
	 * {@inheritdoc}
	 */
	public function hasNamespace($name)
	{
		return isset($this->namespaces[ltrim($name, '\\')]);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getNamespace($name)
	{
		$name = ltrim($name, '\\');
		if ( ! $this->hasNamespace($name)) {
			throw new ParserException(sprintf('Namespace %s does not exist.', $name));
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
	 * {@inheritdoc}
	 */
	public function addClass($name, ReflectionClassInterface $reflectionClass)
	{
		$this->classes[$name] = $reflectionClass;
	}


	/**
	 * {@inheritdoc}
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
	 * {@inheritdoc}
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
			if (isset($this->getDeclaredClasses()[$name])) {
				$reflection = new ReflectionClass($name, $this);
				if ($reflection->isInternal()) {
					return $reflection;
				}
			}
			return NULL;
		}
	}


	/**
	 * {@inheritdoc}
	 */
	public function getClasses($type = self::TOKENIZED_CLASSES)
	{
		if ($this->classes === NULL) {
			$this->classes = $this->parseClassLists();
		}
		$result = [];
		foreach ($this->classes as $classType => $classes) {
			if ($type & $classType) {
				$result = array_merge($result, $classes);
			}
		}
		return $result;
	}


	/**
	 * {@inheritdoc}
	 */
	public function addConstant($name, ReflectionConstantInterface $constantReflection)
	{
		$this->constants[$name] = $constantReflection;
	}


	/**
	 * {@inheritdoc}
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
	 * {@inheritdoc}
	 */
	public function getConstant($name)
	{
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
			if (isset($this->getDeclaredConstants()[$name])) {
				$reflection = new ReflectionConstant($name, $this->getDeclaredConstants()[$name], $this);
				if ($reflection->isInternal()) {
					return $reflection;
				}
			}
			throw new ParserException(sprintf('Constant %s does not exist.', $name));
		}
	}


	/**
	 * {@inheritdoc}
	 */
	public function getConstants()
	{
		if ($this->constants === NULL) {
			$this->constants = [];
			foreach ($this->namespaces as $namespace) {
				foreach ($namespace->getConstants() as $constant) {
					$this->constants[$constant->getName()] = $constant;
				}
			}
		}
		return $this->constants;
	}


	/**
	 * {@inheritdoc}
	 */
	public function addFunction($name, ReflectionFunctionInterface $reflectionFunction)
	{
		$this->functions[$name] = $reflectionFunction;
	}


	/**
	 * {@inheritdoc}
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
	 * {@inheritdoc}
	 */
	public function getFunction($name)
	{
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
			if (isset($this->getDeclaredFunctions()[$name])) {
				return new ReflectionFunction($name, $this);
			}
			throw new ParserException(sprintf('Function %s does not exist.', $name));
		}
	}


	/**
	 * {@inheritdoc}
	 */
	public function getFunctions()
	{
		if ($this->functions === NULL) {
			$this->functions = [];
			foreach ($this->namespaces as $namespace) {
				foreach ($namespace->getFunctions() as $function) {
					$this->functions[$function->getName()] = $function;
				}
			}
		}
		return $this->functions;
	}


	/**
	 * {@inheritdoc}
	 */
	public function addFile(ReflectionFile $file)
	{
		$this->files[$file->getName()] = $file;
	}


	/**
	 * Prepares and returns used class lists.
	 *
	 * @return ReflectionClassInterface[][]
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
				/** @var ReflectionClassInterface $parent */
				if ($parent->isInternal()) {
					$allClasses[self::INTERNAL_CLASSES][$parent->getName()] = $parent;

				} elseif ( ! $parent->isTokenized()) {
					$allClasses[self::NONEXISTENT_CLASSES][$parent->getName()] = $parent;
				}
			}
		}

		return $allClasses;
	}


	/**
	 * @return array
	 */
	private function getDeclaredClasses()
	{
		if ($this->declaredClasses === NULL) {
			$this->declaredClasses = array_flip(array_merge(get_declared_classes(), get_declared_interfaces()));
		}
		return $this->declaredClasses;
	}


	/**
	 * @return array
	 */
	private function getDeclaredConstants()
	{
		if ($this->declaredConstants === NULL) {
			$this->declaredConstants = get_defined_constants();
		}
		return $this->declaredConstants;
	}


	/**
	 * @return array
	 */
	private function getDeclaredFunctions()
	{
		if ($this->declaredFunctions === NULL) {
			$this->declaredFunctions = array_flip(get_defined_functions()['internal']);
		}
		return $this->declaredFunctions;
	}

}
