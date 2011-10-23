<?php
/**
 * PHP Token Reflection
 *
 * Version 1.0.0 RC 1
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this library in the file LICENSE.
 *
 * @author Ondřej Nešpor
 * @author Jaroslav Hanslík
 */

namespace TokenReflection;

use TokenReflection\Exception, TokenReflection\Stream\StreamBase as Stream;
use ReflectionProperty as InternalReflectionProperty, ReflectionClass as InternalReflectionClass;

/**
 * Tokenized class property reflection.
 */
class ReflectionProperty extends ReflectionBase implements IReflectionProperty
{
	/**
	 * Access level of this property has changed from the original implementation.
	 *
	 * @see http://svn.php.net/viewvc/php/php-src/branches/PHP_5_3/Zend/zend_compile.h?revision=306939&view=markup#l134
	 * ZEND_ACC_CHANGED
	 *
	 * @var integer
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
	 * @var integer
	 */
	private $modifiers = 0;

	/**
	 * Determines if modifiers are complete.
	 *
	 * @var boolean
	 */
	private $modifiersComplete = false;

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
	private $defaultValueDefinition = array();

	/**
	 * Determined if the property value is accessible.
	 *
	 * @var boolean
	 */
	private $accessible = false;

	/**
	 * Declaring trait name.
	 *
	 * @var string
	 */
	private $declaringTraitName;

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
	 * Returns the name of the declaring class.
	 *
	 * @return string
	 */
	public function getDeclaringClassName()
	{
		return $this->declaringClassName;
	}

	/**
	 * Returns the property default value.
	 *
	 * @return mixed
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
	 * Returns the part of the source code defining the property default value.
	 *
	 * @return string
	 */
	public function getDefaultValueDefinition()
	{
		return is_array($this->defaultValueDefinition) ? Resolver::getSourceCode($this->defaultValueDefinition) : $this->defaultValueDefinition;
	}

	/**
	 * Returns the property value for a particular class instance.
	 *
	 * @param object $object
	 * @return mixed
	 * @throws \TokenReflection\Exception\Runtime If it is not possible to return the property value.
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
	 * Returns if the property was created at compile time.
	 *
	 * All properties in the source code are.
	 *
	 * @return boolean
	 */
	public function isDefault()
	{
		return true;
	}

	/**
	 * Returns property modifiers.
	 *
	 * @return integer
	 */
	public function getModifiers()
	{
		if (false === $this->modifiersComplete) {
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
	 * Returns the string representation of the reflection object.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return sprintf(
			"Property [ %s%s%s%s%s\$%s ]\n",
			$this->isStatic() ? '' : '<default> ',
			$this->isPublic() ? 'public ' : '',
			$this->isPrivate() ? 'private ' : '',
			$this->isProtected() ? 'protected ' : '',
			$this->isStatic() ? 'static ' : '',
			$this->getName()
		);
	}

	/**
	 * Exports a reflected object.
	 *
	 * @param \TokenReflection\Broker $broker Broker instance
	 * @param string|object $class Class name or class instance
	 * @param string $property Property name
	 * @param boolean $return Return the export instead of outputting it
	 * @return string|null
	 * @throws \TokenReflection\Exception\Runtime If requested parameter doesn't exist.
	 */
	public static function export(Broker $broker, $class, $property, $return = false)
	{
		$className = is_object($class) ? get_class($class) : $class;
		$propertyName = $property;

		$class = $broker->getClass($className);
		if ($class instanceof Dummy\ReflectionClass) {
			throw new Exception\Runtime(sprintf('Class %s does not exist.', $className), Exception\Runtime::DOES_NOT_EXIST);
		}
		$property = $class->getProperty($propertyName);

		if ($return) {
			return $property->__toString();
		}

		echo $property->__toString();
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
	 * Sets a property to be accessible or not.
	 *
	 * @param boolean $accessible If the property should be accessible.
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
		$this->defaultValueDefinition = var_export($value, true);
	}

	/**
	 * Sets value of a property for a particular class instance.
	 *
	 * @param object $object Class instance
	 * @param mixed $value Poperty value
	 * @throws \TokenReflection\Exception\Runtime If it is not possible to set the property value.
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
					$this->setDefaultValue($value);
				}
			} else {
				throw new Exception\Runtime('Only public and accessible properties can be set.', Exception\Runtime::NOT_ACCESSBILE);
			}
		} catch (Exception\Runtime $e) {
			throw new Exception\Runtime(sprintf('Could not set value of property "%s::$%s".', $this->declaringClassName, $this->name), 0, $e);
		}
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
	 * Creates a property alias for the given class.
	 *
	 * @param \TokenReflection\ReflectionClass $parent New parent class
	 * @return \TokenReflection\ReflectionProperty
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
	 * @return \TokenReflection\IReflectionClass|null
	 */
	public function getDeclaringTrait()
	{
		return null === $this->declaringTraitName ? null : $this->getBroker()->getClass($this->declaringTraitName);
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
	 * Processes the parent reflection object.
	 *
	 * @param \TokenReflection\IReflection $parent Parent reflection object
	 * @return \TokenReflection\ReflectionBase
	 * @throws \TokenReflection\Exception\Parse If an invalid parent reflection object was provided.
	 */
	protected function processParent(IReflection $parent)
	{
		if (!$parent instanceof ReflectionClass) {
			throw new Exception\Parse(sprintf('The parent object has to be an instance of TokenReflection\ReflectionClass, "%s" given.', get_class($parent)), Exception\Parse::INVALID_PARENT);
		}

		$this->declaringClassName = $parent->getName();
		if ($parent->isTrait()) {
			$this->declaringTraitName = $parent->getName();
		}
		return parent::processParent($parent);
	}

	/**
	 * Parses reflected element metadata from the token stream.
	 *
	 * @param \TokenReflection\Stream\StreamBase $tokenStream Token substream
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
	 * Parses class modifiers (abstract, final) and class type (class, interface).
	 *
	 * @param \TokenReflection\Stream\StreamBase $tokenStream Token substream
	 * @param \TokenReflection\ReflectionClass $class Defining class
	 * @return \TokenReflection\ReflectionClass
	 * @throws \TokenReflection\Exception\Parse If the modifiers value cannot be determined.
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
	 * Parses the property name.
	 *
	 * @param \TokenReflection\Stream\StreamBase $tokenStream Token substream
	 * @return \TokenReflection\ReflectionProperty
	 * @throws \TokenReflection\Exception\Parse If the property name could not be determined.
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
	 * @param \TokenReflection\Stream\StreamBase $tokenStream Token substream
	 * @return \TokenReflection\ReflectionProperty
	 * @throws \TokenReflection\Exception\Parse If the property default value could not be determined.
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

				$this->defaultValueDefinition[] = $tokenStream->current();
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
}
