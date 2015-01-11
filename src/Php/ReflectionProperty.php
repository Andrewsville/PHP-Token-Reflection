<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Php;

use ApiGen\TokenReflection\Behaviors\Annotations;
use ApiGen\TokenReflection\Broker\Broker;
use ApiGen\TokenReflection\Exception\RuntimeException;
use ApiGen\TokenReflection\IReflectionProperty;
use ApiGen\TokenReflection\ReflectionElement;
use Reflector;
use ReflectionProperty as InternalReflectionProperty;


class ReflectionProperty extends InternalReflectionProperty implements IReflection, IReflectionProperty, Annotations
{

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
	 * @param string|ReflectionClass|\ReflectionClass $class Defining class
	 * @param string $propertyName Property name
	 * @param Broker $broker
	 */
	public function __construct($class, $propertyName, Broker $broker)
	{
		parent::__construct($class, $propertyName);
		$this->broker = $broker;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDeclaringClass()
	{
		return ReflectionClass::create(parent::getDeclaringClass(), $this->broker);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDeclaringClassName()
	{
		return $this->getDeclaringClass()->getName();
	}


	/**
	 * {@inheritdoc}
	 */
	public function getStartLine()
	{
		return NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getEndLine()
	{
		return NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDocComment()
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function hasAnnotation($name)
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getAnnotation($name)
	{
		return NULL;
	}


	/**
	 * {@inheritdoc}
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
	 * {@inheritdoc}
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
	 * {@inheritdoc}
	 */
	public function getBroker()
	{
		return $this->broker;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getNamespaceAliases()
	{
		return [];
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDeclaringTrait()
	{
		return NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDeclaringTraitName()
	{
		return NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isAccessible()
	{
		return $this->accessible;
	}


	/**
	 * {@inheritdoc}
	 */
	public function setAccessible($accessible)
	{
		$this->accessible = (bool) $accessible;
		parent::setAccessible($accessible);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getExtension()
	{
		return $this->getDeclaringClass()->getExtension();
	}


	/**
	 * {@inheritdoc}
	 */
	public function getExtensionName()
	{
		$extension = $this->getExtension();
		return $extension ? $extension->getName() : FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getFileName()
	{
		return $this->getDeclaringClass()->getFileName();
	}


	/**
	 * {@inheritdoc}
	 */
	public function getPrettyName()
	{
		return sprintf('%s::$%s', $this->getDeclaringClassName(), $this->getName());
	}


	/**
	 * {@inheritdoc}
	 */
	final public function __get($key)
	{
		return ReflectionElement::get($this, $key);
	}


	/**
	 * {@inheritdoc}
	 */
	final public function __isset($key)
	{
		return ReflectionElement::exists($this, $key);
	}


	/**
	 * Creates a reflection instance.
	 *
	 * @return ReflectionProperty
	 * @throws RuntimeException If an invalid internal reflection object was provided.
	 */
	public static function create(Reflector $internalReflection, Broker $broker)
	{
		static $cache = [];
		if ( ! $internalReflection instanceof InternalReflectionProperty) {
			throw new RuntimeException('Invalid reflection instance provided, ReflectionProperty expected.', RuntimeException::INVALID_ARGUMENT);
		}
		$key = $internalReflection->getDeclaringClass()->getName() . '::' . $internalReflection->getName();
		if ( ! isset($cache[$key])) {
			$cache[$key] = new self($internalReflection->getDeclaringClass()->getName(), $internalReflection->getName(), $broker);
		}
		return $cache[$key];
	}

}
