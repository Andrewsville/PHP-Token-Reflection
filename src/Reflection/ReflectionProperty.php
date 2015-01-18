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
use ApiGen\TokenReflection\Parser\ElementParser;
use ApiGen\TokenReflection\ReflectionInterface;
use ApiGen\TokenReflection\ReflectionClassInterface;
use ApiGen\TokenReflection\ReflectionPropertyInterface;
use ApiGen\TokenReflection\Parser\PropertyParser;
use ApiGen\TokenReflection\Resolver;
use ApiGen\TokenReflection\Stream\StreamBase;
use ReflectionProperty as InternalReflectionProperty;
use ReflectionClass as InternalReflectionClass;


class ReflectionProperty extends ReflectionElement implements ReflectionPropertyInterface
{

	/**
	 * @see http://svn.php.net/viewvc/php/php-src/branches/PHP_5_3/Zend/zend_compile.h?revision=306939&view=markup#l134
	 * ZEND_ACC_CHANGED
	 *
	 * @var int
	 */
	const ACCESS_LEVEL_CHANGED = 0x800;

	/**
	 * @var string
	 */
	private $declaringClassName;

	/**
	 * @var int
	 */
	private $modifiers = 0;

	/**
	 * @var bool
	 */
	private $modifiersComplete = FALSE;

	/**
	 * @var mixed
	 */
	private $defaultValue;

	/**
	 * @var array|string
	 */
	private $defaultValueDefinition = [];

	/**
	 * @var bool
	 */
	private $accessible = FALSE;

	/**
	 * @var string
	 */
	private $declaringTraitName;


	public function __construct(StreamBase $tokenStream, Broker $broker, ReflectionClass $parent = NULL)
	{
		$this->broker = $broker;
		$this->parse($tokenStream, $parent);
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
	 * @return ReflectionProperty
	 */
	public function alias(ReflectionClass $parent)
	{
		$property = clone $this;
		$property->declaringClassName = $parent->getName();
		return $property;
	}


	/**
	 * @return ReflectionClassInterface|NULL
	 */
	public function getDeclaringTrait()
	{
		return $this->declaringTraitName === NULL ? NULL : $this->getBroker()->getClass($this->declaringTraitName);
	}


	/**
	 * @return string|NULL
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


	private function parse(StreamBase $tokenStream, ReflectionClass $parent)
	{
		$propertyParser = new PropertyParser($tokenStream, $this, $parent);
		$elementParser = new ElementParser($tokenStream, $this, $parent);

		$this->fileName = $tokenStream->getFileName();
		$this->declaringClassName = $parent->getName();
		if ($parent->isTrait()) {
			$this->declaringTraitName = $parent->getName();
		}

		$this->startLine = $elementParser->parseLineNumber();
		$this->startPosition = $elementParser->parsePosition();

		list($this->docComment, $this->startPosition) = $elementParser->parseDocComment($this->startPosition);

		$this->modifiers = $propertyParser->parseModifiers();

		if ($this->docComment->getDocComment() === FALSE) {
			list($this->docComment, $this->startPosition) = $elementParser->parseDocComment($this->startPosition);
		}

		$this->name = $propertyParser->parseName();
		$this->defaultValueDefinition = $propertyParser->parseDefaultValue();

		$this->endLine = $elementParser->parseLineNumber();
		$this->endPosition = $elementParser->parsePosition();
	}

}
