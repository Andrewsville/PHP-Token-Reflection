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
use ReflectionClass as InternalReflectionClass, ReflectionProperty as InternalReflectionProperty;

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
	 * Method implements interfaces.
	 *
	 * @see http://svn.php.net/viewvc/php/php-src/branches/PHP_5_3/Zend/zend_compile.h?revision=306939&view=markup#l152
	 * ZEND_ACC_IMPLEMENT_INTERFACES
	 *
	 * @var integer
	 */
	const IMPLEMENTS_INTERFACES = 0x80000;

	/**
	 * Class namespace name.
	 *
	 * @var string
	 */
	private $namespaceName;

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
	 * Methods reflections.
	 *
	 * @var array
	 */
	private $methods = array();

	/**
	 * Imported namespace/class aliases.
	 *
	 * @var array
	 */
	private $aliases = array();

	/**
	 * Class modifiers.
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
	 * Stores if the class definition is complete.
	 *
	 * @var boolean
	 */
	private $definitionComplete = false;

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
					static $searching = array(T_VARIABLE, T_FUNCTION);

					if (T_VAR !== $tokenStream->getType()) {
						$position = $tokenStream->key();
						while (null !== ($type = $tokenStream->getType($position++)) && !in_array($type, $searching)) {
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
				default:
					$tokenStream->next();
					break;
			}
		}

		return $this;
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
	 * Returns default properties.
	 *
	 * @return array
	 */
	public function getDefaultProperties()
	{
		static $accessLevels = array(InternalReflectionProperty::IS_PUBLIC, InternalReflectionProperty::IS_PRIVATE, InternalReflectionProperty::IS_PROTECTED);

		$defaults = array();
		$properties = $this->getProperties();
		foreach (array(true, false) as $static) {
			foreach ($accessLevels as $level) {
				foreach ($properties as $property) {
					if ($property->isStatic() === $static && ($property->getModifiers() & $level)) {
						$defaults[$property->getName()] = $property->getDefaultValue();
					}
				}
			}
		}

		return $defaults;
	}

	/**
	 * Returns interface names.
	 *
	 * @return array
	 */
	public function getInterfaceNames()
	{
		if ($this->isInterface()) {
			return $this->getParentClassNameList();
		}

		$parentClass = $this->getParentClass();

		$names = $parentClass ? $parentClass->getInterfaceNames() : array();
		foreach (array_reverse($this->interfaces) as $interfaceName) {
			$names = array_merge($names, $this->getBroker()->getClass($interfaceName)->getInterfaceNames());
			$names[] = $interfaceName;
		}

		return array_unique($names);
	}

	/**
	 * Returns names of interfaces implemented by this class, not its parents.
	 *
	 * @return array
	 */
	public function getOwnInterfaceNames()
	{
		return array_reverse($this->interfaces);
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

		if (null !== $this->parentClassName) {
			foreach ($this->getParentClass()->getMethods(null) as $parentMethod) {
				if (!isset($methods[$parentMethod->getName()])) {
					$methods[$parentMethod->getName()] = $parentMethod;
				}
			}
		}

		if (null !== $filter) {
			$methods = array_filter($methods, function(ReflectionMethod $method) use ($filter) {
				return $method->is($filter);
			});
		}

		return array_values($methods);
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
			}

			if (count($this->getInterfaceNames())) {
				$this->modifiers |= self::IMPLEMENTS_INTERFACES;
			}

			$this->modifiersComplete = true;
			foreach ($this->getParentClasses() as $parentClass) {
				if ($parentClass instanceof Dummy\ReflectionClass) {
					$this->modifiersComplete = false;
					break;
				}
			}
		}

		return $this->modifiers;
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
	 * Returns the parent class name.
	 *
	 * @return string|null
	 */
	public function getParentClassName()
	{
		return $this->parentClassName;
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

		if (null !== $this->parentClassName) {
			foreach ($this->getParentClass()->getProperties(null) as $parentProperty) {
				if (!isset($properties[$parentProperty->getName()])) {
					$properties[$parentProperty->getName()] = $parentProperty;
				}
			}
		}

		if (null !== $filter) {
			$properties = array_filter($properties, function(ReflectionProperty $property) use ($filter) {
				return (bool) ($property->getModifiers() & $filter);
			});
		}

		return array_values($properties);
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
	 * Returns if the class implements the given interface.
	 *
	 * @param string|object $interface Interface name or reflection object
	 * @return boolean
	 * @throws \TokenReflection\Exception\Runtime If the provided parameter is not an interface
	 */
	public function implementsInterface($interface) {
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
	 * Returns if the class is defined within a namespace.
	 *
	 * @return boolean
	 */
	public function inNamespace()
	{
		return null !== $this->namespaceName && ReflectionNamespace::NO_NAMESPACE_NAME !== $this->namespaceName;
	}

	/**
	 * Returns if the class is abstract.
	 *
	 * @return boolean
	 */
	public function isAbstract()
	{
		return (bool) ($this->modifiers & InternalReflectionClass::IS_EXPLICIT_ABSTRACT);
	}

	/**
	 * Returns if the class is final.
	 *
	 * @return boolean
	 */
	public function isFinal()
	{
		return $this->modifiers === InternalReflectionClass::IS_FINAL;
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
	 * Not implemented in 5.3, but in trunk though.
	 *
	 * @return boolean
	 * @see http://svn.php.net/viewvc/php/php-src/trunk/ext/reflection/php_reflection.c?revision=307971&view=markup#l4059
	 */
	public function isCloneable()
	{
		if (!$this->isInstantiable()) {
			return false;
		}

		if ($this->hasMethod('__clone')) {
			return $this->getMethod('__clone')->isPublic();
		}

		return true;
	}

	/**
	 * Returns if the class is an interface.
	 *
	 * @return boolean
	 */
	public function isInterface()
	{
		return self::IS_INTERFACE === $this->modifiers;
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
	 * Returns if the class is iterateable.
	 *
	 * Returns true if the class implements the Traversable interface.
	 *
	 * @return boolean
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
						// break missing on purpose
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

				$tokenStream->next();
			}
			$tokenStream->skipWhitespaces();

			$this->parentClassName = self::resolveClassFQN($parentClassName, $this->aliases, $this->namespaceName);

			return $this;
		} catch (Exception $e) {
			throw new Exception\Parse('Could not parse parent class name.', Exception\Parse::PARSE_ELEMENT_ERROR, $e);
		}
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

				$this->interfaces[] = self::resolveClassFQN($interfaceName, $this->aliases, $this->namespaceName);

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
}
