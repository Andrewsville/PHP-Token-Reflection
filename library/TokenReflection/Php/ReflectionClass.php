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

namespace TokenReflection\Php;
use TokenReflection;

use TokenReflection\Broker;
use Reflector, ReflectionClass as InternalReflectionClass, ReflectionProperty as InternalReflectionProperty, ReflectionMethod as InternalReflectionMethod;
use RuntimeException, TokenReflection\Exception;

/**
 * Reflection of a not tokenized but defined class.
 *
 * Descendant of the internal reflection with additional features.
 */
class ReflectionClass extends InternalReflectionClass implements IReflection, TokenReflection\IReflectionClass
{
	/**
	 * Internal classes pseudo-package name.
	 *
	 * @var string
	 */
	const PACKAGE_INTERNAL = 'PHP';

	/**
	 * Reflection broker.
	 *
	 * @var \TokenReflection\Broker
	 */
	private $broker;

	/**
	 * Constant reflections.
	 *
	 * @var array
	 */
	private $contants;

	/**
	 * Metod reflections.
	 *
	 * @var array
	 */
	private $methods;

	/**
	 * Implemented interface reflections.
	 *
	 * @var array
	 */
	private $interfaces;

	/**
	 * Property reflections.
	 *
	 * @var array
	 */
	private $properties;

	/**
	 * Constructor.
	 *
	 * @param string $className Class name
	 * @param \TokenReflection\Broker $broker Reflection broker
	 */
	public function __construct($className, Broker $broker)
	{
		parent::__construct($className);
		$this->broker = $broker;
	}

	/**
	 * Returns the reflection broker used by this reflection object.
	 *
	 * @return \TokenReflection\Broker
	 */
	public function getBroker()
	{
		return $this->broker;
	}

	/**
	 * Returns parent class reflection.
	 *
	 * @return \TokenReflection\Php\ReflectionClass
	 */
	public function getParentClass()
	{
		$parent = parent::getParentClass();
		return $parent ? self::create($parent, $this->broker) : null;
	}

	/**
	 * Returns the parent class name.
	 *
	 * @return string
	 */
	public function getParentClassName()
	{
		$parent = $this->getParentClass();
		return $parent ? $parent->getName() : null;
	}

	/**
	 * Returns the parent classes reflections.
	 *
	 * @return array
	 */
	public function getParentClasses()
	{
		$broker = $this->broker;
		return array_map(function($className) use($broker) {
			return $broker->getClass($className);
		}, $this->getParentClassNameList());
	}

	/**
	 * Returns a constant reflection.
	 *
	 * @param string $name Constant name
	 * @return \TokenReflection\ReflectionConstant|null
	 * @throws \TokenReflection\Exception If the requested constant does not exist
	 */
	public function getConstantReflection($name)
	{
		if ($this->hasConstant($name)) {
			return new ReflectionConstant($name, $this->getConstant($name), $this->broker, $this);
		}

		throw new Exception(sprintf('Constant "%s" is not defined in class "%s"', $name, $this->getName()), Exception::DOES_NOT_EXIST);
	}

	/**
	 * Returns an array of constant reflections.
	 *
	 * @return array
	 */
	public function getConstantReflections()
	{
		if (null === $this->contants) {
			$this->contants = array();
			foreach ($this->getConstants() as $name => $value) {
				$this->contants[$name] = $this->getConstantReflection($name);
			}
		}

		return $this->contants;
	}

	/**
	 * Returns an array of constant reflections defined by this class and not its parents.
	 *
	 * @return array
	 */
	public function getOwnConstantReflections()
	{
		if (null === $this->contants) {
			$this->contants = array();
			foreach ($this->getOwnConstants() as $name => $value) {
				$this->contants[$name] = $this->getConstantReflection($name);
			}
		}

		return $this->contants;
	}

	/**
	 * Returns the parent classes names.
	 *
	 * @return array
	 */
	public function getParentClassNameList()
	{
		return class_parents($this->getName());
	}

	/**
	 * Returns the package name.
	 *
	 * @return string
	 */
	public function getPackageName()
	{
		return $this->isInternal() ? self::PACKAGE_INTERNAL : TokenReflection\ReflectionClass::PACKAGE_NONE;
	}

	/**
	 * Magic __get method.
	 *
	 * @param string $key Variable name
	 * @return mixed
	 */
	final public function __get($key)
	{
		return TokenReflection\ReflectionBase::get($this, $key);
	}

	/**
	 * Magic __isset method.
	 *
	 * @param string $key Variable name
	 * @return boolean
	 */
	final public function __isset($key) {
		return TokenReflection\ReflectionBase::exists($this, $key);
	}

	/**
	 * Returns interfaces implemented by this class, not its parents.
	 *
	 * @return array
	 */
	public function getOwnInterfaces()
	{
		$parent = $this->getParentClass();
		return $parent ? array_diff_key($this->getInterfaces(), $parent->getInterfaces()) : $this->getInterfaces();
	}

	/**
	 * Returns names of interfaces implemented by this class, not its parents.
	 *
	 * @return array
	 */
	public function getOwnInterfaceNames()
	{
		return array_keys($this->getOwnInterfaces());
	}

	/**
	 * Returns methods declared by this class, not its parents.
	 *
	 * @return array
	 */
	public function getOwnMethods($filter = null)
	{
		$me = $this->getName();
		return array_filter($this->getMethods($filter), function(ReflectionMethod $method) use ($me) {
			return $method->declaringClass->name === $me;
		});
	}

	/**
	 * Returns if the class implements (and not its parents) the given method.
	 *
	 * @param string $name Method name
	 * @return boolean
	 */
	public function hasOwnMethod($name)
	{
		$methods = $this->getOwnMethods();
		return isset($methods[$name]);
	}

	/**
	 * Returns properties declared by this class, not its parents.
	 *
	 * @return array
	 */
	public function getOwnProperties($filter = null)
	{
		$me = $this->getName();
		return array_filter($this->getProperties($filter), function(ReflectionProperty $property) use ($me) {
			return $property->declaringClass->name === $me;
		});
	}

	/**
	 * Returns if the class has (and not its parents) the given property.
	 *
	 * @param string $name Property name
	 * @return boolean
	 */
	public function hasOwnProperty($name)
	{
		$properties = $this->getOwnProperties();
		return isset($properties[$name]);
	}

	/**
	 * Returns constants declared by this class, not its parents
	 *
	 * @return array
	 */
	public function getOwnConstants()
	{
		return array_diff_assoc($this->getConstants(), $this->getParentClass() ? $this->getParentClass()->getConstants() : array());
	}

	/**
	 * Returns if the class (and not its parents) defines the given constant.
	 *
	 * @param string $name Constant name.
	 * @return boolean
	 */
	public function hasOwnConstant($name)
	{
		$constants = $this->getOwnConstants();
		return isset($constants[$name]);
	}

	/**
	 * Returns class properties.
	 *
	 * @param integer $filter Property filter
	 * @return array
	 */
	public function getProperties($filter = null)
	{
		if (null === $this->properties) {
			$broker = $this->broker;
			$this->properties = array_map(function(InternalReflectionProperty $property) use($broker) {
				return ReflectionProperty::create($property, $broker);
			}, parent::getProperties());
		}

		if (null === $filter) {
			return $this->properties;
		} else {
			return array_filter($this->properties, function(ReflectionProperty $property) use($filter) {
				return (bool) ($property->getModifiers() & $filter);
			});
		}
	}

	/**
	 * Returns class methods.
	 *
	 * @param integer $filter Property filter
	 * @return array
	 */
	public function getMethods($filter = null)
	{
		if (null === $this->methods) {
			$broker = $this->broker;
			$this->methods = array_map(function(InternalReflectionMethod $method) use($broker) {
				return ReflectionMethod::create($method, $broker);
			}, parent::getMethods());
		}

		if (null === $filter) {
			return $this->methods;
		} else {
			return array_filter($this->methods, function(ReflectionMethod $method) use($filter) {
				return (bool) ($method->getModifiers() & $filter);
			});
		}
	}

	/**
	 * Returns class constructor reflection.
	 *
	 * @return \TokenReflection\Php\ReflectionClass|null
	 */
	public function getConstructor()
	{
		return ReflectionMethod::create(parent::getConstructor(), $this->broker);
	}

	/**
	 * Returns class desctructor reflection.
	 *
	 * @return \TokenReflection\Php\ReflectionClass|null
	 */
	public function getDestructor()
	{
		foreach ($this->getMethods() as $method) {
			if ($method->isDestructor()) {
				return $method;
			}
		}

		// throw?
	}

	/**
	 * Returns the PHP extension reflection.
	 *
	 * @return \TokenReflection\Php\ReflectionExtension
	 */
	public function getExtension()
	{
		return ReflectionExtension::create(parent::getExtension(), $this->broker);
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

		$methods = $this->getMethods();
		return isset($methods['__clone']) ? $methods['__clone']->isPublic() : true;
	}

	/**
	 * Returns if the class is an exception or its descendant.
	 *
	 * @return boolean
	 */
	public function isException()
	{
		return 'Exception' === $this->getName() || $this->isSubclassOf('Exception');
	}

	/**
	 * Returns an array of interface reflections.
	 *
	 * @return array
	 */
	public function getInterfaces()
	{
		if (null === $this->interfaces) {
			$broker = $this->broker;
			$this->interfaces = array_map(function($interfaceName) use($broker) {
				return $broker->getClass($interfaceName);
			}, $this->getInterfaceNames());
		}

		return $this->interfaces;
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
			if ($interface instanceof InternalReflectionClass || $interface instanceof IReflectionClass) {
				$interfaceName = $interface->getName();
			} else {
				throw new InvalidArgumentException('Parameter must be a string or an instance of class reflection');
			}
		} else {
			$interfaceName = $interface;
		}

		$interfaces = $this->getInterfaces();
		return isset($interfaces[$interfaceName]);
	}

	/**
	 * Returns a particular method reflection.
	 *
	 * @param string $name Method name
	 * @return \TokenReflection\Php\ReflectionMethod
	 * @throws \TokenReflection\Exception\Runtime If the requested method does not exist
	 */
	public function getMethod($name)
	{
		foreach ($this->getMethods() as $method) {
			if ($method->getName() === $name) {
				return $method;
			}
		}

		throw new Exception(sprintf('There is no method %s in class %s', $name, $this->name), Exception::DOES_NOT_EXIST);
	}

	/**
	 * Returns a particular property reflection.
	 *
	 * @param string $name Property name
	 * @return \TokenReflection\Php\ReflectionProperty
	 * @throws \TokenReflection\Exception\Runtime If the requested property does not exist
	 */
	public function getProperty($name)
	{
		foreach ($this->getProperties() as $property) {
			if ($name === $property->getName()) {
				return $property;
			}
		}

		throw new Exception(sprintf('There is no property %s in class %s', $name, $this->getName()), Exception::DOES_NOT_EXIST);
	}

	/**
	 * Returns static properties reflections.
	 *
	 * @return array
	 */
	public function getStaticProperties()
	{
		return $this->getProperties(InternalReflectionProperty::IS_STATIC);
	}

	/**
	 * Returns the docblock definition of the class or its parent.
	 *
	 * @return string|false
	 */
	public function getInheritedDocComment()
	{
		if (false !== ($docComment = $this->getDocComment()) && false === strpos($docComment, '@inheritdoc')) {
			return $docComment;
		}

		if ($parent = $this->getParentClass()) {
			return $parent->getInheritedDocComment();
		}

		return false;
	}

	/**
	 * Returns parsed docblock.
	 *
	 * @return array
	 */
	public function getAnnotations()
	{
		return array();
	}

	/**
	 * Returns a particular annotation value.
	 *
	 * @param string $name Annotation name
	 * @param boolean $forceArray Always return values as array
	 * @return string|array|null
	 */
	public function getAnnotation($name)
	{
		return null;
	}

	/**
	 * Checks if there is a particular annotation.
	 *
	 * @param string $name Annotation name
	 * @return boolean
	 */
	public function hasAnnotation($name)
	{
		return false;
	}

	/**
	 * Returns if the current class is a subclass of the given class.
	 *
	 * @param string|object $class Class name or reflection object
	 * @return boolean
	 */
	public function isSubclassOf($class)
	{
		return in_array($class, $this->getParentClassNameList());
	}

	/**
	 * Returns if the current reflection comes from a tokenized source.
	 *
	 * @return boolean
	 */
	public function isTokenized()
	{
		return false;
	}

	/**
	 * Returns if the reflection subject is deprecated.
	 *
	 * @return boolean
	 */
	public function isDeprecated()
	{
		return false;
	}

	/**
	 * Returns reflections of direct subclasses.
	 *
	 * @return array
	 */
	public function getDirectSubclasses()
	{
		$that = $this->name;
		return array_filter($this->getBroker()->getClasses(Broker\Backend::INTERNAL_CLASSES | Broker\Backend::TOKENIZED_CLASSES), function(IReflectionClass $class) use($that) {
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
		return array_filter($this->getBroker()->getClasses(Broker\Backend::INTERNAL_CLASSES | Broker\Backend::TOKENIZED_CLASSES), function(IReflectionClass $class) use($that) {
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
		return array_filter($this->getBroker()->getClasses(Broker\Backend::INTERNAL_CLASSES | Broker\Backend::TOKENIZED_CLASSES), function(IReflectionClass $class) use($that) {
			if (!$class->implementsInterface($that)) {
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
		return array_filter($this->getBroker()->getClasses(Broker\Backend::INTERNAL_CLASSES | Broker\Backend::TOKENIZED_CLASSES), function(IReflectionClass $class) use($that) {
			if (!$class->implementsInterface($that)) {
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
	 * Creates a reflection instance.
	 *
	 * @param \ReflectionClass Internal reflection instance
	 * @param \TokenReflection\Broker Reflection broker instance
	 * @return \TokenReflection\Php\ReflectionClass
	 * @throws \TokenReflection\Exception\Runtime If an invalid internal reflection object was provided
	 */
	public static function create(Reflector $internalReflection, Broker $broker)
	{
		if (!$internalReflection instanceof InternalReflectionClass) {
			throw new RuntimeException(sprintf('Invalid reflection instance provided (%s), ReflectionClass expected.', get_class($internalReflection)));
		}

		return $broker->getClass($internalReflection->getName());
	}
}
