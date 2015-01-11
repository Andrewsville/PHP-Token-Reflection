<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Php;

use ApiGen;
use ApiGen\TokenReflection;
use ApiGen\TokenReflection\Broker\Broker;
use ApiGen\TokenReflection\Exception;
use ApiGen\TokenReflection\Exception\RuntimeException;
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
	 * @var Broker
	 */
	private $broker;

	/**
	 * Is the property accessible despite its access level.
	 *
	 * @var bool
	 */
	private $accessible = FALSE;


	/**
	 * @param string|\TokenReflection\Php\ReflectionClass|\ReflectionClass $class Defining class
	 * @param string $methodName Method name
	 * @param ApiGen\TokenReflection\Broker $broker Reflection broker
	 */
	public function __construct($class, $methodName, Broker $broker)
	{
		parent::__construct($class, $methodName);
		$this->broker = $broker;
	}


	/**
	 * Returns the declaring class reflection.
	 *
	 * @return ApiGen\TokenReflection\IReflectionClass
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
	 * Returns imported namespaces and aliases from the declaring namespace.
	 *
	 * @return array
	 */
	public function getNamespaceAliases()
	{
		return $this->getDeclaringClass()->getNamespaceAliases();
	}


	/**
	 * Checks if there is a particular annotation.
	 *
	 * @param string $name Annotation name
	 * @return bool
	 */
	public function hasAnnotation($name)
	{
		return FALSE;
	}


	/**
	 * Returns a particular annotation value.
	 *
	 * @param string $name Annotation name
	 * @return null
	 */
	public function getAnnotation($name)
	{
		return NULL;
	}


	/**
	 * Returns parsed docblock.
	 *
	 * @return array
	 */
	public function getAnnotations()
	{
		return [];
	}


	/**
	 * Returns if the current reflection comes from a tokenized source.
	 *
	 * @return bool
	 */
	public function isTokenized()
	{
		return FALSE;
	}


	/**
	 * Returns the method prototype.
	 *
	 * @return ApiGen\TokenReflection\Php\ReflectionMethod
	 */
	public function getPrototype()
	{
		return self::create(parent::getPrototype(), $this->broker);
	}


	/**
	 * Returns a particular parameter.
	 *
	 * @param int|string $parameter Parameter name or position
	 * @return ApiGen\TokenReflection\Php\ReflectionParameter
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If there is no parameter of the given name.
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If there is no parameter at the given position.
	 */
	public function getParameter($parameter)
	{
		$parameters = $this->getParameters();
		if (is_numeric($parameter)) {
			if ( ! isset($parameters[$parameter])) {
				throw new RuntimeException(sprintf('There is no parameter at position "%d".', $parameter), RuntimeException::DOES_NOT_EXIST, $this);
			}
			return $parameters[$parameter];
		} else {
			foreach ($parameters as $reflection) {
				if ($reflection->getName() === $parameter) {
					return $reflection;
				}
			}
			throw new RuntimeException(sprintf('There is no parameter "%s".', $parameter), RuntimeException::DOES_NOT_EXIST, $this);
		}
	}


	/**
	 * Returns function parameters.
	 *
	 * @return array
	 */
	public function getParameters()
	{
		if (NULL === $this->parameters) {
			$broker = $this->broker;
			$parent = $this;
			$this->parameters = array_map(function (InternalReflectionParameter $parameter) use ($broker, $parent) {
				return ReflectionParameter::create($parameter, $broker, $parent);
			}, parent::getParameters());
		}
		return $this->parameters;
	}


	/**
	 * Returns if the method is set accessible.
	 *
	 * @return bool
	 */
	public function isAccessible()
	{
		return $this->accessible;
	}


	/**
	 * Sets a method to be accessible or not.
	 *
	 * Introduced in PHP 5.3.2. Throws an exception if run on an older version.
	 *
	 * @param bool $accessible
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If run on PHP version < 5.3.2.
	 */
	public function setAccessible($accessible)
	{
		if (PHP_VERSION_ID < 50302) {
			throw new Exception\RuntimeException(sprintf('Method setAccessible was introduced the internal reflection in PHP 5.3.2, you are using %s.', PHP_VERSION), Exception\RuntimeException::UNSUPPORTED, $this);
		}
		$this->accessible = $accessible;
		parent::setAccessible($accessible);
	}


	/**
	 * Shortcut for isPublic(), ... methods that allows or-ed modifiers.
	 *
	 * @param int $filter Filter
	 * @return bool
	 */
	public function is($filter = NULL)
	{
		return NULL === $filter || ($this->getModifiers() & $filter);
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
	 * @return bool
	 */
	final public function __isset($key)
	{
		return TokenReflection\ReflectionElement::exists($this, $key);
	}

//	/**
//	 * Returns the function/method as closure.
//	 *
//	 * @param object $object Object
//	 * @return \Closure
//	 */
//	public function getClosure($object)
//	{
//		return parent::getClosure();
//	}
//	/**
//	 * Returns the closure scope class.
//	 *
//	 * @return string|null
//	 */
//	public function getClosureScopeClass()
//	{
//		return parent::getClosureScopeClass();
//	}
//	/**
//	 * Returns this pointer bound to closure.
//	 *
//	 * @return null
//	 */
//	public function getClosureThis()
//	{
//		return PHP_VERSION_ID >= 50400 ? parent::getClosureThis() : null;
//	}
	/**
	 * Returns the original name when importing from a trait.
	 *
	 * @return string
	 */
	public function getOriginalName()
	{
		return NULL;
	}


	/**
	 * Returns the original method when importing from a trait.
	 *
	 * @return null
	 */
	public function getOriginal()
	{
		return NULL;
	}


	/**
	 * Returns the original modifiers value when importing from a trait.
	 *
	 * @return null
	 */
	public function getOriginalModifiers()
	{
		return NULL;
	}


	/**
	 * Returns the defining trait.
	 *
	 * @return ApiGen\TokenReflection\IReflectionClass|null
	 */
	public function getDeclaringTrait()
	{
		return NULL;
	}


	/**
	 * Returns the declaring trait name.
	 *
	 * @return string|null
	 */
	public function getDeclaringTraitName()
	{
		return NULL;
	}


	/**
	 * Returns an element pretty (docblock compatible) name.
	 *
	 * @return string
	 */
	public function getPrettyName()
	{
		return sprintf('%s::%s()', $this->getDeclaringClassName(), $this->getName());
	}


	/**
	 * @return ApiGen\TokenReflection\Php\IReflection
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If an invalid internal reflection object was provided.
	 */
	public static function create(Reflector $internalReflection, Broker $broker)
	{
		static $cache = [];
		if ( ! $internalReflection instanceof InternalReflectionMethod) {
			throw new RuntimeException('Invalid reflection instance provided, ReflectionMethod expected.', RuntimeException::INVALID_ARGUMENT);
		}
		$key = $internalReflection->getDeclaringClass()->getName() . '::' . $internalReflection->getName();
		if ( ! isset($cache[$key])) {
			$cache[$key] = new self($internalReflection->getDeclaringClass()->getName(), $internalReflection->getName(), $broker);
		}
		return $cache[$key];
	}

}
