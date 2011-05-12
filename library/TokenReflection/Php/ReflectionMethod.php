<?php
/**
 * PHP Token Reflection
 *
 * Version 1.0beta1
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this library in the file license.txt.
 *
 * @author Ondřej Nešpor <andrew@andrewsville.cz>
 * @author Jaroslav Hanslík <kukulich@kukulich.cz>
 */

namespace TokenReflection\Php;
use TokenReflection;

use TokenReflection\Broker;
use Reflector, ReflectionMethod as InternalReflectionMethod, ReflectionParameter as InternalReflectionParameter;
use RuntimeException;

/**
 * Reflection of a not tokenized but defined class method.
 *
 * Descendant of the internal reflection with additional features.
 */
class ReflectionMethod extends InternalReflectionMethod implements IReflection, TokenReflection\IReflectionMethod
{
	/**
	 * Reflection broker.
	 *
	 * @var \TokenReflection\Broker
	 */
	private $broker;

	/**
	 * Function parameter reflections.
	 *
	 * @var array
	 */
	private $parameters;

	/**
	 * Constructor.
	 *
	 * @param string|\TokenReflection\Php\ReflectionClass|\ReflectionClass Defining class
	 * @param string $methodName Method name
	 * @param \TokenReflection\Broker $broker Reflection broker
	 */
	public function __construct($class, $methodName, Broker $broker)
	{
		parent::__construct($class, $methodName);
		$this->broker = $broker;
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
	final public function __isset($key) {
		return TokenReflection\ReflectionBase::exists($this, $key);
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
			$this->parameters = array_map(function(InternalReflectionParameter $parameter) use($broker, $parent) {
				return ReflectionParameter::create($parameter, $broker, $parent);
			}, parent::getParameters());
		}

		return $this->parameters;
	}

	/**
	 * Returns the docblock definition of the method or its parent.
	 *
	 * @return string|false
	 */
	public function getInheritedDocComment()
	{
		if (false !== ($docComment = $this->getDocComment())) {
			return $docComment;
		}

		$parent = $this->getDeclaringClass()->getParentClass();
		if ($parent && $parent->hasMethod($this->getName())) {
			return $parent->getMethod($this->getName())->getInheritedDocComment();
		}

		return false;
	}

	/**
	 * Returns a particular parameter.
	 *
	 * @param integer|string $parameter Parameter name or position
	 * @return \TokenReflection\Php\ReflectionParameter
	 */
	public function getParameter($parameter)
	{
		$parameters = $this->getParameters();

		if (is_numeric($parameter)) {
			if (isset($parameters[$parameter])) {
				return $parameters[$parameter];
			} else {
				throw new Exception(sprintf('There is no parameter at position %d', $parameter), Exception::DOES_NOT_EXIST);
			}
		} else {
			foreach ($parameters as $reflection) {
				if ($reflection->getName() === $parameter) {
					return $reflection;
				}
			}

			throw new Exception(sprintf('There is no parameter %s', $parameter), Exception::DOES_NOT_EXIST);
		}
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
	 * Returns the name of the declaring class.
	 *
	 * @return string
	 */
	public function getClass()
	{
		return $this->getDeclaringClassName();
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
	 * Returns a particular annotation value.
	 *
	 * @param string $name Annotation name
	 * @param boolean $forceArray Always return values as array
	 * @return string|array|null
	 */
	public function getAnnotation($name)
	{
		return null;
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
	 * Returns the method prototype.
	 *
	 * @return \TokenReflection\Php\ReflectionMethod
	 */
	public function getPrototype()
	{
		return self::create(parent::getPrototype(), $this->broker);
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
	 * Creates a reflection instance.
	 *
	 * @param \ReflectionMethod Internal reflection instance
	 * @param \TokenReflection\Broker Reflection broker instance
	 * @return \TokenReflection\Php\IReflection
	 */
	public static function create(Reflector $internalReflection, Broker $broker)
	{
		static $cache = array();

		if (!$internalReflection instanceof InternalReflectionMethod) {
			throw new RuntimeException(sprintf('Invalid reflection instance provided (%s), ReflectionMethod expected.', get_class($internalReflection)));
		}

		$key = $internalReflection->getDeclaringClass()->getName() . '::' . $internalReflection->getName();
		if (!isset($cache[$key])) {
			$cache[$key] = new self($internalReflection->getDeclaringClass()->getName(), $internalReflection->getName(), $broker);
		}

		return $cache[$key];
	}
}
