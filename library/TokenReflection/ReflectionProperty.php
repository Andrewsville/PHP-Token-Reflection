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

namespace TokenReflection;

use ReflectionProperty as InternalReflectionProperty, ReflectionClass as InternalReflectionClass;
use RuntimeException, InvalidArgumentException;

/**
 * Tokenized class property reflection.
 */
class ReflectionProperty extends ReflectionBase implements IReflectionProperty
{
	/**
	 * Property redeclares a parent's private property.
	 *
	 * @see http://svn.php.net/viewvc/php/php-src/branches/PHP_5_3/Zend/zend_compile.h?revision=306939&view=markup#l146
	 * ZEND_ACC_SHADOW
	 *
	 * @var integer
	 */
	const IS_SHADOW = 0x20000;

	/**
	 * Defines if the default value definitions should be parsed (eval-ed).
	 *
	 * @var boolean
	 */
	private static $parseValueDefinitions = false;

	/**
	 * Name of the declaring class.
	 *
	 * @var String
	 */
	private $declaringClassName;

	/**
	 * Property default value.
	 *
	 * @var mixed
	 */
	private $defaultValue;

	/**
	 * Property default value definition (part of the source code).
	 *
	 * @var string
	 */
	private $defaultValueDefinition;

	/**
	 * Property modifiers.
	 *
	 * @var integer
	 */
	private $modifiers = 0;

	/**
	 * Determined if the property value is accessible.
	 *
	 * @var boolean
	 */
	private $accessible = false;

	/**
	 * Returns the name of the declaring class.
	 *
	 * @return string
	 */
	public function getDeclaringClassName()
	{
		return $this->declaringClassName;
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
	 * Returns a reflection of the declaring class.
	 *
	 * @return \TokenReflection\ReflectionClass
	 */
	public function getDeclaringClass()
	{
		return $this->getBroker()->getClass($this->declaringClassName);
	}

	/**
	 * Returns property modifiers.
	 *
	 * @return integer
	 */
	public function getModifiers()
	{
		if (!($this->modifiers & self::IS_SHADOW)) {
			$declaringClass = $this->getDeclaringClass();
			if (null === $declaringClass) {
				throw new RuntimeException('No declaring class defined.');
			}

			$parentClass = $declaringClass->getParentClass();
			if (null !== $parentClass) {
				$parentClassProperties = $parentClass->getProperties(InternalReflectionProperty::IS_PRIVATE);
				if (isset($parentClassProperties[$this->name])) {
					$this->modifiers |= self::IS_SHADOW;
				}
			}
		}

		return $this->modifiers;
	}

	/**
	 * Returns if the property has a default value.
	 *
	 * @return boolean
	 */
	public function isDefault()
	{
		return null !== $this->defaultValueDefinition;
	}

	/**
	 * Returns the property default value.
	 *
	 * @return mixed;
	 */
	public function getDefaultValue()
	{
		return $this->defaultValue;
	}

	/**
	 * Returns the property value for a particular class instance.
	 *
	 * @return mixed;
	 */
	public function getValue($object)
	{
		$declaringClass = $this->getDeclaringClass();
		if (null === $declaringClass) {
			throw new RuntimeException('No declaring class defined.');
		}
		if (!$declaringClass->isInstance($object)) {
			throw new InvalidArgumentException(sprintf('Invalid class, %s expected %s given', $declaringClass->getName(), get_class($object)));
		}

		if ($this->isPublic()) {
			return $object->{$this->name};
		} elseif ($this->isAccessible()) {
			$refClass = new InternalReflectionClass($object);
			$refProperty = $refClass->getProperty($this->name);

			$refProperty->setAccessible(true);
			$value = $refProperty->getValue($object);
			$refProperty->setAccessible(false);

			return $value;
		}

		throw new Exception('Only public or accessible properties can return thier values');
	}

	/**
	 * Returns the part of the source code defining the property default value.
	 *
	 * @return string
	 */
	public function getDefaultValueDefinition()
	{
		return $this->defaultValueDefinition;
	}

	/**
	 * Returns if the property is private.
	 *
	 * @return boolean
	 */
	public function isPrivate()
	{
		return (bool) ($this->modifiers & InternalReflectionProperty::IS_PRIVATE);
	}

	/**
	 * Returns if the property is protected.
	 *
	 * @return boolean
	 */
	public function isProtected()
	{
		return (bool) ($this->modifiers & InternalReflectionProperty::IS_PROTECTED);
	}

	/**
	 * Returns if the property is public.
	 *
	 * @return boolean
	 */
	public function isPublic()
	{
		return (bool) ($this->modifiers & InternalReflectionProperty::IS_PUBLIC);
	}

	/**
	 * Returns if the poperty is static.
	 *
	 * @return boolean
	 */
	public function isStatic()
	{
		return (bool) ($this->modifiers & InternalReflectionProperty::IS_STATIC);
	}

	/**
	 * Sets a property to be accessible or not.
	 *
	 * @param boolean $accessible If the property should be accessible.
	 */
	public function setAccessible($accessible)
	{
		$this->accessible = (bool) $accessible;
	}

	/**
	 * Returns if the property is set accessible.
	 *
	 * @return boolean
	 */
	public function isAccessible()
	{
		return $this->accessible;
	}

	/**
	 * Returns the docblock definition of the property or its parent.
	 *
	 * @return string|false
	 */
	public function getInheritedDocComment()
	{
		if (false !== ($docComment = $this->getDocComment()) && false === strpos($docComment, '@inheritdoc')) {
			return $docComment;
		}

		$parent = $this->getDeclaringClass()->getParentClass();
		if (null !== $parent && $parent->hasProperty($this->getName())) {
			return $parent->getProperty($this->getName())->getInheritedDocComment();
		}

		return false;
	}

	/**
	 * Sets value of a property for a particular class instnace.
	 *
	 * @param object $object Class instance
	 * @param mixed $value Poperty value
	 */
	public function setValue($object, $value)
	{
		$declaringClass = $this->getDeclaringClass();
		if (null === $declaringClass) {
			throw new RuntimeException('No declaring class defined.');
		}
		if (!$declaringClass->isInstance($object)) {
			throw new InvalidArgumentException(sprintf('Invalid class, %s expected %s given', $declaringClass->getName(), get_class($object)));
		}

		if ($this->isPublic()) {
			$object->{$this->name} = $value;
		} elseif ($this->isAccessible()) {
			$refClass = new InternalReflectionClass($object);
			$refProperty = $refClass->getProperty($this->name);

			$refProperty->setAccessible(true);
			$refProperty->setValue($object, $value);
			$refProperty->setAccessible(false);

			if ($this->isStatic()) {
				$this->defaultValue = $value;

				// var_export()?
				$this->defaultValueDefinition = null;
			}
		} else {
			throw new Exception('Only public or accessible properties can set');
		}
	}

	/**
	 * Sets the property default value.
	 *
	 * @param mixed $value
	 */
	public function setDefaultValue($value)
	{
		$this->defaultValue = $value;
	}

	/**
	 * Processes the parent reflection object.
	 *
	 * @param \TokenReflection\IReflection $parent Parent reflection object
	 * @return \TokenReflection\ReflectionBase
	 */
	protected function processParent(IReflection $parent)
	{
		if (!$parent instanceof ReflectionClass) {
			throw new InvalidArgumentException(sprintf('The parent reflection object has to be a TokenReflection\ReflectionClass instance, %s given', get_class($parent)));
		}

		$this->declaringClassName = $parent->getName();
		return parent::processParent($parent);
	}

	/**
	 * Parses reflected element metadata from the token stream.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @param \TokenReflection\IReflection $parent Parent reflection object
	 * @return \TokenReflection\ReflectionProperty
	 */
	protected function parse(Stream $tokenStream, IReflection $parent)
	{
		return $this
			->parseModifiers($tokenStream, $parent)
			->parseName($tokenStream)
			->parseDefaultValue($tokenStream);
	}

	/**
	 * Parses the property name.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @return \TokenReflection\ReflectionProperty
	 */
	protected function parseName(Stream $tokenStream)
	{
		if (!$tokenStream->is(T_VARIABLE)) {
			throw new RuntimeException(sprintf('Unexpected token %s; T_VARIABLE expected', $tokenStream->getTokenName()));
		}

		$this->name = substr($tokenStream->getTokenValue(), 1);

		$tokenStream->skipWhitespaces();

		return $this;
	}

	/**
	 * Parses the propety default value.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @return \TokenReflection\ReflectionProperty
	 */
	private function parseDefaultValue(Stream $tokenStream)
	{
		$type = $tokenStream->getType();

		if (';' === $type || ',' === $type) {
			// No default value
			return $this;
		}

		if ('=' === $type) {
			$tokenStream->skipWhitespaces();
		}

		$level = 0;
		while (null !== ($type = $tokenStream->getType())) {
			switch ($type) {
				case ',':
					if (0 !== $level) {
						break;
					}
				case ';':
					break 2;
				case ')':
				case ']':
				case '}':
					$level--;
					break;
				case '(':
				case '{':
				case '[':
					$level++;

			}

			$this->defaultValueDefinition .= $tokenStream->getTokenValue();
			$tokenStream->next();

		}

		if (',' === $type) {
			$tokenStream->next();
		} elseif (';' !== $type) {
			throw new RuntimeException('Property definition is not terminated properly');
		}

		if (self::$parseValueDefinitions) {
			// Následuje husťárna (a fucking awesomness follows)
			$this->defaultValue = @eval('return ' . $this->defaultValueDefinition . ';');
		}

		return $this;
	}

	/**
	 * Parses class modifiers (abstract, final) and class type (class, interface).
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @param \TokenReflection\ReflectionClass $class Defining class
	 * @return \TokenReflection\ReflectionClass
	 */
	private function parseModifiers(Stream $tokenStream, ReflectionClass $class)
	{
		while (true) {
			switch ($tokenStream->getType()) {
				case T_PUBLIC:
				case T_VAR:
					$this->modifiers |= InternalReflectionProperty::IS_PUBLIC;
					break;
				case T_PROTECTED:
					$this->modifiers |= InternalReflectionProperty::IS_PROTECTED;
					break;
				case T_PRIVATE:
					$this->modifiers |= InternalReflectionProperty::IS_PRIVATE;
					break;
				case T_STATIC:
					$this->modifiers |= InternalReflectionProperty::IS_STATIC;
					break;
				default:
					break 2;
			}

			$tokenStream->skipWhitespaces();
		}

		if (InternalReflectionProperty::IS_STATIC === $this->modifiers) {
			$this->modifiers |= InternalReflectionProperty::IS_PUBLIC;
		} elseif (0 === $this->modifiers) {
			$parentProperties = $class->getOwnProperties();
			if (empty($parentProperties)) {
				throw new RuntimeException('No access level defined and no previous defining class property present.');
			}

			$sibling = array_pop($parentProperties);
			if ($sibling->isPublic()) {
				$this->modifiers = InternalReflectionProperty::IS_PUBLIC;
			} elseif ($sibling->isPrivate()) {
				$this->modifiers = InternalReflectionProperty::IS_PRIVATE;
			} elseif ($sibling->isProtected()) {
				$this->modifiers = InternalReflectionProperty::IS_PROTECTED;
			} else {
				throw new RuntimeException(sprintf('Property sibling %s has no access level defined.', $sibling->getName()));
			}

			if ($sibling->isStatic()) {
				$this->modifiers |= InternalReflectionProperty::IS_STATIC;
			}
		}

		return $this;
	}

	/**
	 * Sets if the default value definitions should be parsed.
	 *
	 * @param boolean $parse Should be definitions parsed
	 */
	public static function setParseValueDefinitions($parse)
	{
		self::$parseValueDefinitions = (bool) $parse;
	}

	/**
	 * Returns if the default value definitions should be parsed.
	 *
	 * @return boolean
	 */
	public static function getParseValueDefinitions()
	{
		return self::$parseValueDefinitions;
	}
}
