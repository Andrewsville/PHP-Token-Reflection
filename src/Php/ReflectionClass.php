<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Php;

use ApiGen\TokenReflection\Behaviors\Annotations;
use ApiGen\TokenReflection\Broker\Backend;
use ApiGen\TokenReflection\Broker\Broker;
use ApiGen\TokenReflection\Exception;
use ApiGen\TokenReflection\Exception\RuntimeException;
use ApiGen\TokenReflection\IReflectionClass;
use ApiGen\TokenReflection\ReflectionElement;
use Reflector;
use ReflectionClass as InternalReflectionClass;
use ReflectionProperty as InternalReflectionProperty;
use ReflectionMethod as InternalReflectionMethod;


class ReflectionClass extends InternalReflectionClass implements IReflection, IReflectionClass, Annotations
{

	/**
	 * @var Broker
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
	 * @param Broker $broker
	 */
	public function __construct($className, Broker $broker)
	{
		parent::__construct($className);
		$this->broker = $broker;
	}


	/**
	 * @return ReflectionExtension
	 */
	public function getExtension()
	{
		return ReflectionExtension::create(parent::getExtension(), $this->broker);
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
	 * {@inheritdoc}
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
	public function isSubclassOf($class)
	{
		if (is_object($class)) {
			if ( ! $class instanceof InternalReflectionClass && !$class instanceof IReflectionClass) {
				throw new RuntimeException('Parameter must be a string or an instance of class reflection.', RuntimeException::INVALID_ARGUMENT, $this);
			}
			$class = $class->getName();
		}
		return in_array($class, $this->getParentClassNameList());
	}


	/**
	 * {@inheritdoc}
	 */
	public function getParentClass()
	{
		$parent = parent::getParentClass();
		return $parent ? self::create($parent, $this->broker) : NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getParentClassName()
	{
		$parent = $this->getParentClass();
		return $parent ? $parent->getName() : NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getParentClasses()
	{
		$broker = $this->broker;
		return array_map(function ($className) use ($broker) {
			return $broker->getClass($className);
		}, $this->getParentClassNameList());
	}


	/**
	 * {@inheritdoc}
	 */
	public function getParentClassNameList()
	{
		return class_parents($this->getName());
	}


	/**
	 * {@inheritdoc}
	 */
	public function implementsInterface($interface)
	{
		if (is_object($interface)) {
			if ( ! $interface instanceof InternalReflectionClass && !$interface instanceof IReflectionClass) {
				throw new RuntimeException('Parameter must be a string or an instance of class reflection.', RuntimeException::INVALID_ARGUMENT, $this);
			}
			$interfaceName = $interface->getName();
			if ( ! $interface->isInterface()) {
				throw new RuntimeException(sprintf('"%s" is not an interface.', $interfaceName), RuntimeException::INVALID_ARGUMENT, $this);
			}
		} else {
			$reflection = $this->getBroker()->getClass($interface);
			if ( ! $reflection->isInterface()) {
				throw new RuntimeException(sprintf('"%s" is not an interface.', $interface), RuntimeException::INVALID_ARGUMENT, $this);
			}
			$interfaceName = $interface;
		}
		$interfaces = $this->getInterfaces();
		return isset($interfaces[$interfaceName]);
	}


	/**
	 * {@inheritdoc}
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
	 * {@inheritdoc}
	 */
	public function getOwnInterfaces()
	{
		$parent = $this->getParentClass();
		return $parent ? array_diff_key($this->getInterfaces(), $parent->getInterfaces()) : $this->getInterfaces();
	}


	/**
	 * {@inheritdoc}
	 */
	public function getOwnInterfaceNames()
	{
		return array_keys($this->getOwnInterfaces());
	}


	/**
	 * {@inheritdoc}
	 */
	public function getConstructor()
	{
		return ReflectionMethod::create(parent::getConstructor(), $this->broker);
	}


	/**
	 * {@inheritdoc}
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
	 * {@inheritdoc}
	 */
	public function hasMethod($name)
	{
		return isset($this->getMethods()[$name]);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getMethod($name)
	{
		foreach ($this->getMethods() as $method) {
			if ($method->getName() === $name) {
				return $method;
			}
		}
		throw new RuntimeException(sprintf('Method %s does not exist.', $name), RuntimeException::DOES_NOT_EXIST, $this);
	}


	/**
	 * {@inheritdoc}
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
	 * {@inheritdoc}
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
	 * {@inheritdoc}
	 */
	public function getOwnMethods($filter = NULL)
	{
		$me = $this->getName();
		return array_filter($this->getMethods($filter), function (ReflectionMethod $method) use ($me) {
			return $method->getDeclaringClass()->getName() === $me;
		});
	}


	/**
	 * {@inheritdoc}
	 */
	public function hasTraitMethod($name)
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getTraitMethods($filter = NULL)
	{
		return [];
	}


	/**
	 * {@inheritdoc}
	 */
	public function getConstantReflection($name)
	{
		if ($this->hasConstant($name)) {
			return new ReflectionConstant($name, $this->getConstant($name), $this->broker, $this);
		}
		throw new RuntimeException(sprintf('Constant "%s" does not exist.', $name), RuntimeException::DOES_NOT_EXIST, $this);
	}


	/**
	 * {@inheritdoc}
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
	 * {@inheritdoc}
	 */
	public function hasOwnConstant($name)
	{
		$constants = $this->getOwnConstants();
		return isset($constants[$name]);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getOwnConstants()
	{
		return array_diff_assoc($this->getConstants(), $this->getParentClass() ? $this->getParentClass()->getConstants() : []);
	}


	/**
	 * {@inheritdoc}
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
	 * {@inheritdoc}
	 */
	public function getProperty($name)
	{
		foreach ($this->getProperties() as $property) {
			if ($name === $property->getName()) {
				return $property;
			}
		}
		throw new RuntimeException(sprintf('Property %s does not exist.', $name), RuntimeException::DOES_NOT_EXIST, $this);
	}


	/**
	 * {@inheritdoc}
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
	 * {@inheritdoc}
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
	 * {@inheritdoc}
	 */
	public function getOwnProperties($filter = NULL)
	{
		$me = $this->getName();
		return array_filter($this->getProperties($filter), function (ReflectionProperty $property) use ($me) {
			return $property->getDeclaringClass()->getName() === $me;
		});
	}


	/**
	 * {@inheritdoc}
	 */
	public function hasTraitProperty($name)
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getTraitProperties($filter = NULL)
	{
		return [];
	}


	/**
	 * {@inheritdoc}
	 */
	public function getStaticProperties()
	{
		return $this->getProperties(InternalReflectionProperty::IS_STATIC);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDirectSubclasses()
	{
		$that = $this->name;
		return array_filter($this->getBroker()->getClasses(Backend::INTERNAL_CLASSES | Backend::TOKENIZED_CLASSES), function (IReflectionClass $class) use (
			$that
		) {
			if ( ! $class->isSubclassOf($that)) {
				return FALSE;
			}
			return NULL === $class->getParentClassName() || !$class->getParentClass()->isSubClassOf($that);
		});
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDirectSubclassNames()
	{
		return array_keys($this->getDirectSubclasses());
	}


	/**
	 * {@inheritdoc}
	 */
	public function getIndirectSubclasses()
	{
		$that = $this->name;
		return array_filter($this->getBroker()->getClasses(Backend::INTERNAL_CLASSES | Backend::TOKENIZED_CLASSES), function (IReflectionClass $class) use (
			$that
		) {
			if ( ! $class->isSubclassOf($that)) {
				return FALSE;
			}
			return NULL !== $class->getParentClassName() && $class->getParentClass()->isSubClassOf($that);
		});
	}


	/**
	 * {@inheritdoc}
	 */
	public function getIndirectSubclassNames()
	{
		return array_keys($this->getIndirectSubclasses());
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDirectImplementers()
	{
		if ( ! $this->isInterface()) {
			return [];
		}
		$that = $this->name;
		return array_filter($this->getBroker()->getClasses(Backend::INTERNAL_CLASSES | Backend::TOKENIZED_CLASSES), function (IReflectionClass $class) use (
			$that
		) {
			if ( ! $class->implementsInterface($that)) {
				return FALSE;
			}
			return NULL === $class->getParentClassName() || !$class->getParentClass()->implementsInterface($that);
		});
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDirectImplementerNames()
	{
		return array_keys($this->getDirectImplementers());
	}


	/**
	 * {@inheritdoc}
	 */
	public function getIndirectImplementers()
	{
		if ( ! $this->isInterface()) {
			return [];
		}
		$that = $this->name;
		return array_filter($this->getBroker()->getClasses(Backend::INTERNAL_CLASSES | Backend::TOKENIZED_CLASSES), function (IReflectionClass $class) use (
			$that
		) {
			if ( ! $class->implementsInterface($that)) {
				return FALSE;
			}
			return NULL !== $class->getParentClassName() && $class->getParentClass()->implementsInterface($that);
		});
	}


	/**
	 * {@inheritdoc}
	 */
	public function getIndirectImplementerNames()
	{
		return array_keys($this->getIndirectImplementers());
	}


	/**
	 * {@inheritdoc}
	 */
	public function isComplete()
	{
		return TRUE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isValid()
	{
		return TRUE;
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
	public function getBroker()
	{
		return $this->broker;
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
	 * {@inheritdoc}
	 */
	public function getOwnTraits()
	{
		$parent = $this->getParentClass();
		return $parent ? array_diff_key($this->getTraits(), $parent->getTraits()) : $this->getTraits();
	}


	/**
	 * {@inheritdoc}
	 */
	public function getOwnTraitNames()
	{
		return array_keys($this->getOwnTraits());
	}


	/**
	 * {@inheritdoc}
	 */
	public function usesTrait($trait)
	{
		if (is_object($trait)) {
			if ( ! $trait instanceof InternalReflectionClass && !$trait instanceof IReflectionClass) {
				throw new RuntimeException('Parameter must be a string or an instance of trait reflection.', RuntimeException::INVALID_ARGUMENT, $this);
			}
			$traitName = $trait->getName();
			if ( ! $trait->isTrait()) {
				throw new RuntimeException(sprintf('"%s" is not a trait.', $traitName), RuntimeException::INVALID_ARGUMENT, $this);
			}
		} else {
			$reflection = $this->getBroker()->getClass($trait);
			if ( ! $reflection->isTrait()) {
				throw new RuntimeException(sprintf('"%s" is not a trait.', $trait), RuntimeException::INVALID_ARGUMENT, $this);
			}
			$traitName = $trait;
		}
		return in_array($traitName, $this->getTraitNames());
	}


	/**
	 * {@inheritdoc}
	 */
	public function newInstanceWithoutConstructor()
	{
		if ($this->isInternal()) {
			throw new RuntimeException('Could not create an instance; only user defined classes can be instantiated.', RuntimeException::UNSUPPORTED, $this);
		}
		foreach ($this->getParentClasses() as $parent) {
			if ($parent->isInternal()) {
				throw new RuntimeException('Could not create an instance; only user defined classes can be instantiated.', RuntimeException::UNSUPPORTED, $this);
			}
		}
		return parent::newInstanceWithoutConstructor();
	}


	/**
	 * {@inheritdoc}
	 */
	public function getPrettyName()
	{
		return $this->getName();
	}


	/**
	 * Creates a reflection instance.
	 *
	 * @return ReflectionClass
	 * @throws RuntimeException If an invalid internal reflection object was provided.
	 */
	public static function create(Reflector $internalReflection, Broker $broker)
	{
		if ( ! $internalReflection instanceof InternalReflectionClass) {
			throw new RuntimeException('Invalid reflection instance provided, ReflectionClass expected.', RuntimeException::INVALID_ARGUMENT);
		}
		return $broker->getClass($internalReflection->getName());
	}

}
