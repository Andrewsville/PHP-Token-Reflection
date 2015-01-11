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
use ApiGen\TokenReflection\Broker;
use ApiGen\TokenReflection\Exception;
use Reflector, ReflectionProperty as InternalReflectionProperty;


/**
 * Reflection of a not tokenized but defined class property.
 *
 * Descendant of the internal reflection with additional features.
 */
class ReflectionProperty extends InternalReflectionProperty implements IReflection, TokenReflection\IReflectionProperty
{

	/**
	 * Reflection broker.
	 *
	 * @var ApiGen\TokenReflection\Broker
	 */
	private $broker;

	/**
	 * Is the property accessible despite its access level.
	 *
	 * @var bool
	 */
	private $accessible = FALSE;


	/**
	 * Constructor.
	 *
	 * @param string|\TokenReflection\Php\ReflectionClass|\ReflectionClass $class Defining class
	 * @param string $propertyName Property name
	 * @param ApiGen\TokenReflection\Broker $broker Reflection broker
	 */
	public function __construct($class, $propertyName, Broker $broker)
	{
		parent::__construct($class, $propertyName);
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
	 * Returns the definition start line number in the file.
	 *
	 * @return null
	 */
	public function getStartLine()
	{
		return NULL;
	}


	/**
	 * Returns the definition end line number in the file.
	 *
	 * @return null
	 */
	public function getEndLine()
	{
		return NULL;
	}


	/**
	 * Returns the appropriate docblock definition.
	 *
	 * @return bool
	 */
	public function getDocComment()
	{
		return FALSE;
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
	 * Returns the property default value.
	 *
	 * @return mixed
	 */
	public function getDefaultValue()
	{
		$values = $this->getDeclaringClass()->getDefaultProperties();
		return $values[$this->getName()];
	}


	/**
	 * Returns the part of the source code defining the property default value.
	 *
	 * @return string
	 */
	public function getDefaultValueDefinition()
	{
		$value = $this->getDefaultValue();
		return NULL === $value ? NULL : var_export($value, TRUE);
	}


	/**
	 * Returns if the property is internal.
	 *
	 * @return bool
	 */
	public function isInternal()
	{
		return $this->getDeclaringClass()->isInternal();
	}


	/**
	 * Returns if the property is user defined.
	 *
	 * @return bool
	 */
	public function isUserDefined()
	{
		return $this->getDeclaringClass()->isUserDefined();
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
	 * Returns if the reflection subject is deprecated.
	 *
	 * @return bool
	 */
	public function isDeprecated()
	{
		return FALSE;
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
	 * Returns imported namespaces and aliases from the declaring namespace.
	 *
	 * @return array
	 */
	public function getNamespaceAliases()
	{
		return [];
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
	 * Returns if the property is set accessible.
	 *
	 * @return bool
	 */
	public function isAccessible()
	{
		return $this->accessible;
	}


	/**
	 * Sets a property to be accessible or not.
	 *
	 * @param bool $accessible If the property should be accessible.
	 */
	public function setAccessible($accessible)
	{
		$this->accessible = (bool) $accessible;
		parent::setAccessible($accessible);
	}


	/**
	 * Returns the PHP extension reflection.
	 *
	 * @return ApiGen\TokenReflection\Php\ReflectionExtension
	 */
	public function getExtension()
	{
		return $this->getDeclaringClass()->getExtension();
	}


	/**
	 * Returns the PHP extension name.
	 *
	 * @return string|bool
	 */
	public function getExtensionName()
	{
		$extension = $this->getExtension();
		return $extension ? $extension->getName() : FALSE;
	}


	/**
	 * Returns the file name the reflection object is defined in.
	 *
	 * @return string
	 */
	public function getFileName()
	{
		return $this->getDeclaringClass()->getFileName();
	}


	/**
	 * Returns an element pretty (docblock compatible) name.
	 *
	 * @return string
	 */
	public function getPrettyName()
	{
		return sprintf('%s::$%s', $this->getDeclaringClassName(), $this->getName());
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


	/**
	 * Creates a reflection instance.
	 *
	 * @param \ReflectionClass $internalReflection Internal reflection instance
	 * @param ApiGen\TokenReflection\Broker $broker Reflection broker instance
	 * @return ApiGen\TokenReflection\Php\ReflectionProperty
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If an invalid internal reflection object was provided.
	 */
	public static function create(Reflector $internalReflection, Broker $broker)
	{
		static $cache = [];
		if ( ! $internalReflection instanceof InternalReflectionProperty) {
			throw new Exception\RuntimeException('Invalid reflection instance provided, ReflectionProperty expected.', Exception\RuntimeException::INVALID_ARGUMENT);
		}
		$key = $internalReflection->getDeclaringClass()->getName() . '::' . $internalReflection->getName();
		if ( ! isset($cache[$key])) {
			$cache[$key] = new self($internalReflection->getDeclaringClass()->getName(), $internalReflection->getName(), $broker);
		}
		return $cache[$key];
	}

}
