<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Reflection;

use ApiGen\TokenReflection\Broker\Broker;
use ApiGen\TokenReflection\Exception;
use ApiGen\TokenReflection\Exception\ParseException;
use ApiGen\TokenReflection\ReflectionInterface;
use ApiGen\TokenReflection\ReflectionClassInterface;
use ApiGen\TokenReflection\ReflectionPropertyInterface;
use ApiGen\TokenReflection\Parser\PropertyParser;
use ApiGen\TokenReflection\Reflection\ReflectionClass;
use ApiGen\TokenReflection\Reflection\ReflectionElement;
use ApiGen\TokenReflection\Resolver;
use ApiGen\TokenReflection\Stream\StreamBase;
use ReflectionProperty as InternalReflectionProperty;
use ReflectionClass as InternalReflectionClass;


class ReflectionProperty extends ReflectionElement implements ReflectionPropertyInterface
{

	/**
	 * Access level of this property has changed from the original implementation.
	 *
	 * @see http://svn.php.net/viewvc/php/php-src/branches/PHP_5_3/Zend/zend_compile.h?revision=306939&view=markup#l134
	 * ZEND_ACC_CHANGED
	 *
	 * @var int
	 */
	const ACCESS_LEVEL_CHANGED = 0x800;

	/**
	 * Name of the declaring class.
	 *
	 * @var string
	 */
	private $declaringClassName;

	/**
	 * Property modifiers.
	 *
	 * @var int
	 */
	private $modifiers = 0;

	/**
	 * Determines if modifiers are complete.
	 *
	 * @var bool
	 */
	private $modifiersComplete = FALSE;

	/**
	 * Property default value.
	 *
	 * @var mixed
	 */
	private $defaultValue;

	/**
	 * Property default value definition (part of the source code).
	 *
	 * @var array|string
	 */
	private $defaultValueDefinition = [];

	/**
	 * Determined if the property value is accessible.
	 *
	 * @var bool
	 */
	private $accessible = FALSE;

	/**
	 * Declaring trait name.
	 *
	 * @var string
	 */
	private $declaringTraitName;

	/**
	 * @var PropertyParser
	 */
	private $propertyParser;


	public function __construct(StreamBase $tokenStream, Broker $broker, ReflectionInterface $parent = NULL)
	{
		$this->propertyParser = new PropertyParser($tokenStream, $this, $parent);
		parent::__construct($tokenStream, $broker, $parent);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDeclaringClass()
	{
		return $this->getBroker()->getClass($this->declaringClassName);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDeclaringClassName()
	{
		return $this->declaringClassName;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDefaultValue()
	{
		if (is_array($this->defaultValueDefinition)) {
			$this->defaultValue = Resolver::getValueDefinition($this->defaultValueDefinition, $this);
			$this->defaultValueDefinition = Resolver::getSourceCode($this->defaultValueDefinition);
		}
		return $this->defaultValue;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDefaultValueDefinition()
	{
		return is_array($this->defaultValueDefinition) ? Resolver::getSourceCode($this->defaultValueDefinition) : $this->defaultValueDefinition;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getValue($object)
	{
		$declaringClass = $this->getDeclaringClass();
		if ( ! $declaringClass->isInstance($object)) {
			throw new Exception\RuntimeException('The given class is not an instance or subclass of the current class.', Exception\RuntimeException::INVALID_ARGUMENT, $this);
		}
		if ($this->isPublic()) {
			return $object->{$this->name};
		} elseif ($this->isAccessible()) {
			$refClass = new InternalReflectionClass($object);
			$refProperty = $refClass->getProperty($this->name);
			$refProperty->setAccessible(TRUE);
			$value = $refProperty->getValue($object);
			$refProperty->setAccessible(FALSE);
			return $value;
		}
		throw new Exception\RuntimeException('Only public and accessible properties can return their values.', Exception\RuntimeException::NOT_ACCESSBILE, $this);
	}


	/**
	 * {@inheritdoc}
	 */
	public function isDefault()
	{
		return TRUE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getModifiers()
	{
		if ($this->modifiersComplete === FALSE) {
			$declaringClass = $this->getDeclaringClass();
			$declaringClassParent = $declaringClass->getParentClass();
			if ($declaringClassParent && $declaringClassParent->hasProperty($this->name)) {
				$property = $declaringClassParent->getProperty($this->name);
				if (($this->isPublic() && !$property->isPublic()) || ($this->isProtected() && $property->isPrivate())) {
					$this->modifiers |= self::ACCESS_LEVEL_CHANGED;
				}
			}
			$this->modifiersComplete = ($this->modifiers & self::ACCESS_LEVEL_CHANGED) || $declaringClass->isComplete();
		}
		return $this->modifiers;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isPrivate()
	{
		return (bool) ($this->modifiers & InternalReflectionProperty::IS_PRIVATE);
	}


	/**
	 * {@inheritdoc}
	 */
	public function isProtected()
	{
		return (bool) ($this->modifiers & InternalReflectionProperty::IS_PROTECTED);
	}


	/**
	 * {@inheritdoc}
	 */
	public function isPublic()
	{
		return (bool) ($this->modifiers & InternalReflectionProperty::IS_PUBLIC);
	}


	/**
	 * {@inheritdoc}
	 */
	public function isStatic()
	{
		return (bool) ($this->modifiers & InternalReflectionProperty::IS_STATIC);
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
	 * {@inheritdoc}
	 */
	public function setAccessible($accessible)
	{
		$this->accessible = (bool) $accessible;
	}


	/**
	 * Sets the property default value.
	 *
	 * @param mixed $value
	 */
	public function setDefaultValue($value)
	{
		$this->defaultValue = $value;
		$this->defaultValueDefinition = var_export($value, TRUE);
	}


	/**
	 * {@inheritdoc}
	 */
	public function setValue($object, $value)
	{
		$declaringClass = $this->getDeclaringClass();
		if ( ! $declaringClass->isInstance($object)) {
			throw new Exception\RuntimeException('Instance of or subclass expected.', Exception\RuntimeException::INVALID_ARGUMENT, $this);
		}
		if ($this->isPublic()) {
			$object->{$this->name} = $value;
		} elseif ($this->isAccessible()) {
			$refClass = new InternalReflectionClass($object);
			$refProperty = $refClass->getProperty($this->name);
			$refProperty->setAccessible(TRUE);
			$refProperty->setValue($object, $value);
			$refProperty->setAccessible(FALSE);
			if ($this->isStatic()) {
				$this->setDefaultValue($value);
			}
		} else {
			throw new Exception\RuntimeException('Only public and accessible properties can be set.', Exception\RuntimeException::NOT_ACCESSBILE, $this);
		}
	}


	/**
	 * {@inheritdoc}
	 */
	public function getNamespaceAliases()
	{
		return $this->getDeclaringClass()->getNamespaceAliases();
	}


	/**
	 * Creates a property alias for the given class.
	 *
	 * @param ReflectionClass $parent New parent class
	 * @return ReflectionProperty
	 */
	public function alias(ReflectionClass $parent)
	{
		$property = clone $this;
		$property->declaringClassName = $parent->getName();
		return $property;
	}


	/**
	 * Returns the defining trait.
	 *
	 * @return ReflectionClassInterface|NULL
	 */
	public function getDeclaringTrait()
	{
		return NULL === $this->declaringTraitName ? NULL : $this->getBroker()->getClass($this->declaringTraitName);
	}


	/**
	 * Returns the declaring trait name.
	 *
	 * @return string|null
	 */
	public function getDeclaringTraitName()
	{
		return $this->declaringTraitName;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getPrettyName()
	{
		return sprintf('%s::$%s', $this->declaringClassName ?: $this->declaringTraitName, $this->name);
	}


	/**
	 * Processes the parent reflection object.
	 *
	 * @return ReflectionElement
	 * @throws ParseException If an invalid parent reflection object was provided.
	 */
	protected function processParent(ReflectionInterface $parent, StreamBase $tokenStream)
	{
		if ( ! $parent instanceof ReflectionClass) {
			throw new ParseException($this, $tokenStream, 'The parent object has to be an instance of TokenReflection\ReflectionClass.', ParseException::INVALID_PARENT);
		}
		$this->declaringClassName = $parent->getName();
		if ($parent->isTrait()) {
			$this->declaringTraitName = $parent->getName();
		}
	}


	/**
	 * Parses reflected element metadata from the token stream.
	 *
	 * @return ReflectionProperty
	 */
	protected function parse(StreamBase $tokenStream, ReflectionInterface $parent)
	{
		$this->modifiers = $this->propertyParser->parseModifiers();
		if (FALSE === $this->docComment->getDocComment()) {
			$this->parseDocComment($tokenStream, $parent);
		}

		$this->name = $this->propertyParser->parseName();
		$this->defaultValueDefinition = $this->propertyParser->parseDefaultValue();

		return $this;
	}

}