<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */
namespace ApiGen\TokenReflection\Php;

use ApiGen\TokenReflection;
use ApiGen\TokenReflection\Broker, TokenReflection\Exception;
use ApiGen\TokenReflection\Exception\RuntimeException;
use Reflector, ReflectionExtension as InternalReflectionExtension;


/**
 * Reflection of a not tokenized but defined extension.
 *
 * Descendant of the internal reflection with additional features.
 */
class ReflectionExtension extends InternalReflectionExtension implements IReflection, TokenReflection\IReflectionExtension
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
	 * Reflection broker.
	 *
	 * @var ApiGen\TokenReflection\Broker
	 */
	private $broker;


	/**
	 * Constructor.
	 *
	 * @param string $name Extension name
	 * @param ApiGen\TokenReflection\Broker $broker Reflection broker
	 */
	public function __construct($name, Broker $broker)
	{
		parent::__construct($name);
		$this->broker = $broker;
	}


	/**
	 * Returns if the constant is internal.
	 *
	 * @return boolean
	 */
	public function isInternal()
	{
		return TRUE;
	}


	/**
	 * Returns if the constant is user defined.
	 *
	 * @return boolean
	 */
	public function isUserDefined()
	{
		return FALSE;
	}


	/**
	 * Returns if the current reflection comes from a tokenized source.
	 *
	 * @return boolean
	 */
	public function isTokenized()
	{
		return FALSE;
	}


	/**
	 * Returns if the reflection subject is deprecated.
	 *
	 * @return boolean
	 */
	public function isDeprecated()
	{
		return FALSE;
	}


	/**
	 * Returns a class reflection.
	 *
	 * @param string $name Class name
	 * @return ApiGen\TokenReflection\IReflectionClass|null
	 */
	public function getClass($name)
	{
		$classes = $this->getClasses();
		return isset($classes[$name]) ? $classes[$name] : NULL;
	}


	/**
	 * Returns classes defined by this extension.
	 *
	 * @return array
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
	 * Returns a constant value.
	 *
	 * @param string $name Constant name
	 * @return mixed|false
	 */
	public function getConstant($name)
	{
		$constants = $this->getConstants();
		return isset($constants[$name]) ? $constants[$name] : FALSE;
	}


	/**
	 * Returns a constant reflection.
	 *
	 * @param string $name Constant name
	 * @return ApiGen\TokenReflection\IReflectionConstant
	 */
	public function getConstantReflection($name)
	{
		$constants = $this->getConstantReflections();
		return isset($constants[$name]) ? $constants[$name] : NULL;
	}


	/**
	 * Returns reflections of defined constants.
	 *
	 * @return array
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
	 * Returns a function reflection.
	 *
	 * @param string $name Function name
	 * @return ApiGen\TokenReflection\IReflectionFunction
	 */
	public function getFunction($name)
	{
		$functions = $this->getFunctions();
		return isset($functions[$name]) ? $functions[$name] : NULL;
	}


	/**
	 * Returns functions defined by this extension.
	 *
	 * @return array
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
	 * Returns names of functions defined by this extension.
	 *
	 * @return array
	 */
	public function getFunctionNames()
	{
		return array_keys($this->getFunctions());
	}


	/**
	 * Returns an element pretty (docblock compatible) name.
	 *
	 * @return string
	 */
	public function getPrettyName()
	{
		return $this->getName();
	}


	/**
	 * Returns the reflection broker used by this reflection object.
	 *
	 * @return ApiGen\TokenReflection\Broker
	 */
	public function getBroker()
	{
		return $this->broker;
	}


	/**
	 * Magic __get method.
	 *
	 * @param string $key Variable name
	 * @return mixed
	 */
	final public function __get($key)
	{
		return TokenReflection\ReflectionElement::get($this, $key);
	}


	/**
	 * Magic __isset method.
	 *
	 * @param string $key Variable name
	 * @return boolean
	 */
	final public function __isset($key)
	{
		return TokenReflection\ReflectionElement::exists($this, $key);
	}


	/**
	 * Creates a reflection instance.
	 *
	 * @param \ReflectionClass $internalReflection Internal reflection instance
	 * @param ApiGen\TokenReflection\Broker $broker Reflection broker instance
	 * @return ApiGen\TokenReflection\Php\ReflectionExtension
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If an invalid internal reflection object was provided.
	 */
	public static function create(Reflector $internalReflection, Broker $broker)
	{
		static $cache = [];
		if (!$internalReflection instanceof InternalReflectionExtension) {
			throw new RuntimeException('Invalid reflection instance provided, ReflectionExtension expected.', RuntimeException::INVALID_ARGUMENT);
		}
		if (!isset($cache[$internalReflection->getName()])) {
			$cache[$internalReflection->getName()] = new self($internalReflection->getName(), $broker);
		}
		return $cache[$internalReflection->getName()];
	}
}
