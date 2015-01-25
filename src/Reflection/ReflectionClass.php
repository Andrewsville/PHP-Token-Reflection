<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Reflection;

use ApiGen;
use ApiGen\TokenReflection\Storage\StorageInterface;
use ApiGen\TokenReflection\Exception;
use ApiGen\TokenReflection\Exception\ParseException;
use ApiGen\TokenReflection\Exception\RuntimeException;
use ApiGen\TokenReflection\Parser\ClassParser;
use ApiGen\TokenReflection\ReflectionConstantInterface;
use ApiGen\TokenReflection\ReflectionInterface;
use ApiGen\TokenReflection\ReflectionClassInterface;
use ApiGen\TokenReflection\ReflectionMethodInterface;
use ApiGen\TokenReflection\ReflectionPropertyInterface;
use ApiGen\TokenReflection\Resolver;
use ApiGen\TokenReflection\Stream\StreamBase;
use ReflectionClass as InternalReflectionClass;
use ReflectionProperty as InternalReflectionProperty;
use ReflectionMethod as InternalReflectionMethod;


class ReflectionClass extends ReflectionElement implements ReflectionClassInterface
{

	/**
	 * @var int
	 */
	const IS_INTERFACE = 0x80;

	/**
	 * @var int
	 */
	const IS_TRAIT = 0x120;

	/**
	 * @var int
	 */
	const IMPLEMENTS_INTERFACES = 0x80000;

	/**
	 * @var int
	 */
	const IMPLEMENTS_TRAITS = 0x400000;

	/**
	 * @var string
	 */
	private $namespaceName;

	/**
	 * @var int
	 */
	private $modifiers = 0;

	/**
	 * Class type (class/interface/trait).
	 *
	 * @var int
	 */
	private $type = 0;

	/**
	 * @var bool
	 */
	private $modifiersComplete = FALSE;

	/**
	 * @var string
	 */
	private $parentClassName;

	/**
	 * Implemented interface names.
	 *
	 * @var string[]
	 */
	private $interfaces = [];

	/**
	 * @var array
	 */
	private $traits = [];

	/**
	 * Aliases used at trait methods.
	 *
	 * Compatible with the internal reflection.
	 *
	 * @var array
	 */
	private $traitAliases = [];

	/**
	 * Trait importing rules.
	 *
	 * Format:
	 * [<trait>::]<method> => array(
	 *    array(<new-name>, [<access-level>])|null
	 *      [, ...]
	 * )
	 *
	 * @var array
	 */
	private $traitImports = [];

	/**
	 * @var ReflectionMethodInterface[]
	 */
	private $methods = [];

	/**
	 * @var ReflectionConstantInterface[]
	 */
	private $constants = [];

	/**
	 * @var ReflectionPropertyInterface[]
	 */
	private $properties = [];

	/**
	 * Stores if the class definition is complete.
	 *
	 * @var bool
	 */
	private $definitionComplete = FALSE;

	/**
	 * Imported namespace/class aliases.
	 *
	 * @var array
	 */
	private $aliases = [];

	/**
	 * @var ClassParser
	 */
	private $classParser;


	public function __construct(StreamBase $tokenStream, StorageInterface $storage, ReflectionInterface $parent = NULL)
	{
		$this->classParser = new ClassParser($tokenStream, $this, $parent);

		$this->storage = $storage;
		parent::__construct($tokenStream, $storage, $parent);
	}


	/**
	 * @return string
	 */
	public function getAliases()
	{
		return $this->aliases;
	}


	/**
	 * @return array
	 */
	public function getTraitImports()
	{
		return $this->traitImports;
	}


	/**
	 * {@inheritdoc}
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
	 * {@inheritdoc}
	 */
	public function getNamespaceName()
	{
		return $this->namespaceName === ReflectionNamespace::NO_NAMESPACE_NAME ? '' : $this->namespaceName;
	}


	/**
	 * {@inheritdoc}
	 */
	public function inNamespace()
	{
		return NULL !== $this->namespaceName && ReflectionNamespace::NO_NAMESPACE_NAME !== $this->namespaceName;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getModifiers()
	{
		if (FALSE === $this->modifiersComplete) {
			if (($this->modifiers & InternalReflectionClass::IS_EXPLICIT_ABSTRACT) && !($this->modifiers & InternalReflectionClass::IS_IMPLICIT_ABSTRACT)) {
				foreach ($this->getMethods() as $reflectionMethod) {
					if ($reflectionMethod->isAbstract()) {
						$this->modifiers |= InternalReflectionClass::IS_IMPLICIT_ABSTRACT;
					}
				}
				if ( ! empty($this->interfaces)) {
					$this->modifiers |= InternalReflectionClass::IS_IMPLICIT_ABSTRACT;
				}
			}
			if ( ! empty($this->interfaces)) {
				$this->modifiers |= self::IMPLEMENTS_INTERFACES;
			}
			if ($this->isInterface() && !empty($this->methods)) {
				$this->modifiers |= InternalReflectionClass::IS_IMPLICIT_ABSTRACT;
			}
			if ( ! empty($this->traits)) {
				$this->modifiers |= self::IMPLEMENTS_TRAITS;
			}
			$this->modifiersComplete = NULL === $this->parentClassName || $this->getParentClass()->isComplete();
			if ($this->modifiersComplete) {
				foreach ($this->getInterfaces() as $interface) {
					if ( ! $interface->isComplete()) {
						$this->modifiersComplete = FALSE;
						break;
					}
				}
			}
			if ($this->modifiersComplete) {
				foreach ($this->getTraits() as $trait) {
					if ( ! $trait->isComplete()) {
						$this->modifiersComplete = FALSE;
						break;
					}
				}
			}
		}
		return $this->modifiers;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isAbstract()
	{
		if ($this->modifiers & InternalReflectionClass::IS_EXPLICIT_ABSTRACT) {
			return TRUE;
		} elseif ($this->isInterface() && !empty($this->methods)) {
			return TRUE;
		}
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isFinal()
	{
		return (bool) ($this->modifiers & InternalReflectionClass::IS_FINAL);
	}


	/**
	 * {@inheritdoc}
	 */
	public function isInterface()
	{
		return (bool) ($this->modifiers & self::IS_INTERFACE);
	}


	/**
	 * {@inheritdoc}
	 */
	public function isException()
	{
		return 'Exception' === $this->name || $this->isSubclassOf('Exception');
	}


	/**
	 * {@inheritdoc}
	 */
	public function isInstantiable()
	{
		if ($this->isInterface() || $this->isAbstract()) {
			return FALSE;
		}
		if (NULL === ($constructor = $this->getConstructor())) {
			return TRUE;
		}
		return $constructor->isPublic();
	}


	/**
	 * {@inheritdoc}
	 */
	public function isCloneable()
	{
		if ($this->isInterface() || $this->isAbstract()) {
			return FALSE;
		}
		if ($this->hasMethod('__clone')) {
			return $this->getMethod('__clone')->isPublic();
		}
		return TRUE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isIterateable()
	{
		return $this->implementsInterface('Traversable');
	}


	/**
	 * Returns if the current class is a subclass of the given class.
	 *
	 * @param string|object $class Class name or reflection object
	 * @return bool
	 * @throws RuntimeException If the provided parameter is not a reflection class instance.
	 */
	public function isSubclassOf($class)
	{
		if (is_object($class)) {
			if ($class instanceof InternalReflectionClass || $class instanceof ReflectionClassInterface) {
				$class = $class->getName();
			} else {
				$class = get_class($class);
			}
		}
		if ($class === $this->parentClassName) {
			return TRUE;
		}
		$parent = $this->getParentClass();
		return $parent === FALSE ? FALSE : $parent->isSubclassOf($class);
	}


	/**
	 * Returns the parent class reflection.
	 *
	 * @return ReflectionClassInterface|bool
	 */
	public function getParentClass()
	{
		$className = $this->getParentClassName();
		if (NULL === $className) {
			return FALSE;
		}
		return $this->storage->getClass($className);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getParentClassName()
	{
		return $this->parentClassName;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getParentClasses()
	{
		$parent = $this->getParentClass();
		if (FALSE === $parent) {
			return [];
		}
		return array_merge([$parent->getName() => $parent], $parent->getParentClasses());
	}


	/**
	 * {@inheritdoc}
	 */
	public function getParentClassNameList()
	{
		$parent = $this->getParentClass();
		if (FALSE === $parent) {
			return [];
		}
		return array_merge([$parent->getName()], $parent->getParentClassNameList());
	}


	/**
	 * {@inheritdoc}
	 */
	public function implementsInterface($interface)
	{
		if (is_object($interface)) {
			if ( ! $interface instanceof InternalReflectionClass && !$interface instanceof ReflectionClassInterface) {
				throw new RuntimeException(sprintf('Parameter must be a string or an instance of class reflection, "%s" provided.', get_class($interface)));
			}
			if ( ! $interface->isInterface()) {
				throw new RuntimeException(sprintf('"%s" is not an interface.', $interface->getName()));
			}
			$interfaceName = $interface->getName();
		} else {
			$interfaceName = $interface;
		}
		return in_array($interfaceName, $this->getInterfaceNames());
	}


	/**
	 * {@inheritdoc}
	 */
	public function getInterfaces()
	{
		$interfaceNames = $this->getInterfaceNames();
		if (empty($interfaceNames)) {
			return [];
		}
		return array_combine($interfaceNames, array_map(function ($interfaceName) {
			return $this->storage->getClass($interfaceName);
		}, $interfaceNames));
	}


	/**
	 * {@inheritdoc}
	 */
	public function getInterfaceNames()
	{
		$parentClass = $this->getParentClass();
		$names = FALSE !== $parentClass ? array_reverse(array_flip($parentClass->getInterfaceNames())) : [];
		foreach ($this->interfaces as $interfaceName) {
			$names[$interfaceName] = TRUE;
			foreach (array_reverse($this->storage->getClass($interfaceName)->getInterfaceNames()) as $parentInterfaceName) {
				$names[$parentInterfaceName] = TRUE;
			}
		}
		return array_keys($names);
	}


	/**
	 * Returns reflections of interfaces implemented by this class, not its parents.
	 *
	 * @return array|ReflectionClass[]
	 */
	public function getOwnInterfaces()
	{
		if (empty($this->interfaces)) {
			return [];
		}
		return array_combine($this->interfaces, array_map(function ($interfaceName) {
			return $this->storage->getClass($interfaceName);
		}, $this->interfaces));
	}


	/**
	 * Returns the class constructor reflection.
	 *
	 * @return \ApiGen\TokenReflection\Reflection\ReflectionMethod|null
	 */
	public function getConstructor()
	{
		foreach ($this->getMethods() as $method) {
			if ($method->isConstructor()) {
				return $method;
			}
		}
		return NULL;
	}


	/**
	 * Returns the class destructor reflection.
	 *
	 * @return \ApiGen\TokenReflection\Reflection\ReflectionMethod|null
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
		foreach ($this->getMethods() as $method) {
			if ($name === $method->getName()) {
				return TRUE;
			}
		}
		return FALSE;
	}


	/**
	 * {@inheritdoc}
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
		throw new RuntimeException(sprintf('There is no method "%s".', $name));
	}


	/**
	 * {@inheritdoc}
	 */
	public function getMethods($filter = NULL)
	{
		$methods = $this->methods;
		foreach ($this->getTraitMethods() as $traitMethod) {
			if ( ! isset($methods[$traitMethod->getName()])) {
				$methods[$traitMethod->getName()] = $traitMethod;
			}
		}
		if (NULL !== $this->parentClassName) {
			foreach ($this->getParentClass()->getMethods(NULL) as $parentMethod) {
				if ( ! isset($methods[$parentMethod->getName()])) {
					$methods[$parentMethod->getName()] = $parentMethod;
				}
			}
		}
		foreach ($this->getOwnInterfaces() as $interface) {
			foreach ($interface->getMethods(NULL) as $parentMethod) {
				if ( ! isset($methods[$parentMethod->getName()])) {
					$methods[$parentMethod->getName()] = $parentMethod;
				}
			}
		}
		if (NULL !== $filter) {
			$methods = array_filter($methods, function (ReflectionMethodInterface $method) use ($filter) {
				return $method->is($filter);
			});
		}
		return array_values($methods);
	}


	/**
	 * {@inheritdoc}
	 */
	public function hasOwnMethod($name)
	{
		return isset($this->methods[$name]);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getOwnMethods($filter = NULL)
	{
		$methods = $this->methods;
		if (NULL !== $filter) {
			$methods = array_filter($methods, function (ReflectionMethod $method) use ($filter) {
				return $method->is($filter);
			});
		}
		return array_values($methods);
	}


	/**
	 * {@inheritdoc}
	 */
	public function hasTraitMethod($name)
	{
		if (isset($this->methods[$name])) {
			return FALSE;
		}
		foreach ($this->getOwnTraits() as $trait) {
			if ($trait->hasMethod($name)) {
				return TRUE;
			}
		}
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getTraitMethods($filter = NULL)
	{
		$methods = [];
		foreach ($this->getOwnTraits() as $trait) {
			$traitName = $trait->getName();
			foreach ($trait->getMethods(NULL) as $traitMethod) {
				$methodName = $traitMethod->getName();
				$imports = [];
				if (isset($this->traitImports[$traitName . '::' . $methodName])) {
					$imports = $this->traitImports[$traitName . '::' . $methodName];
				}
				if (isset($this->traitImports[$methodName])) {
					$imports = empty($imports) ? $this->traitImports[$methodName] : array_merge($imports, $this->traitImports[$methodName]);
				}
				foreach ($imports as $import) {
					if (NULL !== $import) {
						list($newName, $accessLevel) = $import;
						if ('' === $newName) {
							$newName = $methodName;
							$imports[] = NULL;
						}
						if ( ! isset($this->methods[$newName])) {
							if (isset($methods[$newName]) && ! $traitMethod->isAbstract()) {
								throw new RuntimeException(sprintf('Trait method "%s" was already imported.', $newName));
							}
							$methods[$newName] = $traitMethod->alias($this, $newName, $accessLevel);
						}
					}
				}
				if ( ! in_array(NULL, $imports)) {
					if ( ! isset($this->methods[$methodName])) {
						if (isset($methods[$methodName]) && ! $traitMethod->isAbstract()) {
							throw new RuntimeException(sprintf('Trait method "%s" was already imported.', $methodName));
						}
						$methods[$methodName] = $traitMethod->alias($this);
					}
				}
			}
		}
		if (NULL !== $filter) {
			$methods = array_filter($methods, function (ReflectionMethodInterface $method) use ($filter) {
				return (bool) ($method->getModifiers() & $filter);
			});
		}
		return array_values($methods);
	}


	/**
	 * {@inheritdoc}
	 */
	public function hasConstant($name)
	{
		if (isset($this->constants[$name])) {
			return TRUE;
		}
		foreach ($this->getConstantReflections() as $constant) {
			if ($name === $constant->getName()) {
				return TRUE;
			}
		}
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getConstant($name)
	{
		try {
			return $this->getConstantReflection($name)->getValue();
		} catch (Exception\BaseException $e) {
			return FALSE;
		}
	}


	/**
	 * {@inheritdoc}
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
		throw new RuntimeException(sprintf('There is no constant "%s".', $name));
	}


	/**
	 * {@inheritdoc}
	 */
	public function getConstants()
	{
		$constants = [];
		foreach ($this->getConstantReflections() as $constant) {
			$constants[$constant->getName()] = $constant->getValue();
		}
		return $constants;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getConstantReflections()
	{
		if (NULL === $this->parentClassName && empty($this->interfaces)) {
			return array_values($this->constants);
		} else {
			$reflections = array_values($this->constants);
			if (NULL !== $this->parentClassName) {
				$reflections = array_merge($reflections, $this->getParentClass()->getConstantReflections());
			}
			foreach ($this->getOwnInterfaces() as $interface) {
				$reflections = array_merge($reflections, $interface->getConstantReflections());
			}
			return $reflections;
		}
	}


	/**
	 * {@inheritdoc}
	 */
	public function hasOwnConstant($name)
	{
		return isset($this->constants[$name]);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getOwnConstants()
	{
		return array_map(function (ReflectionConstant $constant) {
			return $constant->getValue();
		}, $this->constants);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getOwnConstantReflections()
	{
		return array_values($this->constants);
	}


	/**
	 * {@inheritdoc}
	 */
	public function hasProperty($name)
	{
		foreach ($this->getProperties() as $property) {
			if ($name === $property->getName()) {
				return TRUE;
			}
		}
		return FALSE;
	}


	/**
	 * {@inheritdoc}
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
		throw new RuntimeException(sprintf('There is no property "%s".', $name, $this->name));
	}


	/**
	 * {@inheritdoc}
	 */
	public function getProperties($filter = NULL)
	{
		$properties = $this->properties;
		foreach ($this->getTraitProperties(NULL) as $traitProperty) {
			if ( ! isset($properties[$traitProperty->getName()])) {
				$properties[$traitProperty->getName()] = $traitProperty->alias($this);
			}
		}
		if (NULL !== $this->parentClassName) {
			foreach ($this->getParentClass()->getProperties(NULL) as $parentProperty) {
				if ( ! isset($properties[$parentProperty->getName()])) {
					$properties[$parentProperty->getName()] = $parentProperty;
				}
			}
		}
		if (NULL !== $filter) {
			$properties = array_filter($properties, function (ReflectionPropertyInterface $property) use ($filter) {
				return (bool) ($property->getModifiers() & $filter);
			});
		}
		return array_values($properties);
	}


	/**
	 * {@inheritdoc}
	 */
	public function hasOwnProperty($name)
	{
		return isset($this->properties[$name]);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getOwnProperties($filter = NULL)
	{
		$properties = $this->properties;
		if (NULL !== $filter) {
			$properties = array_filter($properties, function (ReflectionProperty $property) use ($filter) {
				return (bool) ($property->getModifiers() & $filter);
			});
		}
		return array_values($properties);
	}


	/**
	 * {@inheritdoc}
	 */
	public function hasTraitProperty($name)
	{
		if (isset($this->properties[$name])) {
			return FALSE;
		}
		foreach ($this->getOwnTraits() as $trait) {
			if ($trait->hasProperty($name)) {
				return TRUE;
			}
		}
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getTraitProperties($filter = NULL)
	{
		$properties = [];
		foreach ($this->getOwnTraits() as $trait) {
			foreach ($trait->getProperties(NULL) as $traitProperty) {
				if ( ! isset($this->properties[$traitProperty->getName()]) && !isset($properties[$traitProperty->getName()])) {
					$properties[$traitProperty->getName()] = $traitProperty->alias($this);
				}
			}
		}
		if (NULL !== $filter) {
			$properties = array_filter($properties, function (ReflectionPropertyInterface $property) use ($filter) {
				return (bool) ($property->getModifiers() & $filter);
			});
		}
		return array_values($properties);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDefaultProperties()
	{
		static $accessLevels = [InternalReflectionProperty::IS_PUBLIC, InternalReflectionProperty::IS_PROTECTED, InternalReflectionProperty::IS_PRIVATE];
		$defaults = [];
		$properties = $this->getProperties();
		foreach ([TRUE, FALSE] as $static) {
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
	 * {@inheritdoc}
	 */
	public function getStaticProperties()
	{
		$defaults = [];
		foreach ($this->getProperties(InternalReflectionProperty::IS_STATIC) as $property) {
			if ($property instanceof ReflectionProperty) {
				$defaults[$property->getName()] = $property->getDefaultValue();
			}
		}
		return $defaults;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getStaticPropertyValue($name, $default = NULL)
	{
		if ($this->hasProperty($name) && ($property = $this->getProperty($name)) && $property->isStatic()) {
			if ( ! $property->isPublic() && !$property->isAccessible()) {
				throw new RuntimeException(sprintf('Static property "%s" is not accessible.', $name));
			}
			return $property->getDefaultValue();
		}
		throw new RuntimeException(sprintf('There is no static property "%s".', $name));
	}


	/**
	 * {@inheritdoc}
	 */
	public function getTraits()
	{
		$parentClass = $this->getParentClass();
		$names = $parentClass ? array_keys($parentClass->getTraits()) : [];
		foreach ($this->traits as $traitName) {
			$names[] = $traitName;
		}
		$traitNames =  array_unique($names);
		if (empty($traitNames)) {
			return [];
		}
		return array_combine($traitNames, array_map(function ($traitName) {
			return $this->storage->getClass($traitName);
		}, $traitNames));
	}


	/**
	 * {@inheritdoc}
	 */
	public function getOwnTraits()
	{
		if (empty($this->traits)) {
			return [];
		}
		return array_combine($this->traits, array_map(function ($traitName) {
			return $this->storage->getClass($traitName);
		}, $this->traits));
	}


	/**
	 * {@inheritdoc}
	 */
	public function getTraitAliases()
	{
		return $this->traitAliases;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isTrait()
	{
		return self::IS_TRAIT === $this->type;
	}


	/**
	 * {@inheritdoc}
	 */
	public function usesTrait($trait)
	{
		if (is_object($trait)) {
			if ( ! $trait instanceof InternalReflectionClass && !$trait instanceof ReflectionClassInterface) {
				throw new RuntimeException(sprintf('Parameter must be a string or an instance of trait reflection, "%s" provided.', get_class($trait)));
			}
			$traitName = $trait->getName();
			if ( ! $trait->isTrait()) {
				throw new RuntimeException(sprintf('"%s" is not a trait.', $traitName));
			}
		} else {
			$reflection = $this->storage->getClass($trait);
			if ( ! $reflection->isTrait()) {
				throw new RuntimeException(sprintf('"%s" is not a trait.', $trait));
			}
			$traitName = $trait;
		}

		$traitNames = array_keys($this->traits);
		return in_array($traitName, $traitNames);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDirectSubclasses()
	{
		return array_filter($this->storage->getClasses(), function (ReflectionClass $class) {
			if ( ! $class->isSubclassOf($this->name)) {
				return FALSE;
			}
			return $class->getParentClassName() === NULL || ! $class->getParentClass()->isSubClassOf($this->name);
		});
	}


	/**
	 * {@inheritdoc}
	 */
	public function getIndirectSubclasses()
	{
		return array_filter($this->storage->getClasses(), function (ReflectionClass $class) {
			if ( ! $class->isSubclassOf($this->name)) {
				return FALSE;
			}
			return $class->getParentClassName() !== NULL && $class->getParentClass()->isSubClassOf($this->name);
		});
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDirectImplementers()
	{
		if ( ! $this->isInterface()) {
			return [];
		}
		return array_filter($this->storage->getClasses(), function (ReflectionClass $class) {
			if ($class->isInterface() || !$class->implementsInterface($this->name)) {
				return FALSE;
			}
			return NULL === $class->getParentClassName() || !$class->getParentClass()->implementsInterface($this->name);
		});
	}


	/**
	 * {@inheritdoc}
	 */
	public function getIndirectImplementers()
	{
		if ( ! $this->isInterface()) {
			return [];
		}
		return array_filter($this->storage->getClasses(), function (ReflectionClass $class) {
			if ($class->isInterface() || !$class->implementsInterface($this->name)) {
				return FALSE;
			}
			return NULL !== $class->getParentClassName() && $class->getParentClass()->implementsInterface($this->name);
		});
	}


	/**
	 * {@inheritdoc}
	 */
	public function isInstance($object)
	{
		if ( ! is_object($object)) {
			throw new RuntimeException(sprintf('Parameter must be an object, "%s" provided.', gettype($object)));
		}
		return $this->name === get_class($object) || is_subclass_of($object, $this->getName());
	}


	/**
	 * {@inheritdoc}
	 */
	public function setStaticPropertyValue($name, $value)
	{
		if ($this->hasProperty($name) && ($property = $this->getProperty($name)) && $property->isStatic()) {
			if ( ! $property->isPublic() && !$property->isAccessible()) {
				throw new RuntimeException(sprintf('Static property "%s" is not accessible.', $name));
			}
			$property->setDefaultValue($value);
			return;
		}
		throw new RuntimeException(sprintf('There is no static property "%s".', $name));
	}


	/**
	 * {@inheritdoc}
	 */
	public function isComplete()
	{
		if ( ! $this->definitionComplete) {
			if ($this->parentClassName !== NULL && ! $this->getParentClass()->isComplete()) {
				return FALSE;
			}
			foreach ($this->getOwnInterfaces() as $interface) {
				if ( ! $interface->isComplete()) {
					return FALSE;
				}
			}
			$this->definitionComplete = TRUE;
		}
		return $this->definitionComplete;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getNamespaceAliases()
	{
		return $this->aliases;
	}


	protected function processParent(ReflectionInterface $parent, StreamBase $tokenStream)
	{
		if ( ! $parent instanceof ReflectionFileNamespace) {
			throw new ParseException($this, $tokenStream, sprintf('Invalid parent reflection provided: "%s".', get_class($parent)));
		}
		$this->namespaceName = $parent->getName();
		$this->aliases = $parent->getNamespaceAliases();
	}


	protected function parse(StreamBase $tokenStream, ReflectionInterface $parent)
	{
		$classParser = new ClassParser($tokenStream, $this, $parent);
		list ($this->modifiers, $this->type) = $classParser->parseModifiers();

		$this->fileName = $tokenStream->getFileName();

		$this->parseName($tokenStream);

		$this->parseParent($tokenStream, $parent);

		$this->parseInterfaces($tokenStream, $parent);
	}


	protected function parseName(StreamBase $tokenStream)
	{
		if ( ! $tokenStream->is(T_STRING)) {
			throw new ParseException('Unexpected token found.');
		}
		if ($this->namespaceName === ReflectionNamespace::NO_NAMESPACE_NAME) {
			$this->name = $tokenStream->getTokenValue();
		} else {
			$this->name = $this->namespaceName . '\\' . $tokenStream->getTokenValue();
		}
		$tokenStream->skipWhitespaces(TRUE);
	}


	private function parseParent(StreamBase $tokenStream, ReflectionElement $parent = NULL)
	{
		if ( ! $tokenStream->is(T_EXTENDS)) {
			return $this;
		}
		while (TRUE) {
			$tokenStream->skipWhitespaces(TRUE);
			$parentClassName = '';
			while (TRUE) {
				switch ($tokenStream->getType()) {
					case T_STRING:
					case T_NS_SEPARATOR:
						$parentClassName .= $tokenStream->getTokenValue();
						break;
					default:
						break 2;
				}
				$tokenStream->skipWhitespaces(TRUE);
			}
			$parentClassName = Resolver::resolveClassFQN($parentClassName, $this->aliases, $this->namespaceName);
			if ($this->isInterface()) {
				$this->interfaces[] = $parentClassName;
				if ($tokenStream->getTokenValue() === ',') {
					continue;
				}
			} else {
				$this->parentClassName = $parentClassName;
			}
			break;
		}
	}


	private function parseInterfaces(StreamBase $tokenStream, ReflectionElement $parent = NULL)
	{
		if ( ! $tokenStream->is(T_IMPLEMENTS)) {
			return $this;
		}
		if ($this->isInterface()) {
			throw new ParseException('Interfaces cannot implement interfaces.');
		}
		while (TRUE) {
			$interfaceName = '';
			$tokenStream->skipWhitespaces(TRUE);
			while (TRUE) {
				switch ($tokenStream->getType()) {
					case T_STRING:
					case T_NS_SEPARATOR:
						$interfaceName .= $tokenStream->getTokenValue();
						break;
					default:
						break 2;
				}
				$tokenStream->skipWhitespaces(TRUE);
			}
			$this->interfaces[] = Resolver::resolveClassFQN($interfaceName, $this->aliases, $this->namespaceName);
			$type = $tokenStream->getType();
			if ($type === '{') {
				break;

			} elseif ($type !== ',') {
				throw new ParseException('Unexpected token found, expected "{" or ";".');
			}
		}
	}


	protected function parseChildren(StreamBase $tokenStream, ReflectionInterface $parent)
	{
		while (TRUE) {
			switch ($type = $tokenStream->getType()) {
				case NULL:
					break 2;
				case T_COMMENT:
				case T_DOC_COMMENT:
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
					static $searching = [T_VARIABLE => TRUE, T_FUNCTION => TRUE];
					if (T_VAR !== $tokenStream->getType()) {
						$position = $tokenStream->key();
						while (NULL !== ($type = $tokenStream->getType($position)) && !isset($searching[$type])) {
							$position++;
						}
					}
					if (T_VARIABLE === $type || T_VAR === $type) {
						$property = new ReflectionProperty($tokenStream, $this->storage, $this);
						$this->properties[$property->getName()] = $property;
						$tokenStream->next();
						break;
					}
				// Break missing on purpose
				case T_FINAL:
				case T_ABSTRACT:
				case T_FUNCTION:
					$method = new ReflectionMethod($tokenStream, $this->storage, $this);
					$this->methods[$method->getName()] = $method;
					$tokenStream->next();
					break;
				case T_CONST:
					$tokenStream->skipWhitespaces(TRUE);
					while ($tokenStream->is(T_STRING)) {
						$constant = new ReflectionConstant($tokenStream, $this->getStorage(), $this);
						$this->constants[$constant->getName()] = $constant;
						if ($tokenStream->is(',')) {
							$tokenStream->skipWhitespaces(TRUE);
						} else {
							$tokenStream->next();
						}
					}
					break;
				case T_USE:
					$tokenStream->skipWhitespaces(TRUE);
					while (TRUE) {
						$traitName = '';
						$type = $tokenStream->getType();
						while (T_STRING === $type || T_NS_SEPARATOR === $type) {
							$traitName .= $tokenStream->getTokenValue();
							$type = $tokenStream->skipWhitespaces(TRUE)->getType();
						}
						if ('' === trim($traitName, '\\')) {
							throw new ParseException('An empty trait name found.');
						}
						$this->traits[] = Resolver::resolveClassFQN($traitName, $this->aliases, $this->namespaceName);
						if (';' === $type) {
							// End of "use"
							$tokenStream->skipWhitespaces();
							break;
						} elseif (',' === $type) {
							// Next trait name follows
							$tokenStream->skipWhitespaces();
							continue;
						} elseif ('{' !== $type) {
							// Unexpected token
							throw new ParseException('Unexpected token found: "%s".');
						}
						// Aliases definition
						$type = $tokenStream->skipWhitespaces(TRUE)->getType();
						while (TRUE) {
							if ('}' === $type) {
								$tokenStream->skipWhitespaces();
								break 2;
							}
							$leftSide = '';
							$rightSide = ['', NULL];
							$alias = TRUE;
							while (T_STRING === $type || T_NS_SEPARATOR === $type || T_DOUBLE_COLON === $type) {
								$leftSide .= $tokenStream->getTokenValue();
								$type = $tokenStream->skipWhitespaces(TRUE)->getType();
							}
							if (T_INSTEADOF === $type) {
								$alias = FALSE;
							} elseif (T_AS !== $type) {
								throw new ParseException('Unexpected token found.');
							}
							$type = $tokenStream->skipWhitespaces(TRUE)->getType();
							if (T_PUBLIC === $type || T_PROTECTED === $type || T_PRIVATE === $type) {
								if ( ! $alias) {
									throw new ParseException('Unexpected token found.');
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
									default:
										break;
								}
								$rightSide[1] = $type;
								$type = $tokenStream->skipWhitespaces(TRUE)->getType();
							}
							while (T_STRING === $type || (T_NS_SEPARATOR === $type && !$alias)) {
								$rightSide[0] .= $tokenStream->getTokenValue();
								$type = $tokenStream->skipWhitespaces(TRUE)->getType();
							}
							if (empty($leftSide)) {
								throw new ParseException('An empty method name was found.');
							}
							if ($alias) {
								// Alias
								if ($pos = strpos($leftSide, '::')) {
									$methodName = substr($leftSide, $pos + 2);
									$className = Resolver::resolveClassFQN(substr($leftSide, 0, $pos), $this->aliases, $this->namespaceName);
									$leftSide = $className . '::' . $methodName;
									$this->traitAliases[$rightSide[0]] = $leftSide;
								} else {
									$this->traitAliases[$rightSide[0]] = '(null)::' . $leftSide;
								}
								$this->traitImports[$leftSide][] = $rightSide;

							} else {
								// Insteadof
								if ($pos = strpos($leftSide, '::')) {
									$methodName = substr($leftSide, $pos + 2);

									} else {
									throw new ParseException('A T_DOUBLE_COLON has to be present when using T_INSTEADOF.');
								}
								$this->traitImports[Resolver::resolveClassFQN($rightSide[0], $this->aliases, $this->namespaceName) . '::' . $methodName][] = NULL;
							}
							if ($type === ',') {
								$tokenStream->skipWhitespaces(TRUE);
								continue;

							} elseif ($type !== ';') {
								throw new ParseException('Unexpected token found.');
							}
							$type = $tokenStream->skipWhitespaces()->getType();
						}
					}
					break;
				default:
					$tokenStream->next();
					break;
			}
		}
	}

}
