<?php
/**
 * PHP Token Reflection
 *
 * Version 1.0.0 beta 6
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

use TokenReflection\Exception;
use ReflectionClass as InternalReflectionClass, ReflectionProperty as InternalReflectionProperty, ReflectionMethod as InternalReflectionMethod;

/**
 * Tokenized class reflection.
 */
class ReflectionClass extends ReflectionBase implements IReflectionClass
{
	/**
	 * Modifier for determining if the reflected object is an interface.
	 *
	 * @var integer
	 * @see http://svn.php.net/viewvc/php/php-src/branches/PHP_5_3/Zend/zend_compile.h?revision=306939&view=markup#l122
	 */
	const IS_INTERFACE = 0x80;

	/**
	 * Modifier for determining if the reflected object is a trait.
	 *
	 * @var integer
	 * @see http://svn.php.net/viewvc/php/php-src/trunk/Zend/zend_compile.h?revision=306938&view=markup#l150
	 */
	const IS_TRAIT = 0x120;

	/**
	 * Class implements interfaces.
	 *
	 * @see http://svn.php.net/viewvc/php/php-src/branches/PHP_5_3/Zend/zend_compile.h?revision=306939&view=markup#l152
	 *
	 * @var integer
	 */
	const IMPLEMENTS_INTERFACES = 0x80000;

	/**
	 * Class implements traits.
	 *
	 * @see http://svn.php.net/viewvc/php/php-src/trunk/Zend/zend_compile.h?revision=306938&view=markup#l181
	 *
	 * @var integer
	 */
	const IMPLEMENTS_TRAITS = 0x400000;

	/**
	 * Class namespace name.
	 *
	 * @var string
	 */
	private $namespaceName;

	/**
	 * Class modifiers.
	 *
	 * @var integer
	 */
	private $modifiers = 0;

	/**
	 * Class type (class/interface/trait).
	 *
	 * @var integer
	 */
	private $type = 0;

	/**
	 * Determines if modifiers are complete.
	 *
	 * @var boolean
	 */
	private $modifiersComplete = false;

	/**
	 * Parent class name.
	 *
	 * @var string
	 */
	private $parentClassName;

	/**
	 * Implemented interface names.
	 *
	 * @var array
	 */
	private $interfaces = array();

	/**
	 * Used trait names.
	 *
	 * @var array
	 */
	private $traits = array();

	/**
	 * Aliases used at trait methods.
	 *
	 * Compatible with the internal reflection.
	 *
	 * @var array
	 */
	private $traitAliases = array();

	/**
	 * Trait importing rules.
	 *
	 * [<trait>::]<method> => array(
	 *    array(<new-name>, [<access-level>])|null
	 * 	  [, ...]
	 * )
	 *
	 * @var array
	 */
	private $traitImports = array();

	/**
	 * Stores if the class definition is complete.
	 *
	 * @var array
	 */
	private $methods = array();

	/**
	 * Constant reflections.
	 *
	 * @var array
	 */
	private $constants = array();

	/**
	 * Properties reflections.
	 *
	 * @var array
	 */
	private $properties = array();

	/**
	 * Stores if the class definition is complete.
	 *
	 * @var boolean
	 */
	private $definitionComplete = false;

	/**
	 * Imported namespace/class aliases.
	 *
	 * @var array
	 */
	private $aliases = array();

	/**
	 * Returns the unqualified name (UQN).
	 *
	 * @return string
	 */
	public function getShortName()
	{
		$name = $this->getName();
		if ($this->namespaceName !== ReflectionNamespace::NO_NAMESPACE_NAME) {
			$name = substr($name, strlen($this->namespaceName) + 1);
		}

		return $name;
	}

	/**
	 * Returns the namespace name.
	 *
	 * @return string
	 */
	public function getNamespaceName()
	{
		return $this->namespaceName === ReflectionNamespace::NO_NAMESPACE_NAME ? '' : $this->namespaceName;
	}

	/**
	 * Returns if the class is defined within a namespace.
	 *
	 * @return boolean
	 */
	public function inNamespace()
	{
		return null !== $this->namespaceName && ReflectionNamespace::NO_NAMESPACE_NAME !== $this->namespaceName;
	}

	/**
	 * Returns modifiers.
	 *
	 * @return array
	 */
	public function getModifiers()
	{
		if (false === $this->modifiersComplete) {
			if (($this->modifiers & InternalReflectionClass::IS_EXPLICIT_ABSTRACT) && !($this->modifiers & InternalReflectionClass::IS_IMPLICIT_ABSTRACT)) {
				foreach ($this->getMethods() as $reflectionMethod) {
					if ($reflectionMethod->isAbstract()) {
						$this->modifiers |= InternalReflectionClass::IS_IMPLICIT_ABSTRACT;
					}
				}

				if (!empty($this->interfaces)) {
					$this->modifiers |= InternalReflectionClass::IS_IMPLICIT_ABSTRACT;
				}
			}

			if (!empty($this->interfaces)) {
				$this->modifiers |= self::IMPLEMENTS_INTERFACES;
			}

			if ($this->isInterface() && !empty($this->methods)) {
				$this->modifiers |= InternalReflectionClass::IS_IMPLICIT_ABSTRACT;
			}

			if (!empty($this->traits)) {
				$this->modifiers |= self::IMPLEMENTS_TRAITS;
			}

			$this->modifiersComplete = true;
			foreach ($this->getParentClasses() as $parentClass) {
				if ($parentClass instanceof Dummy\ReflectionClass) {
					$this->modifiersComplete = false;
					break;
				}
			}
			if ($this->modifiersComplete) {
				foreach ($this->getInterfaces() as $interface) {
					if ($interface instanceof Dummy\ReflectionClass) {
						$this->modifiersComplete = false;
						break;
					}
				}
			}
			if ($this->modifiersComplete) {
				foreach ($this->getTraits() as $trait) {
					if ($trait instanceof Dummy\ReflectionClass) {
						$this->modifiersComplete = false;
						break;
					}
				}
			}
		}

		return $this->modifiers;
	}

	/**
	 * Returns if the class is abstract.
	 *
	 * @return boolean
	 */
	public function isAbstract()
	{
		if ($this->modifiers & InternalReflectionClass::IS_EXPLICIT_ABSTRACT) {
			return true;
		} elseif ($this->isInterface() && !empty($this->methods)) {
			return true;
		}

		return false;
	}

	/**
	 * Returns if the class is final.
	 *
	 * @return boolean
	 */
	public function isFinal()
	{
		return (bool) ($this->modifiers & InternalReflectionClass::IS_FINAL);
	}

	/**
	 * Returns if the class is an interface.
	 *
	 * @return boolean
	 */
	public function isInterface()
	{
		return (bool) ($this->modifiers & self::IS_INTERFACE);
	}

	/**
	 * Returns if the class is an exception or its descendant.
	 *
	 * @return boolean
	 */
	public function isException()
	{
		return 'Exception' === $this->name || $this->isSubclassOf('Exception');
	}

	/**
	 * Returns if it is possible to create an instance of this class.
	 *
	 * @return boolean
	 */
	public function isInstantiable()
	{
		if ($this->isInterface() || $this->isAbstract()) {
			return false;
		}

		if (null === ($constructor = $this->getConstructor())) {
			return true;
		}

		return $constructor->isPublic();
	}

	/**
	 * Returns if objects of this class are cloneable.
	 *
	 * Introduced in PHP 5.4.
	 *
	 * @return boolean
	 * @see http://svn.php.net/viewvc/php/php-src/trunk/ext/reflection/php_reflection.c?revision=307971&view=markup#l4059
	 */
	public function isCloneable()
	{
		if ($this->isInterface() || $this->isAbstract()) {
			return false;
		}

		if ($this->hasMethod('__clone')) {
			return $this->getMethod('__clone')->isPublic();
		}

		return true;
	}

	/**
	 * Returns if the class is iterateable.
	 *
	 * Returns true if the class implements the Traversable interface.
	 *
	 * @return boolean
	 * @todo traits
	 */
	public function isIterateable()
	{
		return $this->implementsInterface('Traversable');
	}

	/**
	 * Returns if the current class is a subclass of the given class.
	 *
	 * @param string|object $class Class name or reflection object
	 * @return boolean
	 * @throws \TokenReflection\Exception\Runtime If the provided parameter is not a reflection class instance
	 */
	public function isSubclassOf($class)
	{
		if (is_object($class)) {
			if (!$class instanceof InternalReflectionClass && !$class instanceof IReflectionClass) {
				throw new Exception\Runtime(sprintf('Parameter must be a string or an instance of class reflection, "%s" provided.', get_class($class)), Exception\Runtime::INVALID_ARGUMENT);
			}

			$class = $class->getName();
		}

		if ($class === $this->parentClassName) {
			return true;
		}

		$parent = $this->getParentClass();
		return false === $parent ? false : $parent->isSubclassOf($class);
	}

	/**
	 * Returns the parent class reflection.
	 *
	 * @return \TokenReflection\ReflectionClass|boolean
	 */
	public function getParentClass()
	{
		$className = $this->getParentClassName();
		if (null === $className) {
			return false;
		}

		return $this->getBroker()->getClass($className);
	}

	/**
	 * Returns the parent class name.
	 *
	 * @return string|null
	 */
	public function getParentClassName()
	{
		return $this->parentClassName;
	}

	/**
	 * Returns the parent classes reflections.
	 *
	 * @return array
	 */
	public function getParentClasses()
	{
		$parent = $this->getParentClass();
		if (false === $parent) {
			return array();
		}

		return array_merge(array($parent->getName() => $parent), $parent->getParentClasses());
	}

	/**
	 * Returns the parent classes names.
	 *
	 * @return array
	 */
	public function getParentClassNameList()
	{
		$parent = $this->getParentClass();
		if (false === $parent) {
			return array();
		}

		return array_merge(array($parent->getName()), $parent->getParentClassNameList());
	}

	/**
	 * Returns if the class implements the given interface.
	 *
	 * @param string|object $interface Interface name or reflection object
	 * @return boolean
	 * @throws \TokenReflection\Exception\Runtime If the provided parameter is not an interface
	 */
	public function implementsInterface($interface)
	{
		if (is_object($interface)) {
			if (!$interface instanceof InternalReflectionClass && !$interface instanceof IReflectionClass) {
				throw new Exception\Runtime(sprintf('Parameter must be a string or an instance of class reflection, "%s" provided.', get_class($interface)), Exception\Runtime::INVALID_ARGUMENT);
			}

			$interfaceName = $interface->getName();

			if (!$interface->isInterface()) {
				throw new Exception\Runtime(sprintf('"%s" is not an interface.', $interfaceName), Exception\Runtime::INVALID_ARGUMENT);
			}
		} else {
			$reflection = $this->getBroker()->getClass($interface);
			if (!$reflection->isInterface()) {
				throw new Exception\Runtime(sprintf('"%s" is not an interface.', $interface), Exception\Runtime::INVALID_ARGUMENT);
			}

			$interfaceName = $interface;
		}

		return in_array($interfaceName, $this->getInterfaceNames());
	}

	/**
	 * Returns interface reflections.
	 *
	 * @return array
	 */
	public function getInterfaces()
	{
		$interfaceNames = $this->getInterfaceNames();
		if (empty($interfaceNames)) {
			return array();
		}

		$broker = $this->getBroker();
		return array_combine($interfaceNames, array_map(function($interfaceName) use ($broker) {
			return $broker->getClass($interfaceName);
		}, $interfaceNames));
	}

	/**
	 * Returns interface names.
	 *
	 * @return array
	 */
	public function getInterfaceNames()
	{
		$parentClass = $this->getParentClass();

		$names = false !== $parentClass ? array_reverse(array_flip($parentClass->getInterfaceNames())) : array();
		foreach ($this->interfaces as $interfaceName) {
			$names[$interfaceName] = true;
			foreach (array_reverse($this->getBroker()->getClass($interfaceName)->getInterfaceNames()) as $parentInterfaceName) {
				$names[$parentInterfaceName] = true;
			}
		}

		return array_keys($names);
	}

	/**
	 * Returns reflections of interfaces implemented by this class, not its parents.
	 *
	 * @return array
	 */
	public function getOwnInterfaces()
	{
		$interfaceNames = $this->getOwnInterfaceNames();
		if (empty($interfaceNames)) {
			return array();
		}

		$broker = $this->getBroker();
		return array_combine($interfaceNames, array_map(function($interfaceName) use ($broker) {
			return $broker->getClass($interfaceName);
		}, $interfaceNames));
	}

	/**
	 * Returns names of interfaces implemented by this class, not its parents.
	 *
	 * @return array
	 */
	public function getOwnInterfaceNames()
	{
		return $this->interfaces;
	}

	/**
	 * Returns the class constructor reflection.
	 *
	 * @return \TokenReflection\ReflectionMethod|null
	 */
	public function getConstructor()
	{
		foreach ($this->getMethods() as $method) {
			if ($method->isConstructor()) {
				return $method;
			}
		}

		return null;
	}

	/**
	 * Returns the class destructor reflection.
	 *
	 * @return \TokenReflection\ReflectionMethod|null
	 */
	public function getDestructor()
	{
		foreach ($this->getMethods() as $method) {
			if ($method->isDestructor()) {
				return $method;
			}
		}

		return null;
	}

	/**
	 * Returns if the class implements the given method.
	 *
	 * @param string $name Method name
	 * @return boolean
	 */
	public function hasMethod($name)
	{
		foreach ($this->getMethods() as $method) {
			if ($name === $method->getName()) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns a method reflection.
	 *
	 * @param string $name Method name
	 * @return \TokenReflection\ReflectionMethod
	 * @throws \TokenReflection\Exception\Runtime If the requested method does not exist
	 */
	public function getMethod($name)
	{
		if (isset($this->methods[$name])) {
			return $this->methods[$name];
		}

		foreach ($this->getMethods() as $method) {
			if ($name === $method->getName()) {
				return $method;
			}
		}

		throw new Exception\Runtime(sprintf('There is no method "%s" in class "%s".', $name, $this->name), Exception\Runtime::DOES_NOT_EXIST);
	}

	/**
	 * Returns method reflections.
	 *
	 * @param integer $filter Methods filter
	 * @return array
	 */
	public function getMethods($filter = null)
	{
		$methods = $this->methods;

		foreach ($this->getTraitMethods() as $traitMethod) {
			if (!isset($methods[$traitMethod->getName()])) {
				$methods[$traitMethod->getName()] = $traitMethod;
			}
		}

		if (null !== $this->parentClassName) {
			foreach ($this->getParentClass()->getMethods(null) as $parentMethod) {
				if (!isset($methods[$parentMethod->getName()])) {
					$methods[$parentMethod->getName()] = $parentMethod;
				}
			}
		}
		foreach ($this->getOwnInterfaces() as $interface) {
			foreach ($interface->getMethods(null) as $parentMethod) {
				if (!isset($methods[$parentMethod->getName()])) {
					$methods[$parentMethod->getName()] = $parentMethod;
				}
			}
		}

		if (null !== $filter) {
			$methods = array_filter($methods, function(IReflectionMethod $method) use ($filter) {
				return $method->is($filter);
			});
		}

		return array_values($methods);
	}

	/**
	 * Returns if the class implements (and not its parents) the given method.
	 *
	 * @param string $name Method name
	 * @return boolean
	 */
	public function hasOwnMethod($name)
	{
		return isset($this->methods[$name]);
	}

	/**
	 * Returns reflections of methods declared by this class, not its parents.
	 *
	 * @param integer $filter Methods filter
	 * @return array
	 */
	public function getOwnMethods($filter = null)
	{
		$methods = $this->methods;

		if (null !== $filter) {
			$methods = array_filter($methods, function(ReflectionMethod $method) use ($filter) {
				return $method->is($filter);
			});
		}

		return array_values($methods);
	}

	/**
	 * Returns if the class imports the given method from traits.
	 *
	 * @param string $name Method name
	 * @return boolean
	 */
	public function hasTraitMethod($name)
	{
		if (isset($this->methods[$name])) {
			return false;
		}

		foreach ($this->getOwnTraits() as $trait) {
			if ($trait->hasMethod($name)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns reflections of method imported from traits.
	 *
	 * @param integer $filter Methods filter
	 * @return array
	 */
	public function getTraitMethods($filter = null)
	{
		$methods = array();

		foreach ($this->getOwnTraits() as $trait) {
			$traitName = $trait->getName();
			foreach ($trait->getMethods(null) as $traitMethod) {
				$methodName = $traitMethod->getName();

				$imports = array();
				if (isset($this->traitImports[$traitName . '::' . $methodName])) {
					$imports = $this->traitImports[$traitName . '::' . $methodName];
				}
				if (isset($this->traitImports[$methodName])) {
					$imports = empty($imports) ? $this->traitImports[$methodName] : array_merge($imports, $this->traitImports[$methodName]);
				}

				foreach ($imports as $import) {
					if (null !== $import) {
						list($newName, $accessLevel) = $import;

						if ('' === $newName) {
							$newName = $methodName;
							$imports[] = null;
						}

						if (isset($methods[$newName])) {
							throw new Exception\Runtime(sprintf('Trait method "%s" was already imported.', $newName), Exception\Runtime::ALREADY_EXISTS);
						}

						$methods[$newName] = $traitMethod->alias($this, $newName, $accessLevel);
					}
				}

				if (!in_array(null, $imports)) {
					if (isset($methods[$methodName])) {
						throw new Exception\Runtime(sprintf('Trait method "%s" was already imported.', $methodName), Exception\Runtime::ALREADY_EXISTS);
					}

					$methods[$methodName] = $traitMethod->alias($this);
				}
			}
		}

		if (null !== $filter) {
			$methods = array_filter($methods, function(IReflectionMethod $method) use ($filter) {
				return (bool) ($method->getModifiers() & $filter);
			});
		}

		return array_values($methods);
	}

	/**
	 * Returns if the class defines the given constant.
	 *
	 * @param string $name Constant name.
	 * @return boolean
	 */
	public function hasConstant($name)
	{
		if (isset($this->constants[$name])) {
			return true;
		}

		foreach ($this->getConstantReflections() as $constant) {
			if ($name === $constant->getName()) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns a constant value.
	 *
	 * @param string $name Constant name
	 * @return mixed|boolean
	 * @throws \TokenReflection\Exception\Runtime On error
	 */
	public function getConstant($name)
	{
		try {
			return $this->getConstantReflection($name)->getValue();
		} catch (Exception\Runtime $e) {
			if ($e->getCode() === Exception\Runtime::DOES_NOT_EXIST) {
				return false;
			}

			throw $e;
		}
	}

	/**
	 * Returns a constant reflection.
	 *
	 * @param string $name Constant name
	 * @return \TokenReflection\ReflectionConstant
	 * @throws \TokenReflection\Exception\Runtime If the requested constant does not exist
	 */
	public function getConstantReflection($name)
	{
		if (isset($this->constants[$name])) {
			return $this->constants[$name];
		}

		foreach ($this->getConstantReflections() as $constant) {
			if ($name === $constant->getName()) {
				return $constant;
			}
		}

		throw new Exception\Runtime(sprintf('There is no constant "%s" in class "%s".', $name, $this->name), Exception\Runtime::DOES_NOT_EXIST);
	}

	/**
	 * Returns constant values.
	 *
	 * @return array
	 */
	public function getConstants()
	{
		$constants = array();
		foreach ($this->getConstantReflections() as $constant) {
			$constants[$constant->getName()] = $constant->getValue();
		}
		return $constants;
	}

	/**
	 * Returns constant reflections.
	 *
	 * @return array
	 */
	public function getConstantReflections()
	{
		if (null === $this->parentClassName) {
			return array_values($this->constants);
		} else {
			$reflections = array_values($this->constants);

			if (null !== $this->parentClassName) {
				$reflections = array_merge($reflections, $this->getParentClass()->getConstantReflections());
			}
			foreach ($this->getOwnInterfaces() as $interface) {
				$reflections = array_merge($reflections, $interface->getConstantReflections());
			}

			return $reflections;
		}
	}

	/**
	 * Returns if the class (and not its parents) defines the given constant.
	 *
	 * @param string $name Constant name.
	 * @return boolean
	 */
	public function hasOwnConstant($name)
	{
		return isset($this->constants[$name]);
	}

	/**
	 * Returns constants declared by this class, not by its parents.
	 *
	 * @return array
	 */
	public function getOwnConstants()
	{
		return array_map(function(ReflectionConstant $constant) {
			return $constant->getValue();
		}, $this->constants);
	}

	/**
	 * Returns reflections of constants declared by this class, not by its parents.
	 *
	 * @return array
	 */
	public function getOwnConstantReflections()
	{
		return array_values($this->constants);
	}

	/**
	 * Returns if the class defines the given property.
	 *
	 * @param string $name Property name
	 * @return boolean
	 */
	public function hasProperty($name)
	{
		foreach ($this->getProperties() as $property) {
			if ($name === $property->getName()) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Return a property reflection.
	 *
	 * @param string $name Property name
	 * @return \TokenReflection\ReflectionProperty
	 * @throws \TokenReflection\Exception\Runtime If the requested property does not exist
	 */
	public function getProperty($name)
	{
		if (isset($this->properties[$name])) {
			return $this->properties[$name];
		}

		foreach ($this->getProperties() as $property) {
			if ($name === $property->getName()) {
				return $property;
			}
		}

		throw new Exception\Runtime(sprintf('There is no property "%s" in class "%s".', $name, $this->name), Exception\Runtime::DOES_NOT_EXIST);
	}

	/**
	 * Returns property reflections.
	 *
	 * @param integer $filter Properties filter
	 * @return array
	 */
	public function getProperties($filter = null)
	{
		$properties = $this->properties;

		foreach ($this->getTraitProperties(null) as $traitProperty) {
			if (!isset($properties[$traitProperty->getName()])) {
				$properties[$traitProperty->getName()] = $traitProperty->alias($this);
			}
		}

		if (null !== $this->parentClassName) {
			foreach ($this->getParentClass()->getProperties(null) as $parentProperty) {
				if (!isset($properties[$parentProperty->getName()])) {
					$properties[$parentProperty->getName()] = $parentProperty;
				}
			}
		}

		if (null !== $filter) {
			$properties = array_filter($properties, function(IReflectionProperty $property) use ($filter) {
				return (bool) ($property->getModifiers() & $filter);
			});
		}

		return array_values($properties);
	}

	/**
	 * Returns if the class (and not its parents) defines the given property.
	 *
	 * @param string $name Property name
	 * @return boolean
	 */
	public function hasOwnProperty($name)
	{
		return isset($this->properties[$name]);
	}

	/**
	 * Returns reflections of properties declared by this class, not its parents.
	 *
	 * @param integer $filter Properties filter
	 * @return array
	 */
	public function getOwnProperties($filter = null)
	{
		$properties = $this->properties;

		if (null !== $filter) {
			$properties = array_filter($properties, function(ReflectionProperty $property) use ($filter) {
				return (bool) ($property->getModifiers() & $filter);
			});
		}

		return array_values($properties);
	}

	/**
	 * Returns if the class imports the given property from traits.
	 *
	 * @param string $name Property name
	 * @return boolean
	 */
	public function hasTraitProperty($name)
	{
		if (isset($this->properties[$name])) {
			return false;
		}

		foreach ($this->getOwnTraits() as $trait) {
			if ($trait->hasProperty($name)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns reflections of properties imported from traits.
	 *
	 * @param integer $filter Properties filter
	 * @return array
	 */
	public function getTraitProperties($filter = null)
	{
		$properties = array();

		foreach ($this->getOwnTraits() as $trait) {
			foreach ($trait->getProperties(null) as $traitProperty) {
				if (!isset($this->properties[$traitProperty->getName()]) && !isset($properties[$traitProperty->getName()])) {
					$properties[$traitProperty->getName()] = $traitProperty->alias($this);
				}
			}
		}

		if (null !== $filter) {
			$properties = array_filter($properties, function(IReflectionProperty $property) use ($filter) {
				return (bool) ($property->getModifiers() & $filter);
			});
		}

		return array_values($properties);
	}

	/**
	 * Returns default properties.
	 *
	 * @return array
	 */
	public function getDefaultProperties()
	{
		static $accessLevels = array(InternalReflectionProperty::IS_PUBLIC, InternalReflectionProperty::IS_PROTECTED, InternalReflectionProperty::IS_PRIVATE);

		$defaults = array();
		$properties = $this->getProperties();
		foreach (array(true, false) as $static) {
			foreach ($properties as $property) {
				foreach ($accessLevels as $level) {
					if ($property->isStatic() === $static && ($property->getModifiers() & $level)) {
						$defaults[$property->getName()] = $property->getDefaultValue();
					}
				}
			}
		}

		return $defaults;
	}

	/**
	 * Returns static properties reflections.
	 *
	 * @return array
	 */
	public function getStaticProperties()
	{
		$defaults = array();
		foreach ($this->getProperties(InternalReflectionProperty::IS_STATIC) as $property) {
			if ($property instanceof ReflectionProperty) {
				$defaults[$property->getName()] = $property->getDefaultValue();
			}
		}

		return $defaults;
	}

	/**
	 * Returns a value of a static property.
	 *
	 * @param string $name Property name
	 * @param mixed $default Default value
	 * @return mixed
	 * @throws \TokenReflection\Exception\Runtime If the requested static property does not exist
	 * @throws \TokenReflection\Exception\Runtime If the requested static property is not accessible
	 */
	public function getStaticPropertyValue($name, $default = null)
	{
		if ($this->hasProperty($name) && ($property = $this->getProperty($name)) && $property->isStatic()) {
			if (!$property->isPublic() && !$property->isAccessible()) {
				throw new Exception\Runtime(sprintf('Static property "%s" in class "%s" is not accessible.', $name, $this->name), Exception\Runtime::NOT_ACCESSBILE);
			}

			return $property->getDefaultValue();
		}

		throw new Exception\Runtime(sprintf('There is no static property "%s" in class "%s".', $name, $this->name), Exception\Runtime::DOES_NOT_EXIST);
	}

	/**
	 * Returns traits used by this class.
	 *
	 * @return array
	 */
	public function getTraits()
	{
		$traitNames = $this->getTraitNames();
		if (empty($traitNames)) {
			return array();
		}

		$broker = $this->getBroker();
		return array_combine($traitNames, array_map(function($traitName) use ($broker) {
			return $broker->getClass($traitName);
		}, $traitNames));
	}

	/**
	 * Returns traits used by this class and not its parents.
	 *
	 * @return array
	 */
	public function getOwnTraits()
	{
		$ownTraitNames = $this->getOwnTraitNames();
		if (empty($ownTraitNames)) {
			return array();
		}

		$broker = $this->getBroker();
		return array_combine($ownTraitNames, array_map(function($traitName) use ($broker) {
			return $broker->getClass($traitName);
		}, $ownTraitNames));
	}

	/**
	 * Returns names of used traits.
	 *
	 * @return array
	 */
	public function getTraitNames()
	{
		$parentClass = $this->getParentClass();

		$names = $parentClass ? $parentClass->getTraitNames() : array();
		foreach (array_reverse($this->traits) as $traitName) {
			$names = array_merge($names, $this->getBroker()->getClass($traitName)->getTraitNames());
			$names[] = $traitName;
		}

		return array_unique($names);
	}

	/**
	 * Returns names of traits used by this class an not its parents.
	 *
	 * @return array
	 */
	public function getOwnTraitNames()
	{
		return array_reverse($this->traits);
	}

	/**
	 * Returns method aliases from traits.
	 *
	 * @return array
	 */
	public function getTraitAliases()
	{
		return $this->traitAliases;
	}

	/**
	 * Returns if the class is a trait.
	 *
	 * @return boolean
	 */
	public function isTrait()
	{
		return self::IS_TRAIT === $this->type;
	}

	/**
	 * Returns if the class uses a particular trait.
	 *
	 * @param \ReflectionClass|\TokenReflection\IReflectionClass|string $trait Trait reflection or name
	 * @return bool
	 */
	public function usesTrait($trait)
	{
		if (is_object($trait)) {
			if (!$trait instanceof InternalReflectionClass && !$trait instanceof IReflectionClass) {
				throw new Exception\Runtime(sprintf('Parameter must be a string or an instance of trait reflection, "%s" provided.', get_class($trait)), Exception\Runtime::INVALID_ARGUMENT);
			}

			$traitName = $trait->getName();

			if (!$trait->isTrait()) {
				throw new Exception\Runtime(sprintf('"%s" is not a trait.', $traitName), Exception\Runtime::INVALID_ARGUMENT);
			}
		} else {
			$reflection = $this->getBroker()->getClass($trait);
			if (!$reflection->isTrait()) {
				throw new Exception\Runtime(sprintf('"%s" is not a trait.', $trait), Exception\Runtime::INVALID_ARGUMENT);
			}

			$traitName = $trait;
		}

		return in_array($traitName, $this->getTraitNames());
	}

	/**
	 * Returns reflections of direct subclasses.
	 *
	 * @return array
	 */
	public function getDirectSubclasses()
	{
		$that = $this->name;
		return array_filter($this->getBroker()->getClasses(), function(ReflectionClass $class) use ($that) {
			if (!$class->isSubclassOf($that)) {
				return false;
			}

			return null === $class->getParentClassName() || !$class->getParentClass()->isSubClassOf($that);
		});
	}

	/**
	 * Returns names of direct subclasses.
	 *
	 * @return array
	 */
	public function getDirectSubclassNames()
	{
		return array_keys($this->getDirectSubclasses());
	}

	/**
	 * Returns reflections of indirect subclasses.
	 *
	 * @return array
	 */
	public function getIndirectSubclasses()
	{
		$that = $this->name;
		return array_filter($this->getBroker()->getClasses(), function(ReflectionClass $class) use ($that) {
			if (!$class->isSubclassOf($that)) {
				return false;
			}

			return null !== $class->getParentClassName() && $class->getParentClass()->isSubClassOf($that);
		});
	}

	/**
	 * Returns names of indirect subclasses.
	 *
	 * @return array
	 */
	public function getIndirectSubclassNames()
	{
		return array_keys($this->getIndirectSubclasses());
	}

	/**
	 * Returns reflections of classes directly implementing this interface.
	 *
	 * @return array
	 */
	public function getDirectImplementers()
	{
		if (!$this->isInterface()) {
			return array();
		}

		$that = $this->name;
		return array_filter($this->getBroker()->getClasses(), function(ReflectionClass $class) use ($that) {
			if ($class->isInterface() || !$class->implementsInterface($that)) {
				return false;
			}

			return null === $class->getParentClassName() || !$class->getParentClass()->implementsInterface($that);
		});
	}

	/**
	 * Returns names of classes directly implementing this interface.
	 *
	 * @return array
	 */
	public function getDirectImplementerNames()
	{
		return array_keys($this->getDirectImplementers());
	}

	/**
	 * Returns reflections of classes indirectly implementing this interface.
	 *
	 * @return array
	 */
	public function getIndirectImplementers()
	{
		if (!$this->isInterface()) {
			return array();
		}

		$that = $this->name;
		return array_filter($this->getBroker()->getClasses(), function(ReflectionClass $class) use ($that) {
			if ($class->isInterface() || !$class->implementsInterface($that)) {
				return false;
			}

			return null !== $class->getParentClassName() && $class->getParentClass()->implementsInterface($that);
		});
	}

	/**
	 * Returns names of classes indirectly implementing this interface.
	 *
	 * @return array
	 */
	public function getIndirectImplementerNames()
	{
		return array_keys($this->getIndirectImplementers());
	}

	/**
	 * Returns if the given object is an instance of this class.
	 *
	 * @param object $object Instance
	 * @return boolean
	 * @throws \TokenReflection\Exception\Runtime If the provided argument is not an object
	 */
	public function isInstance($object)
	{
		if (!is_object($object)) {
			throw new Exception\Runtime(sprintf('Parameter must be a class instance, "%s" provided.', gettype($object)), Exception\Runtime::INVALID_ARGUMENT);
		}

		return $this->name === get_class($object) || is_subclass_of($object, $this->name);
	}

	/**
	 * Creates a new instance using variable number of parameters.
	 *
	 * Use any number of constructor parameters as function parameters.
	 *
	 * @param mixed $args
	 * @return object
	 */
	public function newInstance($args)
	{
		return $this->newInstanceArgs(func_get_args());
	}

	/**
	 * Creates a new instance using an array of parameters.
	 *
	 * @param array $args Array of constructor parameters
	 * @return object
	 * @throws \TokenReflection\Exception\Runtime If the required class does not exist
	 */
	public function newInstanceArgs(array $args = array())
	{
		if (!class_exists($this->name, true)) {
			throw new Exception\Runtime(sprintf('Could not create an instance of class "%s"; class does not exist.', $this->name), Exception\Runtime::DOES_NOT_EXIST);
		}

		$reflection = new InternalReflectionClass($this->name);
		return $reflection->newInstanceArgs($args);
	}

	/**
	 * Sets a static property value.
	 *
	 * @param string $name Property name
	 * @param mixed $value Property value
	 * @throws \TokenReflection\Exception\Runtime If the requested static property does not exist
	 * @throws \TokenReflection\Exception\Runtime If the requested static property is not accessible
	 */
	public function setStaticPropertyValue($name, $value)
	{
		if ($this->hasProperty($name) && ($property = $this->getProperty($name)) && $property->isStatic()) {
			if (!$property->isPublic() && !$property->isAccessible()) {
				throw new Exception\Runtime(sprintf('Static property "%s" in class "%s" is not accessible.', $name, $this->name), Exception\Runtime::NOT_ACCESSBILE);
			}

			$property->setDefaultValue($value);
			return;
		}

		throw new Exception\Runtime(sprintf('There is no static property "%s" in class "%s".', $name, $this->name), Exception\Runtime::DOES_NOT_EXIST);
	}

	/**
	 * Returns the string representation of the reflection object.
	 *
	 * @return string
	 */
	public function __toString()
	{
		$implements = '';
		$interfaceNames = $this->getInterfaceNames();
		if (count($interfaceNames) > 0) {
			$implements = sprintf(
				' %s %s',
				$this->isInterface() ? 'extends' : 'implements',
				implode(', ', $interfaceNames)
			);
		}

		$buffer = '';
		$count = 0;
		foreach ($this->getConstantReflections() as $constant) {
			$buffer .= '    ' . $constant->__toString();
			$count++;
		}
		$constants = sprintf("\n\n  - Constants [%d] {\n%s  }", $count, $buffer);

		$sBuffer = '';
		$sCount = 0;
		$buffer = '';
		$count = 0;
		foreach ($this->getProperties() as $property) {
			$string = '    ' . preg_replace('~\n(?!$)~', "\n    ", $property->__toString());
			if ($property->isStatic()) {
				$sBuffer .= $string;
				$sCount++;
			} else {
				$buffer .= $string;
				$count++;
			}
		}
		$staticProperties = sprintf("\n\n  - Static properties [%d] {\n%s  }", $sCount, $sBuffer);
		$properties = sprintf("\n\n  - Properties [%d] {\n%s  }", $count, $buffer);

		$sBuffer = '';
		$sCount = 0;
		$buffer = '';
		$count = 0;
		foreach ($this->getMethods() as $method) {
			// Skip private methods of parent classes
			if ($method->getDeclaringClassName() !== $this->getName() && $method->isPrivate()) {
				continue;
			}
			// Indent
			$string = "\n    ";
			if (null !== $method->getDeclaringTraitName()) {
				$string .= "\n    ";
			}

			$string .= preg_replace('~\n(?!$|\n|\s*\*)~', "\n    ", $method->__toString());
			// Add inherits
			if ($method->getDeclaringClassName() !== $this->getName()) {
				$string = preg_replace(
					array('~Method [ <[\w:]+~', '~, overwrites[^,]+~'),
					array('\0, inherits ' . $method->getDeclaringClassName(), ''),
					$string
				);
			}
			if ($method->isStatic()) {
				$sBuffer .= $string;
				$sCount++;
			} else {
				$buffer .= $string;
				$count++;
			}
		}
		$staticMethods = sprintf("\n\n  - Static methods [%d] {\n%s  }", $sCount, ltrim($sBuffer, "\n"));
		$methods = sprintf("\n\n  - Methods [%d] {\n%s  }", $count, ltrim($buffer, "\n"));

		return sprintf(
			"%s%s [ <user>%s %s%s%s %s%s%s ] {\n  @@ %s %d-%d%s%s%s%s%s\n}\n",
			$this->getDocComment() ? $this->getDocComment() . "\n" : '',
			$this->isInterface() ? 'Interface' : 'Class',
			$this->isIterateable() ? ' <iterateable>' : '',
			$this->isAbstract() && !$this->isInterface() ? 'abstract ' : '',
			$this->isFinal() ? 'final ' : '',
			$this->isInterface() ? 'interface' : 'class',
			$this->getName(),
			null !== $this->getParentClassName() ? ' extends ' . $this->getParentClassName() : '',
			$implements,
			$this->getFileName(),
			$this->getStartLine(),
			$this->getEndLine(),
			$constants,
			$staticProperties,
			$staticMethods,
			$properties,
			$methods
		);
	}

	/**
	 * Exports a reflected object.
	 *
	 * @param \TokenReflection\Broker $broker Broker instance
	 * @param string|object $className Class name or class instance
	 * @param boolean $return Return the export instead of outputting it
	 * @return string|null
	 * @throws \TokenReflection\Exception\Runtime If requested parameter doesn't exist
	 */
	public static function export(Broker $broker, $className, $return = false)
	{
		if (is_object($className)) {
			$className = get_class($className);
		}

		$class = $broker->getClass($className);
		if ($class instanceof Dummy\ReflectionClass) {
			throw new Exception\Runtime(sprintf('Class %s does not exist.', $className), Exception\Runtime::DOES_NOT_EXIST);
		}

		if ($return) {
			return $class->__toString();
		}

		echo $class->__toString();
	}

	/**
	 * Returns if the class definition is complete.
	 *
	 * @return boolean
	 */
	public function isComplete()
	{
		if (!$this->definitionComplete) {
			if (null !== $this->parentClassName && !$this->getParentClass()->isComplete()) {
				return false;
			}

			foreach ($this->getOwnInterfaces() as $interface) {
				if (!$interface->isComplete()) {
					return false;
				}
			}

			$this->definitionComplete = true;
		}

		return $this->definitionComplete;
	}

	/**
	 * Returns imported namespaces and aliases from the declaring namespace.
	 *
	 * @return array
	 */
	public function getNamespaceAliases()
	{
		return $this->aliases;
	}

	/**
	 * Processes the parent reflection object.
	 *
	 * @param \TokenReflection\IReflection $parent Parent reflection object
	 * @return \TokenReflection\ReflectionClass
	 * @throws \TokenReflection\Exception\Parse If an invalid parent reflection object was provided
	 */
	protected function processParent(IReflection $parent)
	{
		if (!$parent instanceof ReflectionFileNamespace) {
			throw new Exception\Parse(sprintf('The parent object has to be an instance of TokenReflection\ReflectionFileNamespace, "%s" given.', get_class($parent)), Exception\Parse::INVALID_PARENT);
		}

		$this->namespaceName = $parent->getName();
		$this->aliases = $parent->getNamespaceAliases();
		return parent::processParent($parent);
	}

	/**
	 * Parses reflected element metadata from the token stream.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @param \TokenReflection\IReflection $parent Parent reflection object
	 * @return \TokenReflection\ReflectionClass
	 */
	protected function parse(Stream $tokenStream, IReflection $parent)
	{
		return $this
			->parseModifiers($tokenStream)
			->parseName($tokenStream)
			->parseParent($tokenStream, $parent)
			->parseInterfaces($tokenStream, $parent);
	}

	/**
	 * Parses class modifiers (abstract, final) and class type (class, interface).
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @return \TokenReflection\ReflectionClass
	 * @throws \TokenReflection\Exception\Parse If class modifiers could not be parsed
	 */
	private function parseModifiers(Stream $tokenStream)
	{
		try {
			while (true) {
				switch ($tokenStream->getType()) {
					case null:
						break 2;
					case T_ABSTRACT:
						$this->modifiers = InternalReflectionClass::IS_EXPLICIT_ABSTRACT;
						break;
					case T_FINAL:
						$this->modifiers = InternalReflectionClass::IS_FINAL;
						break;
					case T_INTERFACE:
						$this->modifiers = self::IS_INTERFACE;
						$this->type = self::IS_INTERFACE;
						$tokenStream->skipWhitespaces();
						break 2;
					case T_TRAIT:
						$this->modifiers = self::IS_TRAIT;
						$this->type = self::IS_TRAIT;
						$tokenStream->skipWhitespaces();
						break 2;
					case T_CLASS:
						$tokenStream->skipWhitespaces();
						break 2;
					default:
						break;
				}

				$tokenStream->skipWhitespaces();
			}

			return $this;
		} catch (Exception $e) {
			throw new Exception\Parse('Could not parse class modifiers.', Exception\Parse::PARSE_ELEMENT_ERROR, $e);
		}
	}

	/**
	 * Parses the class/interface name.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @return \TokenReflection\ReflectionClass
	 * @throws \TokenReflection\Exception\Parse If the class name could not be determined
	 */
	protected function parseName(Stream $tokenStream)
	{
		try {
			if (!$tokenStream->is(T_STRING)) {
				throw new Exception\Parse(sprintf('Invalid token found: "%s".', $tokenStream->getTokenName()), Exception\Parse::PARSE_ELEMENT_ERROR);
			}

			if ($this->namespaceName === ReflectionNamespace::NO_NAMESPACE_NAME) {
				$this->name = $tokenStream->getTokenValue();
			} else {
				$this->name = $this->namespaceName . '\\' . $tokenStream->getTokenValue();
			}

			$tokenStream->skipWhitespaces();

			return $this;
		} catch (Exception $e) {
			throw new Exception\Parse('Could not parse class name.', Exception\Parse::PARSE_ELEMENT_ERROR, $e);
		}
	}

	/**
	 * Parses the parent class.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @param \TokenReflection\IReflection $parent Parent reflection object
	 * @return \TokenReflection\ReflectionClass
	 * @throws \TokenReflection\Exception\Parse If parent class name could not be parsed
	 */
	private function parseParent(Stream $tokenStream, ReflectionBase $parent = null)
	{
		if (!$tokenStream->is(T_EXTENDS)) {
			return $this;
		}

		try {
			while (true) {
				$tokenStream->skipWhitespaces();

				$parentClassName = '';
				while (true) {
					switch ($tokenStream->getType()) {
						case T_STRING:
						case T_NS_SEPARATOR:
							$parentClassName .= $tokenStream->getTokenValue();
							break;
						default:
							break 2;
					}

					$tokenStream->skipWhitespaces();
				}

				$parentClassName = Resolver::resolveClassFQN($parentClassName, $this->aliases, $this->namespaceName);

				if ($this->isInterface()) {
					$this->interfaces[] = $parentClassName;

					if (',' === $tokenStream->getTokenValue()) {
						continue;
					}
				} else {
					$this->parentClassName = $parentClassName;
				}

				break;
			}

			return $this;
		} catch (Exception $e) {
			throw new Exception\Parse('Could not parse parent class name.', Exception\Parse::PARSE_ELEMENT_ERROR, $e);
		}
	}

	/**
	 * Parses implemented interfaces.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @param \TokenReflection\IReflection $parent Parent reflection object
	 * @return \TokenReflection\ReflectionClass
	 * @throws \TokenReflection\Exception\Parse On error while parsing interfaces
	 */
	private function parseInterfaces(Stream $tokenStream, ReflectionBase $parent = null)
	{
		if (!$tokenStream->is(T_IMPLEMENTS)) {
			return $this;
		}

		if ($this->isInterface()) {
			throw new Exception\Parse(sprintf('Interfaces ("%s") cannot implement interfaces.', $this->name), Exception\Parse::PARSE_ELEMENT_ERROR);
		}

		try {
			while (true) {
				$interfaceName = '';

				$tokenStream->skipWhitespaces();
				while (true) {
					switch ($tokenStream->getType()) {
						case T_STRING:
						case T_NS_SEPARATOR:
							$interfaceName .= $tokenStream->getTokenValue();
							break;
						default:
							break 2;
					}

					$tokenStream->skipWhitespaces();
				}

				$this->interfaces[] = Resolver::resolveClassFQN($interfaceName, $this->aliases, $this->namespaceName);

				$type = $tokenStream->getType();
				if ('{' === $type) {
					break;
				} elseif (',' !== $type) {
					throw new Exception\Parse(sprintf('Invalid token found: "%s", expected "{" or ";".', $tokenStream->getTokenName()), Exception\Parse::PARSE_ELEMENT_ERROR);
				}
			}

			return $this;
		} catch (Exception $e) {
			throw new Exception\Parse('Could not parse implemented interfaces.', Exception\Parse::PARSE_ELEMENT_ERROR, $e);
		}
	}

	/**
	 * Parses child reflection objects from the token stream.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @param \TokenReflection\IReflection $parent Parent reflection object
	 * @return \TokenReflection\ReflectionClass
	 */
	protected function parseChildren(Stream $tokenStream, IReflection $parent)
	{
		while (true) {
			switch ($type = $tokenStream->getType()) {
				case null:
					break 2;
				case T_COMMENT:
				case T_DOC_COMMENT:
					$docblock = $tokenStream->getTokenValue();
					if (preg_match('~^' . preg_quote(self::DOCBLOCK_TEMPLATE_START, '~') . '~', $docblock)) {
						array_unshift($this->docblockTemplates, new ReflectionAnnotation($this, $docblock));
					} elseif (self::DOCBLOCK_TEMPLATE_END === $docblock) {
						array_shift($this->docblockTemplates);
					}
					$tokenStream->next();
					break;
				case '}':
					break 2;
				case T_PUBLIC:
				case T_PRIVATE:
				case T_PROTECTED:
				case T_STATIC:
				case T_VAR:
				case T_VARIABLE:
					static $searching = array(T_VARIABLE => true, T_FUNCTION => true);

					if (T_VAR !== $tokenStream->getType()) {
						$position = $tokenStream->key();
						while (null !== ($type = $tokenStream->getType($position)) && !isset($searching[$type])) {
							$position++;
						}
					}

					if (T_VARIABLE === $type || T_VAR === $type) {
						$property = new ReflectionProperty($tokenStream, $this->getBroker(), $this);
						$this->properties[$property->getName()] = $property;
						$tokenStream->next();
						break;
					}
					// Break missing on purpose
				case T_FINAL:
				case T_ABSTRACT:
				case T_FUNCTION:
					$method = new ReflectionMethod($tokenStream, $this->getBroker(), $this);
					$this->methods[$method->getName()] = $method;
					$tokenStream->next();
					break;
				case T_CONST:
					$tokenStream->skipWhitespaces();
					while ($tokenStream->is(T_STRING)) {
						$constant = new ReflectionConstant($tokenStream, $this->getBroker(), $this);
						$this->constants[$constant->getName()] = $constant;
						if ($tokenStream->is(',')) {
							$tokenStream->skipWhitespaces();
						} else {
							$tokenStream->next();
						}
					}
					break;
				case T_USE:
					$tokenStream->skipWhitespaces();

					while (true) {
						$traitName = '';
						$type = $tokenStream->getType();
						while (T_STRING === $type || T_NS_SEPARATOR === $type) {
							$traitName .= $tokenStream->getTokenValue();
							$type = $tokenStream->skipWhitespaces()->getType();
						}

						if ('' === trim($traitName, '\\')) {
							throw new Exception\Parse('Empty trait name found.', Exception\Parse::PARSE_CHILDREN_ERROR);
						}

						$this->traits[] = Resolver::resolveClassFQN($traitName, $this->aliases, $this->namespaceName);

						if (';' === $type) { // End of "use"
							$tokenStream->skipWhitespaces();
							break;
						} elseif (',' === $type) { // Next trait name follows
							$tokenStream->skipWhitespaces();
							continue;
						} elseif ('{' !== $type) { // Unexpected token
							throw new Exception\Parse(sprintf('Unexpected token found: "%s".', $tokenStream->getTokenName()), Exception\Parse::PARSE_CHILDREN_ERROR);
						}

						// Aliases definition
						$type = $tokenStream->skipWhitespaces()->getType();
						while (true) {
							if ('}' === $type) {
								$tokenStream->skipWhitespaces();
								break 2;
							}

							$leftSide = '';
							$rightSide = array('', null);
							$alias = true;

							while (T_STRING === $type || T_NS_SEPARATOR === $type || T_DOUBLE_COLON === $type) {
								$leftSide .= $tokenStream->getTokenValue();
								$type = $tokenStream->skipWhitespaces()->getType();
							}

							if (T_INSTEADOF === $type) {
								$alias = false;
							} elseif (T_AS !== $type) {
								throw new Exception\Parse(sprintf('Unexpected token found: "%s".', $tokenStream->getTokenName()), Exception\Parse::PARSE_CHILDREN_ERROR);
							}

							$type = $tokenStream->skipWhitespaces()->getType();

							if (T_PUBLIC === $type || T_PROTECTED === $type || T_PRIVATE === $type) {
								if (!$alias) {
									throw new Exception\Parse(sprintf('Unexpected token found: "%s".', $tokenStream->getTokenName()), Exception\Parse::PARSE_CHILDREN_ERROR);
								}

								switch ($type) {
									case T_PUBLIC:
										$type = InternalReflectionMethod::IS_PUBLIC;
										break;
									case T_PROTECTED:
										$type = InternalReflectionMethod::IS_PROTECTED;
										break;
									case T_PRIVATE:
										$type = InternalReflectionMethod::IS_PRIVATE;
										break;
								}

								$rightSide[1] = $type;
								$type = $tokenStream->skipWhitespaces()->getType();
							}


							while (T_STRING === $type || (T_NS_SEPARATOR === $type && !$alias)) {
								$rightSide[0] .= $tokenStream->getTokenValue();
								$type = $tokenStream->skipWhitespaces()->getType();
							}

							if (empty($leftSide)) {
								throw new Exception\Parse('An empty method name was found.', Exception\Parse::PARSE_CHILDREN_ERROR);
							}

							if ($alias) {
								// Alias
								if ($pos = strpos($leftSide, '::')) {
									$methodName = substr($leftSide, $pos + 2);
									$className = Resolver::resolveClassFQN(substr($leftSide, 0, $pos), $this->aliases, $this->namespaceName);
									$leftSide = $className . '::' . $methodName;
								}

								$this->traitImports[$leftSide][] = $rightSide;
							} else {
								// Insteadof
								if ($pos = strpos($leftSide, '::')) {
									$methodName = substr($leftSide, $pos + 2);
								} else {
									throw new Exception\Parse('A T_DOUBLE_COLON has to be present when using T_INSTEADOF.', Exception\Parse::PARSE_CHILDREN_ERROR);
								}

								$this->traitImports[Resolver::resolveClassFQN($rightSide[1], $this->aliases, $this->namespaceName) . '::' . $methodName][] = null;
							}

							if (',' === $type) {
								$tokenStream->skipWhitespaces();
								continue;
							} elseif (';' !== $type) {
								throw new Exception\Parse(sprintf('Unexpected token found: "%s".', $tokenStream->getTokenName()), Exception\Parse::PARSE_CHILDREN_ERROR);
							}

							$type = $tokenStream->skipWhitespaces()->getType();
						}

						throw new Exception\Parse(sprintf('Unexpected token found: "%s".', $tokenStream->getTokenName()), Exception\Parse::PARSE_CHILDREN_ERROR);
					}

					break;
				default:
					$tokenStream->next();
					break;
			}
		}

		return $this;
	}
}
