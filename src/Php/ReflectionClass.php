<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Php;

use ApiGen;
use ApiGen\TokenReflection\Behaviors\AnnotationsInterface;
use ApiGen\TokenReflection\Behaviors\ExtensionInterface;
use ApiGen\TokenReflection\Broker\StorageInterface;
use ApiGen\TokenReflection\Broker\Broker;
use ApiGen\TokenReflection\Exception;
use ApiGen\TokenReflection\Exception\RuntimeException;
use ApiGen\TokenReflection\Php\Factory\ReflectionClassFactory;
use ApiGen\TokenReflection\Php\Factory\ReflectionExtensionFactory;
use ApiGen\TokenReflection\Php\Factory\ReflectionMethodFactory;
use ApiGen\TokenReflection\Php\Factory\ReflectionPropertyFactory;
use ApiGen\TokenReflection\ReflectionClassInterface;
use ApiGen\TokenReflection\ReflectionConstantInterface;
use Reflector;
use ReflectionClass as InternalReflectionClass;
use ReflectionProperty as InternalReflectionProperty;
use ReflectionMethod as InternalReflectionMethod;


class ReflectionClass extends InternalReflectionClass implements ReflectionInterface, ReflectionClassInterface, AnnotationsInterface, ExtensionInterface
{

	/**
	 * @var Broker
	 */
	private $storage;

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
	 * @param string $name
	 * @param StorageInterface $storage
	 */
	public function __construct($name, StorageInterface $storage)
	{
		parent::__construct($name);
		$this->storage = $storage;
	}


	/**
	 * @return ReflectionExtension
	 */
	public function getExtension()
	{
		return ReflectionExtensionFactory::create(parent::getExtension(), $this->storage);
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
		return $this->getName() === 'Exception' || $this->isSubclassOf('Exception');
	}


	/**
	 * {@inheritdoc}
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
			if ( ! $class instanceof InternalReflectionClass && !$class instanceof ReflectionClassInterface) {
				throw new RuntimeException('Parameter must be a string or an instance of class reflection.');
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
		return $parent ? ReflectionClassFactory::create($parent, $this->storage) : NULL;
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
		return array_map(function ($className) {
			return $this->storage->getClass($className);
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
			if ( ! $interface instanceof InternalReflectionClass && !$interface instanceof ReflectionClassInterface) {
				throw new RuntimeException('Parameter must be a string or an instance of class reflection.');
			}
			$interfaceName = $interface->getName();
			if ( ! $interface->isInterface()) {
				throw new RuntimeException(sprintf('"%s" is not an interface.', $interfaceName));
			}

		} else {
			$reflection = $this->storage->getClass($interface);
			if ( ! $reflection->isInterface()) {
				throw new RuntimeException(sprintf('"%s" is not an interface.', $interface));
			}
			$interfaceName = $interface;
		}
		return isset($this->getInterfaces()[$interfaceName]);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getInterfaces()
	{
		if ($this->interfaces === NULL) {
			$interfaceNames = $this->getInterfaceNames();
			if (empty($interfaceNames)) {
				$this->interfaces = [];
			} else {
				$this->interfaces = array_combine($interfaceNames, array_map(function ($interfaceName) {
					return $this->storage->getClass($interfaceName);
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
		return ReflectionMethodFactory::create(parent::getConstructor(), $this->storage);
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
			if ($name === $method->getName()) {
				return $method;
			}
		}
		throw new RuntimeException(sprintf('Method %s does not exist.', $name), RuntimeException::DOES_NOT_EXIST);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getMethods($filter = NULL)
	{
		if ($this->methods === NULL) {
			$this->methods = array_map(function (InternalReflectionMethod $method) {
				return ReflectionMethodFactory::create($method, $this->storage);
			}, parent::getMethods());
		}
		if ($filter === NULL) {
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
		return array_filter($this->getMethods($filter), function (ReflectionMethod $method) {
			return $this->getName() === $method->getDeclaringClass()->getName();
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
	 * @param string $name
	 * @return bool
	 */
	public function hasConstant($name)
	{
		return isset($this->getConstants()[$name]);
	}


	/**
	 * @param string $name
	 * @return ReflectionConstantInterface|NULL
	 */
	public function getConstant($name)
	{
		if ($this->hasConstant($name)) {
			return $this->getConstants()[$name];
		}
		return NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getConstantReflection($name)
	{
		if ($this->hasConstant($name)) {
			return new ReflectionConstant($name, $this->getConstant($name), $this->storage, $this);
		}
		throw new RuntimeException(sprintf('Constant "%s" does not exist.', $name));
	}


	/**
	 * {@inheritdoc}
	 */
	public function getConstantReflections()
	{
		if ($this->constants === NULL) {
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
		return isset($this->getOwnConstants()[$name]);
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
		throw new RuntimeException(sprintf('Property %s does not exist.', $name));
	}


	/**
	 * {@inheritdoc}
	 */
	public function getProperties($filter = NULL)
	{
		if ($this->properties === NULL) {
			$this->properties = array_map(function (InternalReflectionProperty $property) {
				return ReflectionPropertyFactory::create($property, $this->storage);
			}, parent::getProperties());
		}
		if ($filter === NULL) {
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
		return array_filter($this->getProperties($filter), function (ReflectionProperty $property) {
			return $property->getDeclaringClass()->getName() === $this->getName();
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
	public function getStaticPropertyValue($name, $default = NULL)
	{
		return parent::getStaticPropertyValue($name, $default );
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
		return array_filter($this->getInternalTokenizedClasses(), function (ReflectionClassInterface $class) {
			if ( ! $class->isSubclassOf($this->name)) {
				return FALSE;
			}
			return $class->getParentClassName() === NULL || ! $class->getParentClass()->isSubClassOf($this->name);
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
		return array_filter($this->getInternalTokenizedClasses(), function (ReflectionClassInterface $class) {
			if ( ! $class->isSubclassOf($this->name)) {
				return FALSE;
			}
			return NULL !== $class->getParentClassName() && $class->getParentClass()->isSubClassOf($this->name);
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
		return array_filter($this->getInternalTokenizedClasses(), function (ReflectionClassInterface $class) {
			if ( ! $class->implementsInterface($this->name)) {
				return FALSE;
			}
			return $class->getParentClassName() === NULL || !$class->getParentClass()->implementsInterface($this->name);
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
		return array_filter($this->getInternalTokenizedClasses(), function (ReflectionClassInterface $class) {
			if ( ! $class->implementsInterface($this->name)) {
				return FALSE;
			}
			return NULL !== $class->getParentClassName() && $class->getParentClass()->implementsInterface($this->name);
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
	public function getNamespaceAliases()
	{
		return [];
	}


	/**
	 * {@inheritdoc}
	 */
	public function getStorage()
	{
		return $this->storage;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getOwnTraits()
	{
		return [];
	}


	/**
	 * {@inheritdoc}
	 */
	public function getOwnTraitNames()
	{
		return [];
	}


	/**
	 * {@inheritdoc}
	 */
	public function usesTrait($trait)
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getPrettyName()
	{
		return $this->getName();
	}


	/**
	 * @return ReflectionClassInterface[]
	 */
	private function getInternalTokenizedClasses()
	{
		return $this->storage->getClasses(StorageInterface::INTERNAL_CLASSES | StorageInterface::TOKENIZED_CLASSES);
	}

}
