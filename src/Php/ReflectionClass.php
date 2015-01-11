<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Php;

use ApiGen\TokenReflection;
use ApiGen\TokenReflection\Broker;
use ApiGen\TokenReflection\Exception;
use Reflector, ReflectionClass as InternalReflectionClass;
use ReflectionProperty as InternalReflectionProperty;
use ReflectionMethod as InternalReflectionMethod;


/**
 * Reflection of a not tokenized but defined class.
 *
 * Descendant of the internal reflection with additional features.
 */
class ReflectionClass extends InternalReflectionClass implements IReflection, TokenReflection\IReflectionClass
{

	/**
	 * Reflection broker.
	 *
	 * @var ApiGen\TokenReflection\Broker
	 */
	private $broker;

	/**
	 * Implemented interface reflections.
	 *
	 * @var array
	 */
	private $interfaces;

	/**
	 * Metod reflections.
	 *
	 * @var array
	 */
	private $methods;

	/**
	 * Constant reflections.
	 *
	 * @var array
	 */
	private $constants;

	/**
	 * Property reflections.
	 *
	 * @var array
	 */
	private $properties;


	/**
	 * @param string $className Class name
	 * @param Broker $broker Reflection broker
	 */
	public function __construct($className, Broker $broker)
	{
		parent::__construct($className);
		$this->broker = $broker;
	}


	/**
	 * Returns the PHP extension reflection.
	 *
	 * @return ApiGen\TokenReflection\Php\ReflectionExtension
	 */
	public function getExtension()
	{
		return ReflectionExtension::create(parent::getExtension(), $this->broker);
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
	 * Returns if the class is an exception or its descendant.
	 *
	 * @return bool
	 */
	public function isException()
	{
		return 'Exception' === $this->getName() || $this->isSubclassOf('Exception');
	}


	/**
	 * Returns if objects of this class are cloneable.
	 *
	 * Introduced in PHP 5.4.
	 *
	 * @return bool
	 * @see http://svn.php.net/viewvc/php/php-src/trunk/ext/reflection/php_reflection.c?revision=307971&view=markup#l4059
	 */
	public function isCloneable()
	{
		if ($this->isInterface() || $this->isAbstract()) {
			return FALSE;
		}
		$methods = $this->getMethods();
		return isset($methods['__clone']) ? $methods['__clone']->isPublic() : TRUE;
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
	 * Returns if the current class is a subclass of the given class.
	 *
	 * @param string|object $class Class name or reflection object
	 * @return bool
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If an invalid parameter was provided.
	 */
	public function isSubclassOf($class)
	{
		if (is_object($class)) {
			if ( ! $class instanceof InternalReflectionClass && !$class instanceof IReflectionClass) {
				throw new Exception\RuntimeException('Parameter must be a string or an instance of class reflection.', Exception\RuntimeException::INVALID_ARGUMENT, $this);
			}
			$class = $class->getName();
		}
		return in_array($class, $this->getParentClassNameList());
	}


	/**
	 * Returns parent class reflection.
	 *
	 * @return ApiGen\TokenReflection\Php\ReflectionClass
	 */
	public function getParentClass()
	{
		$parent = parent::getParentClass();
		return $parent ? self::create($parent, $this->broker) : NULL;
	}


	/**
	 * Returns the parent class name.
	 *
	 * @return string
	 */
	public function getParentClassName()
	{
		$parent = $this->getParentClass();
		return $parent ? $parent->getName() : NULL;
	}


	/**
	 * Returns the parent classes reflections.
	 *
	 * @return array
	 */
	public function getParentClasses()
	{
		$broker = $this->broker;
		return array_map(function ($className) use ($broker) {
			return $broker->getClass($className);
		}, $this->getParentClassNameList());
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
	 * Returns if the class implements the given interface.
	 *
	 * @param string|object $interface Interface name or reflection object
	 * @return bool
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If the provided parameter is not an interface.
	 */
	public function implementsInterface($interface)
	{
		if (is_object($interface)) {
			if ( ! $interface instanceof InternalReflectionClass && !$interface instanceof IReflectionClass) {
				throw new Exception\RuntimeException('Parameter must be a string or an instance of class reflection.', Exception\RuntimeException::INVALID_ARGUMENT, $this);
			}
			$interfaceName = $interface->getName();
			if ( ! $interface->isInterface()) {
				throw new Exception\RuntimeException(sprintf('"%s" is not an interface.', $interfaceName), Exception\RuntimeException::INVALID_ARGUMENT, $this);
			}
		} else {
			$reflection = $this->getBroker()->getClass($interface);
			if ( ! $reflection->isInterface()) {
				throw new Exception\RuntimeException(sprintf('"%s" is not an interface.', $interface), Exception\RuntimeException::INVALID_ARGUMENT, $this);
			}
			$interfaceName = $interface;
		}
		$interfaces = $this->getInterfaces();
		return isset($interfaces[$interfaceName]);
	}


	/**
	 * Returns an array of interface reflections.
	 *
	 * @return array
	 */
	public function getInterfaces()
	{
		if (NULL === $this->interfaces) {
			$broker = $this->broker;
			$interfaceNames = $this->getInterfaceNames();
			if (empty($interfaceNames)) {
				$this->interfaces = [];
			} else {
				$this->interfaces = array_combine($interfaceNames, array_map(function ($interfaceName) use ($broker) {
					return $broker->getClass($interfaceName);
				}, $interfaceNames));
			}
		}
		return $this->interfaces;
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
	 * Returns class constructor reflection.
	 *
	 * @return ApiGen\TokenReflection\Php\ReflectionClass|null
	 */
	public function getConstructor()
	{
		return ReflectionMethod::create(parent::getConstructor(), $this->broker);
	}


	/**
	 * Returns class desctructor reflection.
	 *
	 * @return ApiGen\TokenReflection\Php\ReflectionClass|null
	 */
	public function getDestructor()
	{
		foreach ($this->getMethods() as $method) {
			if ($method->isDestructor()) {
				return $method;
			}
		}
		return NULL;
	}


	/**
	 * Returns a particular method reflection.
	 *
	 * @param string $name Method name
	 * @return ApiGen\TokenReflection\Php\ReflectionMethod
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If the requested method does not exist.
	 */
	public function getMethod($name)
	{
		foreach ($this->getMethods() as $method) {
			if ($method->getName() === $name) {
				return $method;
			}
		}
		throw new Exception\RuntimeException(sprintf('Method %s does not exist.', $name), Exception\RuntimeException::DOES_NOT_EXIST, $this);
	}


	/**
	 * Returns class methods.
	 *
	 * @param int $filter Methods filter
	 * @return array
	 */
	public function getMethods($filter = NULL)
	{
		if (NULL === $this->methods) {
			$broker = $this->broker;
			$this->methods = array_map(function (InternalReflectionMethod $method) use ($broker) {
				return ReflectionMethod::create($method, $broker);
			}, parent::getMethods());
		}
		if (NULL === $filter) {
			return $this->methods;
		}
		return array_filter($this->methods, function (ReflectionMethod $method) use ($filter) {
			return (bool) ($method->getModifiers() & $filter);
		});
	}


	/**
	 * Returns if the class implements (and not its parents) the given method.
	 *
	 * @param string $name Method name
	 * @return bool
	 */
	public function hasOwnMethod($name)
	{
		foreach ($this->getOwnMethods() as $method) {
			if ($name === $method->getName()) {
				return TRUE;
			}
		}
		return FALSE;
	}


	/**
	 * Returns methods declared by this class, not its parents.
	 *
	 * @param int $filter
	 * @return array
	 */
	public function getOwnMethods($filter = NULL)
	{
		$me = $this->getName();
		return array_filter($this->getMethods($filter), function (ReflectionMethod $method) use ($me) {
			return $method->getDeclaringClass()->getName() === $me;
		});
	}


	/**
	 * Returns if the class imports the given method from traits.
	 *
	 * @param string $name Method name
	 * @return bool
	 * @todo Impossible with the current status of reflection
	 */
	public function hasTraitMethod($name)
	{
		return FALSE;
	}


	/**
	 * Returns method reflections imported from traits.
	 *
	 * @param int $filter Methods filter
	 * @return array
	 * @todo Impossible with the current status of reflection
	 */
	public function getTraitMethods($filter = NULL)
	{
		return [];
	}


	/**
	 * Returns a constant reflection.
	 *
	 * @param string $name Constant name
	 * @return ApiGen\TokenReflection\ReflectionConstant
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If the requested constant does not exist.
	 */
	public function getConstantReflection($name)
	{
		if ($this->hasConstant($name)) {
			return new ReflectionConstant($name, $this->getConstant($name), $this->broker, $this);
		}
		throw new Exception\RuntimeException(sprintf('Constant "%s" does not exist.', $name), Exception\RuntimeException::DOES_NOT_EXIST, $this);
	}


	/**
	 * Returns an array of constant reflections.
	 *
	 * @return array
	 */
	public function getConstantReflections()
	{
		if (NULL === $this->constants) {
			$this->constants = [];
			foreach ($this->getConstants() as $name => $value) {
				$this->constants[$name] = $this->getConstantReflection($name);
			}
		}
		return array_values($this->constants);
	}


	/**
	 * Returns if the class (and not its parents) defines the given constant.
	 *
	 * @param string $name Constant name.
	 * @return bool
	 */
	public function hasOwnConstant($name)
	{
		$constants = $this->getOwnConstants();
		return isset($constants[$name]);
	}


	/**
	 * Returns constants declared by this class, not its parents.
	 *
	 * @return array
	 */
	public function getOwnConstants()
	{
		return array_diff_assoc($this->getConstants(), $this->getParentClass() ? $this->getParentClass()->getConstants() : []);
	}


	/**
	 * Returns an array of constant reflections defined by this class and not its parents.
	 *
	 * @return array
	 */
	public function getOwnConstantReflections()
	{
		$constants = [];
		foreach ($this->getOwnConstants() as $name => $value) {
			$constants[] = $this->getConstantReflection($name);
		}
		return $constants;
	}


	/**
	 * Returns a particular property reflection.
	 *
	 * @param string $name Property name
	 * @return ApiGen\TokenReflection\Php\ReflectionProperty
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If the requested property does not exist.
	 */
	public function getProperty($name)
	{
		foreach ($this->getProperties() as $property) {
			if ($name === $property->getName()) {
				return $property;
			}
		}
		throw new Exception\RuntimeException(sprintf('Property %s does not exist.', $name), Exception\RuntimeException::DOES_NOT_EXIST, $this);
	}


	/**
	 * Returns class properties.
	 *
	 * @param int $filter Properties filter
	 * @return array
	 */
	public function getProperties($filter = NULL)
	{
		if (NULL === $this->properties) {
			$broker = $this->broker;
			$this->properties = array_map(function (InternalReflectionProperty $property) use ($broker) {
				return ReflectionProperty::create($property, $broker);
			}, parent::getProperties());
		}
		if (NULL === $filter) {
			return $this->properties;
		}
		return array_filter($this->properties, function (ReflectionProperty $property) use ($filter) {
			return (bool) ($property->getModifiers() & $filter);
		});
	}


	/**
	 * Returns if the class has (and not its parents) the given property.
	 *
	 * @param string $name Property name
	 * @return bool
	 */
	public function hasOwnProperty($name)
	{
		foreach ($this->getOwnProperties() as $property) {
			if ($name === $property->getName()) {
				return TRUE;
			}
		}
		return FALSE;
	}


	/**
	 * Returns properties declared by this class, not its parents.
	 *
	 * @param int $filter
	 * @return array
	 */
	public function getOwnProperties($filter = NULL)
	{
		$me = $this->getName();
		return array_filter($this->getProperties($filter), function (ReflectionProperty $property) use ($me) {
			return $property->getDeclaringClass()->getName() === $me;
		});
	}


	/**
	 * Returns if the class imports the given property from traits.
	 *
	 * @param string $name Property name
	 * @return bool
	 * @todo Impossible with the current status of reflection
	 */
	public function hasTraitProperty($name)
	{
		return FALSE;
	}


	/**
	 * Returns property reflections imported from traits.
	 *
	 * @param int $filter Properties filter
	 * @return array
	 * @todo Impossible with the current status of reflection
	 */
	public function getTraitProperties($filter = NULL)
	{
		return [];
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
	 * Returns reflections of direct subclasses.
	 *
	 * @return array
	 */
	public function getDirectSubclasses()
	{
		$that = $this->name;
		return array_filter($this->getBroker()->getClasses(Broker\Backend::INTERNAL_CLASSES | Broker\Backend::TOKENIZED_CLASSES), function (IReflectionClass $class) use (
			$that
		) {
			if ( ! $class->isSubclassOf($that)) {
				return FALSE;
			}
			return NULL === $class->getParentClassName() || !$class->getParentClass()->isSubClassOf($that);
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
		return array_filter($this->getBroker()->getClasses(Broker\Backend::INTERNAL_CLASSES | Broker\Backend::TOKENIZED_CLASSES), function (IReflectionClass $class) use (
			$that
		) {
			if ( ! $class->isSubclassOf($that)) {
				return FALSE;
			}
			return NULL !== $class->getParentClassName() && $class->getParentClass()->isSubClassOf($that);
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
		if ( ! $this->isInterface()) {
			return [];
		}
		$that = $this->name;
		return array_filter($this->getBroker()->getClasses(Broker\Backend::INTERNAL_CLASSES | Broker\Backend::TOKENIZED_CLASSES), function (IReflectionClass $class) use (
			$that
		) {
			if ( ! $class->implementsInterface($that)) {
				return FALSE;
			}
			return NULL === $class->getParentClassName() || !$class->getParentClass()->implementsInterface($that);
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
		if ( ! $this->isInterface()) {
			return [];
		}
		$that = $this->name;
		return array_filter($this->getBroker()->getClasses(Broker\Backend::INTERNAL_CLASSES | Broker\Backend::TOKENIZED_CLASSES), function (IReflectionClass $class) use (
			$that
		) {
			if ( ! $class->implementsInterface($that)) {
				return FALSE;
			}
			return NULL !== $class->getParentClassName() && $class->getParentClass()->implementsInterface($that);
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
	 * Returns if the class definition is complete.
	 *
	 * Internal classes always have the definition complete.
	 *
	 * @return bool
	 */
	public function isComplete()
	{
		return TRUE;
	}


	/**
	 * Returns if the class definition is valid.
	 *
	 * Internal classes are always valid.
	 *
	 * @return bool
	 */
	public function isValid()
	{
		return TRUE;
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
	 * Returns the reflection broker used by this reflection object.
	 *
	 * @return ApiGen\TokenReflection\Broker
	 */
	public function getBroker()
	{
		return $this->broker;
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
	 * Returns traits used by this class and not its parents.
	 *
	 * @return array
	 */
	public function getOwnTraits()
	{
		$parent = $this->getParentClass();
		return $parent ? array_diff_key($this->getTraits(), $parent->getTraits()) : $this->getTraits();
	}


	/**
	 * Returns traits used by this class and not its parents.
	 *
	 * @return array
	 */
	public function getOwnTraitNames()
	{
		return array_keys($this->getOwnTraits());
	}


	/**
	 * Returns if the class uses a particular trait.
	 *
	 * @param \ReflectionClass|\TokenReflection\IReflectionClass|string $trait Trait reflection or name
	 * @return bool
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If an invalid parameter was provided.
	 */
	public function usesTrait($trait)
	{
		if (is_object($trait)) {
			if ( ! $trait instanceof InternalReflectionClass && !$trait instanceof TokenReflection\IReflectionClass) {
				throw new Exception\RuntimeException('Parameter must be a string or an instance of trait reflection.', Exception\RuntimeException::INVALID_ARGUMENT, $this);
			}
			$traitName = $trait->getName();
			if ( ! $trait->isTrait()) {
				throw new Exception\RuntimeException(sprintf('"%s" is not a trait.', $traitName), Exception\RuntimeException::INVALID_ARGUMENT, $this);
			}
		} else {
			$reflection = $this->getBroker()->getClass($trait);
			if ( ! $reflection->isTrait()) {
				throw new Exception\RuntimeException(sprintf('"%s" is not a trait.', $trait), Exception\RuntimeException::INVALID_ARGUMENT, $this);
			}
			$traitName = $trait;
		}
		return in_array($traitName, $this->getTraitNames());
	}


	/**
	 * Creates a new class instance without using a constructor.
	 *
	 * @return object
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If the class inherits from an internal class.
	 */
	public function newInstanceWithoutConstructor()
	{
		if ($this->isInternal()) {
			throw new Exception\RuntimeException('Could not create an instance; only user defined classes can be instantiated.', Exception\RuntimeException::UNSUPPORTED, $this);
		}
		foreach ($this->getParentClasses() as $parent) {
			if ($parent->isInternal()) {
				throw new Exception\RuntimeException('Could not create an instance; only user defined classes can be instantiated.', Exception\RuntimeException::UNSUPPORTED, $this);
			}
		}
		return parent::newInstanceWithoutConstructor();
	}


	/**
	 * Returns an element pretty (docblock compatible) name.
	 *
	 * @return string
	 */
	public function getPrettyName()
	{
		return $this->getName();
	}


	/**
	 * Creates a reflection instance.
	 *
	 * @param \ReflectionClass $internalReflection Internal reflection instance
	 * @param ApiGen\TokenReflection\Broker $broker Reflection broker instance
	 * @return ApiGen\TokenReflection\Php\ReflectionClass
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If an invalid internal reflection object was provided.
	 */
	public static function create(Reflector $internalReflection, Broker $broker)
	{
		if ( ! $internalReflection instanceof InternalReflectionClass) {
			throw new Exception\RuntimeException('Invalid reflection instance provided, ReflectionClass expected.', Exception\RuntimeException::INVALID_ARGUMENT);
		}
		return $broker->getClass($internalReflection->getName());
	}

}
