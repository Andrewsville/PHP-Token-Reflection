<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Php;

use ApiGen\TokenReflection\Broker\Broker;
use ApiGen\TokenReflection\Broker\StorageInterface;
use ApiGen\TokenReflection\Exception;
use ApiGen\TokenReflection\Exception\RuntimeException;
use ApiGen\TokenReflection\ReflectionClassInterface;
use ApiGen\TokenReflection\ReflectionConstantInterface;
use ApiGen\TokenReflection\ReflectionExtensionInterface;
use ApiGen\TokenReflection\ReflectionFunctionInterface;
use Reflector, ReflectionExtension as InternalReflectionExtension;


class ReflectionExtension extends InternalReflectionExtension implements ReflectionInterface, ReflectionExtensionInterface
{

	/**
	 * @var array|ReflectionClassInterface[]
	 */
	private $classes;

	/**
	 * @var array|ReflectionConstantInterface[]
	 */
	private $constants;

	/**
	 * @var array|ReflectionFunctionInterface[]
	 */
	private $functions;

	/**
	 * @var StorageInterface
	 */
	private $storage;


	/**
	 * @param string $name
	 * @param Broker $broker
	 */
	public function __construct($name, StorageInterface $storage)
	{
		parent::__construct($name);
		$this->storage = $storage;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isInternal()
	{
		return TRUE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isUserDefined()
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isTokenized()
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isDeprecated()
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getClass($name)
	{
		$classes = $this->getClasses();
		return isset($classes[$name]) ? $classes[$name] : NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getClasses()
	{
		if ($this->classes === NULL) {
			$this->classes = array_map(function ($className) {
				return $this->storage->getClass($className);
			}, $this->getClassNames());
		}
		return $this->classes;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getConstant($name)
	{
		$constants = $this->getConstants();
		return isset($constants[$name]) ? $constants[$name] : NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getConstantReflection($name)
	{
		$constants = $this->getConstantReflections();
		return isset($constants[$name]) ? $constants[$name] : NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getConstantReflections()
	{
		if ($this->constants === NULL) {
			$this->constants = array_map(function ($constantName) {
				return $this->storage->getConstant($constantName);
			}, array_keys($this->getConstants()));
		}
		return $this->constants;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getFunction($name)
	{
		$functions = $this->getFunctions();
		return isset($functions[$name]) ? $functions[$name] : NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getFunctions()
	{
		if ($this->functions === NULL) {
			$this->classes = array_map(function ($functionName) {
				return $this->storage->getFunction($functionName);
			}, array_keys(parent::getFunctions()));
		}
		return (array) $this->functions;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getFunctionNames()
	{
		return array_keys($this->getFunctions());
	}


	/**
	 * {@inheritdoc}
	 */
	public function getPrettyName()
	{
		return $this->getName();
	}


	/**
	 * {@inheritdoc}
	 */
	public function getStorage()
	{
		return $this->storage;
	}


	/**
	 * @return ReflectionExtension
	 * @throws RuntimeException If an invalid internal reflection object was provided.
	 */
	public static function create(Reflector $internalReflection, StorageInterface $storage)
	{
		static $cache = [];
		if ( ! $internalReflection instanceof InternalReflectionExtension) {
			throw new RuntimeException('Invalid reflection instance provided, ReflectionExtension expected.');
		}
		if ( ! isset($cache[$internalReflection->getName()])) {
			return new self($internalReflection->getName(), $storage);
		}
		return $cache[$internalReflection->getName()];
	}

}
