<?php
/**
 * PHP Token Reflection
 *
 * Version 1.0 beta 2
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

use TokenReflection\Exception;
use ReflectionProperty as InternalReflectionProperty, ReflectionClass as InternalReflectionClass;

/**
 * Tokenized class property reflection.
 */
class ReflectionProperty extends ReflectionBase implements IReflectionProperty
{
	/**
	 * Defines if the default value definitions should be parsed (eval-ed).
	 *
	 * @var boolean
	 */
	private static $parseValueDefinitions = true;

	/**
	 * Name of the declaring class.
	 *
	 * @var string
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
	 * @return mixed
	 */
	public function getDefaultValue()
	{
		if (self::$parseValueDefinitions && null === $this->defaultValue) {
			$this->defaultValue = @eval('return ' . $this->defaultValueDefinition . ';');
		}

		return $this->defaultValue;
	}

	/**
	 * Returns the property value for a particular class instance.
	 *
	 * @param object $object
	 * @return mixed
	 * @throws \TokenReflection\Exception\Runtime If it is not possible to return the property value
	 */
	public function getValue($object)
	{
		try {
			$declaringClass = $this->getDeclaringClass();
			if (!$declaringClass->isInstance($object)) {
				throw new Exception\Runtime(sprintf('Invalid class, "%s" expected "%s" given.', $declaringClass->getName(), get_class($object)), Exception\Runtime::INVALID_ARGUMENT);
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

			throw new Exception\Runtime('Only public and accessible properties can return their values.', Exception\Runtime::NOT_ACCESSBILE);
		} catch (Exception\Runtime $e) {
			throw new Exception\Runtime(sprintf('Could not get value of property "%s::$%s".', $this->declaringClassName, $this->name), 0, $e);
		}
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
	 * Sets value of a property for a particular class instance.
	 *
	 * @param object $object Class instance
	 * @param mixed $value Poperty value
	 * @throws \TokenReflection\Exception\Runtime If it is not possible to set the property value
	 */
	public function setValue($object, $value)
	{
		try {
			$declaringClass = $this->getDeclaringClass();
			if (!$declaringClass->isInstance($object)) {
				throw new Exception\Runtime(sprintf('Invalid class, "%s" expected "%s" given.', $declaringClass->getName(), get_class($object)), Exception\Runtime::INVALID_ARGUMENT);
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
				throw new Exception\Runtime('Only public and accessible properties can be set.', Exception\Runtime::NOT_ACCESSBILE);
			}
		} catch (Exception\Runtime $e) {
			throw new Exception\Runtime(sprintf('Could not set value of property "%s::$%s".', $this->declaringClassName, $this->name), 0, $e);
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
	 * @throws \TokenReflection\Exception\Parse If an invalid parent reflection object was provided
	 */
	protected function processParent(IReflection $parent)
	{
		if (!$parent instanceof ReflectionClass) {
			throw new Exception\Parse(sprintf('The parent object has to be an instance of TokenReflection\ReflectionClass, "%s" given.', get_class($parent)), Exception\Parse::INVALID_PARENT);
		}

		$this->declaringClassName = $parent->getName();
		return parent::processParent($parent);
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
	 * @throws \TokenReflection\Exception\Parse If the property name could not be determined
	 */
	protected function parseName(Stream $tokenStream)
	{
		try {
			if (!$tokenStream->is(T_VARIABLE)) {
				throw new Exception\Parse('The property name could not be determined.', Exception\Parse::PARSE_ELEMENT_ERROR);
			}

			$this->name = substr($tokenStream->getTokenValue(), 1);

			$tokenStream->skipWhitespaces();

			return $this;
		} catch (Exception $e) {
			throw new Exception\Parse('Could not parse property name.', Exception\Parse::PARSE_ELEMENT_ERROR, $e);
		}
	}

	/**
	 * Parses the propety default value.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @return \TokenReflection\ReflectionProperty
	 * @throws \TokenReflection\Exception\Parse If the property default value could not be determined
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

		try {
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
						break;
					default:
						break;
				}

				$this->defaultValueDefinition .= $tokenStream->getTokenValue();
				$tokenStream->next();

			}

			if (',' !== $type && ';' !== $type) {
				throw new Exception\Parse(sprintf('The property default value is not terminated properly. Expected "," or ";", "%s" found.', $tokenStream->getTokenName()), Exception\Parse::PARSE_ELEMENT_ERROR);
			}

			return $this;
		} catch (Exception $e) {
			throw new Exception\Parse('Could not parse property default value.', Exception\Parse::PARSE_ELEMENT_ERROR, $e);
		}
	}

	/**
	 * Parses class modifiers (abstract, final) and class type (class, interface).
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @param \TokenReflection\ReflectionClass $class Defining class
	 * @return \TokenReflection\ReflectionClass
	 * @throws \TokenReflection\Exception\Parse If the modifiers value cannot be determined
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
			try {
				$parentProperties = $class->getOwnProperties();
				if (empty($parentProperties)) {
					throw new Exception\Parse('No access level defined and no previous defining class property present.', Exception\Parse::PARSE_ELEMENT_ERROR);
				}

				$sibling = array_pop($parentProperties);
				if ($sibling->isPublic()) {
					$this->modifiers = InternalReflectionProperty::IS_PUBLIC;
				} elseif ($sibling->isPrivate()) {
					$this->modifiers = InternalReflectionProperty::IS_PRIVATE;
				} elseif ($sibling->isProtected()) {
					$this->modifiers = InternalReflectionProperty::IS_PROTECTED;
				} else {
					throw new Exception\Parse(sprintf('Property sibling "%s" has no access level defined.', $sibling->getName()), Exception\Parse::PARSE_ELEMENT_ERROR);
				}

				if ($sibling->isStatic()) {
					$this->modifiers |= InternalReflectionProperty::IS_STATIC;
				}
			} catch (Exception $e) {
				throw new Exception\Parse('Could not parse modifiers.', Exception\Parse::PARSE_ELEMENT_ERROR, $e);
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
