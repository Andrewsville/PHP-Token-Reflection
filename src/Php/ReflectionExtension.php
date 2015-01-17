<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Php;

use ApiGen\TokenReflection\Broker\Broker;
use ApiGen\TokenReflection\Exception;
use ApiGen\TokenReflection\Exception\RuntimeException;
use ApiGen\TokenReflection\ReflectionExtensionInterface;
use ApiGen\TokenReflection\Reflection\ReflectionElement;
use Reflector, ReflectionExtension as InternalReflectionExtension;


class ReflectionExtension extends InternalReflectionExtension implements IReflection, ReflectionExtensionInterface
{

	/**
	 * Defined classes.
	 *
	 * @var array
	 */
	private $classes;

	/**
	 * Defined constants.
	 *
	 * @var array
	 */
	private $constants;

	/**
	 * Defined functions.
	 *
	 * @var array
	 */
	private $functions;

	/**
	 * @var Broker
	 */
	private $broker;


	/**
	 * @param string $name Extension name
	 * @param Broker $broker Reflection broker
	 */
	public function __construct($name, Broker $broker)
	{
		parent::__construct($name);
		$this->broker = $broker;
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
		if (NULL === $this->classes) {
			$broker = $this->broker;
			$this->classes = array_map(function ($className) use ($broker) {
				return $broker->getClass($className);
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
		return isset($constants[$name]) ? $constants[$name] : FALSE;
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
		if (NULL === $this->constants) {
			$broker = $this->broker;
			$this->constants = array_map(function ($constantName) use ($broker) {
				return $broker->getConstant($constantName);
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
		if (NULL === $this->functions) {
			$broker = $this->broker;
			$this->classes = array_map(function ($functionName) use ($broker) {
				return $broker->getFunction($functionName);
			}, array_keys(parent::getFunctions()));
		}
		return $this->functions;
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
	public function getBroker()
	{
		return $this->broker;
	}


	/**
	 * {@inheritdoc}
	 */
	public function __get($key)
	{
		return ReflectionElement::get($this, $key);
	}


	/**
	 * {@inheritdoc}
	 */
	public function __isset($key)
	{
		return ReflectionElement::exists($this, $key);
	}


	/**
	 * Creates a reflection instance.
	 *
	 * @return ReflectionExtension
	 * @throws RuntimeException If an invalid internal reflection object was provided.
	 */
	public static function create(Reflector $internalReflection, Broker $broker)
	{
		static $cache = [];
		if ( ! $internalReflection instanceof InternalReflectionExtension) {
			throw new RuntimeException('Invalid reflection instance provided, ReflectionExtension expected.', RuntimeException::INVALID_ARGUMENT);
		}
		if ( ! isset($cache[$internalReflection->getName()])) {
			$cache[$internalReflection->getName()] = new self($internalReflection->getName(), $broker);
		}
		return $cache[$internalReflection->getName()];
	}

}
