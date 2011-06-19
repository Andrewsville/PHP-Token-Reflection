<?php
/**
 * PHP Token Reflection
 *
 * Version 1.0 beta 3
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this library in the file LICENSE.
 *
 * @author Ondřej Nešpor <andrew@andrewsville.cz>
 * @author Jaroslav Hanslík <kontakt@kukulich.cz>
 */

namespace TokenReflection\Php;

use TokenReflection;
use TokenReflection\Broker, TokenReflection\Exception;
use Reflector, ReflectionMethod as InternalReflectionMethod, ReflectionParameter as InternalReflectionParameter;

/**
 * Reflection of a not tokenized but defined class method.
 *
 * Descendant of the internal reflection with additional features.
 */
class ReflectionMethod extends InternalReflectionMethod implements IReflection, TokenReflection\IReflectionMethod
{
	/**
	 * Function parameter reflections.
	 *
	 * @var array
	 */
	private $parameters;

	/**
	 * Reflection broker.
	 *
	 * @var \TokenReflection\Broker
	 */
	private $broker;

	/**
	 * Constructor.
	 *
	 * @param string|\TokenReflection\Php\ReflectionClass|\ReflectionClass $class Defining class
	 * @param string $methodName Method name
	 * @param \TokenReflection\Broker $broker Reflection broker
	 */
	public function __construct($class, $methodName, Broker $broker)
	{
		parent::__construct($class, $methodName);
		$this->broker = $broker;
	}

	/**
	 * Returns the declaring class reflection.
	 *
	 * @return \TokenReflection\Php\IReflectionClass
	 */
	public function getDeclaringClass()
	{
		return ReflectionClass::create(parent::getDeclaringClass(), $this->broker);
	}

	/**
	 * Returns the declaring class name.
	 *
	 * @return string
	 */
	public function getDeclaringClassName()
	{
		return $this->getDeclaringClass()->getName();
	}

	/**
	 * Checks if there is a particular annotation.
	 *
	 * @param string $name Annotation name
	 * @return boolean
	 */
	public function hasAnnotation($name)
	{
		return false;
	}

	/**
	 * Returns a particular annotation value.
	 *
	 * @param string $name Annotation name
	 * @return null
	 */
	public function getAnnotation($name)
	{
		return null;
	}

	/**
	 * Returns parsed docblock.
	 *
	 * @return array
	 */
	public function getAnnotations()
	{
		return array();
	}

	/**
	 * Returns if the current reflection comes from a tokenized source.
	 *
	 * @return boolean
	 */
	public function isTokenized()
	{
		return false;
	}

	/**
	 * Returns the method prototype.
	 *
	 * @return \TokenReflection\Php\ReflectionMethod
	 */
	public function getPrototype()
	{
		return self::create(parent::getPrototype(), $this->broker);
	}

	/**
	 * Returns a particular parameter.
	 *
	 * @param integer|string $parameter Parameter name or position
	 * @return \TokenReflection\Php\ReflectionParameter
	 * @throws \TokenReflection\Exception\Runtime If there is no parameter of the given name
	 * @throws \TokenReflection\Exception\Runtime If there is no parameter at the given position
	 */
	public function getParameter($parameter)
	{
		$parameters = $this->getParameters();

		if (is_numeric($parameter)) {
			if (!isset($parameters[$parameter])) {
				throw new Exception\Runtime(sprintf('There is no parameter at position "%d" in method "%s".', $parameter, $this->getName()), Exception\Runtime::DOES_NOT_EXIST);
			}

			return $parameters[$parameter];
		} else {
			foreach ($parameters as $reflection) {
				if ($reflection->getName() === $parameter) {
					return $reflection;
				}
			}

			throw new Exception\Runtime(sprintf('There is no parameter "%s" in method "%s".', $parameter, $this->getName()), Exception\Runtime::DOES_NOT_EXIST);
		}
	}

	/**
	 * Returns function parameters.
	 *
	 * @return array
	 */
	public function getParameters()
	{
		if (null === $this->parameters) {
			$broker = $this->broker;
			$parent = $this;
			$this->parameters = array_map(function(InternalReflectionParameter $parameter) use ($broker, $parent) {
				return ReflectionParameter::create($parameter, $broker, $parent);
			}, parent::getParameters());
		}

		return $this->parameters;
	}

	/**
	 * Sets a method to be accessible or not.
	 *
	 * Introduced in PHP 5.3.2. Throws an exception if run on an older version.
	 *
	 * @param boolean $accessible
	 * @throws \TokenReflection\Exception\Runtime If run on PHP version < 5.3.2
	 */
	public function setAccessible($accessible)
	{
		if (PHP_VERSION_ID < 50302) {
			throw new Exception\Runtime(sprintf('Method setAccessible was introduced the internal reflection in PHP 5.3.2, you are using %s.', PHP_VERSION), Exception\Runtime::UNSUPPORTED);
		}

		return parent::setAccessible($accessible);
	}

	/**
	 * Shortcut for isPublic(), ... methods that allows or-ed modifiers.
	 *
	 * @param integer $filter Filter
	 * @return boolean
	 */
	public function is($filter = null)
	{
		return null === $filter || ($this->getModifiers() & $filter);
	}

	/**
	 * Returns the reflection broker used by this reflection object.
	 *
	 * @return \TokenReflection\Broker
	 */
	public function getBroker()
	{
		return $this->broker;
	}

	/**
	 * Returns imported namespaces and aliases from the declaring namespace.
	 *
	 * @return array
	 */
	public function getNamespaceAliases()
	{
		return array();
	}

	/**
	 * Magic __get method.
	 *
	 * @param string $key Variable name
	 * @return mixed
	 */
	final public function __get($key)
	{
		return TokenReflection\ReflectionBase::get($this, $key);
	}

	/**
	 * Magic __isset method.
	 *
	 * @param string $key Variable name
	 * @return boolean
	 */
	final public function __isset($key)
	{
		return TokenReflection\ReflectionBase::exists($this, $key);
	}

	/**
	 * Creates a reflection instance.
	 *
	 * @param \ReflectionClass $internalReflection Internal reflection instance
	 * @param \TokenReflection\Broker $broker Reflection broker instance
	 * @return \TokenReflection\Php\IReflection
	 * @throws \TokenReflection\Exception\Runtime If an invalid internal reflection object was provided
	 */
	public static function create(Reflector $internalReflection, Broker $broker)
	{
		static $cache = array();

		if (!$internalReflection instanceof InternalReflectionMethod) {
			throw new Exception\Runtime(sprintf('Invalid reflection instance provided: "%s", ReflectionMethod expected.', get_class($internalReflection)), Exception\Runtime::INVALID_ARGUMENT);
		}

		$key = $internalReflection->getDeclaringClass()->getName() . '::' . $internalReflection->getName();
		if (!isset($cache[$key])) {
			$cache[$key] = new self($internalReflection->getDeclaringClass()->getName(), $internalReflection->getName(), $broker);
		}

		return $cache[$key];
	}
}
