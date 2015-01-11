<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Invalid;

use ApiGen\TokenReflection;
use ApiGen\TokenReflection\Broker\Broker;
use ApiGen\TokenReflection\Exception;
use ApiGen\TokenReflection\Exception\RuntimeException;
use ApiGen\TokenReflection\IReflectionClass;
use ApiGen\TokenReflection\Php;
use ApiGen\TokenReflection\ReflectionBase;
use ApiGen\TokenReflection\ReflectionFile;
use ReflectionClass as InternalReflectionClass;


/**
 * The reflected class is not unique.
 */
class ReflectionClass extends ReflectionElement implements IReflectionClass
{

	/**
	 * Class name (FQN).
	 *
	 * @var string
	 */
	private $name;

	/**
	 * Original definition file name.
	 *
	 * @var string
	 */
	private $fileName;

	/**
	 * @var Broker
	 */
	private $broker;


	/**
	 * @param string $className
	 * @param string $fileName Original definition file name
	 * @param Broker $broker
	 */
	public function __construct($className, $fileName, Broker $broker)
	{
		$this->name = ltrim($className, '\\');
		$this->fileName = $fileName;
		$this->broker = $broker;
	}


	/**
	 * Returns the name (FQN).
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * Returns an element pretty (docblock compatible) name.
	 *
	 * @return string
	 */
	public function getPrettyName()
	{
		return $this->name;
	}


	/**
	 * Returns the unqualified name (UQN).
	 *
	 * @return string
	 */
	public function getShortName()
	{
		$pos = strrpos($this->name, '\\');
		return FALSE === $pos ? $this->name : substr($this->name, $pos + 1);
	}


	/**
	 * Returns the namespace name.
	 *
	 * @return string
	 */
	public function getNamespaceName()
	{
		$pos = strrpos($this->name, '\\');
		return FALSE === $pos ? '' : substr($this->name, 0, $pos);
	}


	/**
	 * Returns if the class is defined within a namespace.
	 *
	 * @return bool
	 */
	public function inNamespace()
	{
		return FALSE !== strrpos($this->name, '\\');
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
	 * Returns the PHP extension reflection.
	 *
	 * @return null
	 */
	public function getExtension()
	{
		return NULL;
	}


	/**
	 * Returns the PHP extension name.
	 *
	 * @return bool
	 */
	public function getExtensionName()
	{
		return FALSE;
	}


	/**
	 * Returns the file name the reflection object is defined in.
	 *
	 * @return null
	 */
	public function getFileName()
	{
		return $this->fileName;
	}


	/**
	 * @return ReflectionFile
	 * @throws RuntimeException If the file is not stored inside the broker
	 */
	public function getFileReflection()
	{
		throw new Exception\BrokerException($this->getBroker(), sprintf('Class was not parsed from a file', $this->getName()), Exception\BrokerException::UNSUPPORTED);
	}


	/**
	 * Returns the definition start line number in the file.
	 *
	 * @return null
	 */
	public function getStartLine()
	{
		return NULL;
	}


	/**
	 * Returns the definition end line number in the file.
	 *
	 * @return null
	 */
	public function getEndLine()
	{
		return NULL;
	}


	/**
	 * Returns the appropriate docblock definition.
	 *
	 * @return bool
	 */
	public function getDocComment()
	{
		return FALSE;
	}


	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasAnnotation($name)
	{
		return FALSE;
	}


	/**
	 * @param string $name
	 * @return null
	 */
	public function getAnnotation($name)
	{
		return NULL;
	}


	/**
	 * @return array
	 */
	public function getAnnotations()
	{
		return [];
	}


	/**
	 * @return int
	 */
	public function getModifiers()
	{
		return 0;
	}


	/**
	 * @return bool
	 */
	public function isAbstract()
	{
		return FALSE;
	}


	/**
	 * @return bool
	 */
	public function isFinal()
	{
		return FALSE;
	}


	/**
	 * @return bool
	 */
	public function isInterface()
	{
		return FALSE;
	}


	/**
	 * @return bool
	 */
	public function isException()
	{
		return FALSE;
	}


	/**
	 * @return bool
	 */
	public function isInstantiable()
	{
		return FALSE;
	}


	/**
	 * @return array
	 */
	public function getTraits()
	{
		return [];
	}


	/**
	 * @return array
	 */
	public function getOwnTraits()
	{
		return [];
	}


	/**
	 * @return array
	 */
	public function getTraitNames()
	{
		return [];
	}


	/**
	 * @return array
	 */
	public function getOwnTraitNames()
	{
		return [];
	}


	/**
	 * @return array
	 */
	public function getTraitAliases()
	{
		return [];
	}


	/**
	 * @return bool
	 */
	public function isTrait()
	{
		return FALSE;
	}


	/**
	 * @param \ReflectionClass|IReflectionClass|string $trait Trait reflection or name
	 * @return bool
	 */
	public function usesTrait($trait)
	{
		return FALSE;
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
		return FALSE;
	}


	/**
	 * Returns if the class is iterateable.
	 *
	 * Returns true if the class implements the Traversable interface.
	 *
	 * @return bool
	 */
	public function isIterateable()
	{
		return FALSE;
	}


	/**
	 * Returns if the reflection object is internal.
	 *
	 * @return bool
	 */
	public function isInternal()
	{
		return FALSE;
	}


	/**
	 * Returns if the reflection object is user defined.
	 *
	 * @return bool
	 */
	public function isUserDefined()
	{
		return TRUE;
	}


	/**
	 * Returns if the current reflection comes from a tokenized source.
	 *
	 * @return bool
	 */
	public function isTokenized()
	{
		return TRUE;
	}


	/**
	 * Returns if the current class is a subclass of the given class.
	 *
	 * @param string|object $class Class name or reflection object
	 * @return bool
	 */
	public function isSubclassOf($class)
	{
		return FALSE;
	}


	/**
	 * Returns the parent class reflection.
	 *
	 * @return null
	 */
	public function getParentClass()
	{
		return FALSE;
	}


	/**
	 * Returns the parent classes reflections.
	 *
	 * @return array
	 */
	public function getParentClasses()
	{
		return [];
	}


	/**
	 * Returns the parent classes names.
	 *
	 * @return array
	 */
	public function getParentClassNameList()
	{
		return [];
	}


	/**
	 * Returns the parent class reflection.
	 *
	 * @return null
	 */
	public function getParentClassName()
	{
		return NULL;
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
			if ( ! $interface instanceof IReflectionClass) {
				throw new RuntimeException(sprintf('Parameter must be a string or an instance of class reflection, "%s" provided.', get_class($interface)), RuntimeException::INVALID_ARGUMENT, $this);
			}
			$interfaceName = $interface->getName();
			if ( ! $interface->isInterface()) {
				throw new RuntimeException(sprintf('"%s" is not an interface.', $interfaceName), RuntimeException::INVALID_ARGUMENT, $this);
			}
		}
		// Only validation, always returns false
		return FALSE;
	}


	/**
	 * Returns interface reflections.
	 *
	 * @return array
	 */
	public function getInterfaces()
	{
		return [];
	}


	/**
	 * Returns interface names.
	 *
	 * @return array
	 */
	public function getInterfaceNames()
	{
		return [];
	}


	/**
	 * Returns interfaces implemented by this class, not its parents.
	 *
	 * @return array
	 */
	public function getOwnInterfaces()
	{
		return [];
	}


	/**
	 * Returns names of interfaces implemented by this class, not its parents.
	 *
	 * @return array
	 */
	public function getOwnInterfaceNames()
	{
		return [];
	}


	/**
	 * Returns the class constructor reflection.
	 *
	 * @return null
	 */
	public function getConstructor()
	{
		return NULL;
	}


	/**
	 * Returns the class desctructor reflection.
	 *
	 * @return null
	 */
	public function getDestructor()
	{
		return NULL;
	}


	/**
	 * Returns if the class implements the given method.
	 *
	 * @param string $name Method name
	 * @return bool
	 */
	public function hasMethod($name)
	{
		return FALSE;
	}


	/**
	 * Returns a method reflection.
	 *
	 * @param string $name Method name
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If the requested method does not exist.
	 */
	public function getMethod($name)
	{
		throw new RuntimeException(sprintf('There is no method "%s".', $name), RuntimeException::DOES_NOT_EXIST, $this);
	}


	/**
	 * Returns method reflections.
	 *
	 * @param int $filter Methods filter
	 * @return array
	 */
	public function getMethods($filter = NULL)
	{
		return [];
	}


	/**
	 * Returns if the class implements (and not its parents) the given method.
	 *
	 * @param string $name Method name
	 * @return bool
	 */
	public function hasOwnMethod($name)
	{
		return FALSE;
	}


	/**
	 * Returns methods declared by this class, not its parents.
	 *
	 * @param int $filter Methods filter
	 * @return array
	 */
	public function getOwnMethods($filter = NULL)
	{
		return [];
	}


	/**
	 * Returns if the class imports the given method from traits.
	 *
	 * @param string $name Method name
	 * @return bool
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
	 */
	public function getTraitMethods($filter = NULL)
	{
		return [];
	}


	/**
	 * Returns if the class defines the given constant.
	 *
	 * @param string $name Constant name.
	 * @return bool
	 */
	public function hasConstant($name)
	{
		return FALSE;
	}


	/**
	 * Returns a constant value.
	 *
	 * @param string $name Constant name
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If the requested constant does not exist.
	 */
	public function getConstant($name)
	{
		throw new RuntimeException(sprintf('There is no constant "%s".', $name), RuntimeException::DOES_NOT_EXIST, $this);
	}


	/**
	 * Returns a constant reflection.
	 *
	 * @param string $name Constant name
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If the requested constant does not exist.
	 */
	public function getConstantReflection($name)
	{
		throw new RuntimeException(sprintf('There is no constant "%s".', $name), RuntimeException::DOES_NOT_EXIST, $this);
	}


	/**
	 * Returns an array of constant values.
	 *
	 * @return array
	 */
	public function getConstants()
	{
		return [];
	}


	/**
	 * Returns an array of constant reflections.
	 *
	 * @return array
	 */
	public function getConstantReflections()
	{
		return [];
	}


	/**
	 * Returns if the class (and not its parents) defines the given constant.
	 *
	 * @param string $name Constant name.
	 * @return bool
	 */
	public function hasOwnConstant($name)
	{
		return FALSE;
	}


	/**
	 * Returns constants declared by this class, not its parents.
	 *
	 * @return array
	 */
	public function getOwnConstants()
	{
		return [];
	}


	/**
	 * Returns an array of constant reflections defined by this class not its parents.
	 *
	 * @return array
	 */
	public function getOwnConstantReflections()
	{
		return [];
	}


	/**
	 * Returns default properties.
	 *
	 * @return array
	 */
	public function getDefaultProperties()
	{
		return [];
	}


	/**
	 * Returns if the class implements the given property.
	 *
	 * @param string $name Property name
	 * @return bool
	 */
	public function hasProperty($name)
	{
		return FALSE;
	}


	/**
	 * Returns class properties.
	 *
	 * @param int $filter Property types
	 * @return array
	 */
	public function getProperties($filter = NULL)
	{
		return [];
	}


	/**
	 * Return a property reflections.
	 *
	 * @param string $name Property name
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If the requested property does not exist.
	 */
	public function getProperty($name)
	{
		throw new RuntimeException(sprintf('There is no property "%s".', $name), RuntimeException::DOES_NOT_EXIST, $this);
	}


	/**
	 * Returns if the class (and not its parents) implements the given property.
	 *
	 * @param string $name Property name
	 * @return bool
	 */
	public function hasOwnProperty($name)
	{
		return FALSE;
	}


	/**
	 * Returns properties declared by this class, not its parents.
	 *
	 * @param int $filter Properties filter
	 * @return array
	 */
	public function getOwnProperties($filter = NULL)
	{
		return [];
	}


	/**
	 * Returns if the class imports the given property from traits.
	 *
	 * @param string $name Property name
	 * @return bool
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
		return [];
	}


	/**
	 * Returns a value of a static property.
	 *
	 * @param string $name Property name
	 * @param mixed $default Default value
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If the requested static property does not exist.
	 */
	public function getStaticPropertyValue($name, $default = NULL)
	{
		throw new RuntimeException(sprintf('There is no static property "%s".', $name), RuntimeException::DOES_NOT_EXIST, $this);
	}


	/**
	 * Returns reflections of direct subclasses.
	 *
	 * @return array
	 */
	public function getDirectSubclasses()
	{
		return [];
	}


	/**
	 * Returns names of direct subclasses.
	 *
	 * @return array
	 */
	public function getDirectSubclassNames()
	{
		return [];
	}


	/**
	 * Returns reflections of indirect subclasses.
	 *
	 * @return array
	 */
	public function getIndirectSubclasses()
	{
		return [];
	}


	/**
	 * Returns names of indirect subclasses.
	 *
	 * @return array
	 */
	public function getIndirectSubclassNames()
	{
		return [];
	}


	/**
	 * Returns reflections of classes directly implementing this interface.
	 *
	 * @return array
	 */
	public function getDirectImplementers()
	{
		return [];
	}


	/**
	 * Returns names of classes directly implementing this interface.
	 *
	 * @return array
	 */
	public function getDirectImplementerNames()
	{
		return [];
	}


	/**
	 * Returns reflections of classes indirectly implementing this interface.
	 *
	 * @return array
	 */
	public function getIndirectImplementers()
	{
		return [];
	}


	/**
	 * Returns names of classes indirectly implementing this interface.
	 *
	 * @return array
	 */
	public function getIndirectImplementerNames()
	{
		return [];
	}


	/**
	 * Returns if the given object is an instance of this class.
	 *
	 * @param object $object Instance
	 * @return bool
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If the provided argument is not an object.
	 */
	public function isInstance($object)
	{
		if ( ! is_object($object)) {
			throw new RuntimeException(sprintf('Parameter must be a class instance, "%s" provided.', gettype($object)), RuntimeException::INVALID_ARGUMENT, $this);
		}
		return $this->name === get_class($object) || is_subclass_of($object, $this->name);
	}


	/**
	 * Creates a new class instance without using a constructor.
	 *
	 * @return object
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If the class inherits from an internal class.
	 */
	public function newInstanceWithoutConstructor()
	{
		if ( ! class_exists($this->name, TRUE)) {
			throw new RuntimeException('Could not create an instance; class does not exist.', RuntimeException::DOES_NOT_EXIST, $this);
		}
		$reflection = new Php\ReflectionClass($this->name, $this->getBroker());
		return $reflection->newInstanceWithoutConstructor();
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
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If the required class does not exist.
	 */
	public function newInstanceArgs(array $args = [])
	{
		if ( ! class_exists($this->name, TRUE)) {
			throw new RuntimeException('Could not create an instance of class; class does not exist.', RuntimeException::DOES_NOT_EXIST, $this);
		}
		$reflection = new InternalReflectionClass($this->name);
		return $reflection->newInstanceArgs($args);
	}


	/**
	 * Sets a static property value.
	 *
	 * @param string $name Property name
	 * @param mixed $value Property value
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If the requested static property does not exist.
	 */
	public function setStaticPropertyValue($name, $value)
	{
		throw new RuntimeException(sprintf('There is no static property "%s".', $name), RuntimeException::DOES_NOT_EXIST, $this);
	}


	/**
	 * Returns the string representation of the reflection object.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return sprintf(
			"Class|Interface [ <user> class|interface %s ] {\n  %s%s%s%s%s\n}\n",
			$this->getName(),
			"\n\n  - Constants [0] {\n  }",
			"\n\n  - Static properties [0] {\n  }",
			"\n\n  - Static methods [0] {\n  }",
			"\n\n  - Properties [0] {\n  }",
			"\n\n  - Methods [0] {\n  }"
		);
	}


	/**
	 * Exports a reflected object.
	 *
	 * @param ApiGen\TokenReflection\Broker $broker Broker instance
	 * @param string|object $className Class name or class instance
	 * @param bool $return Return the export instead of outputting it
	 * @return string|null
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If requested parameter doesn't exist.
	 */
	public static function export(Broker $broker, $className, $return = FALSE)
	{
		TokenReflection\ReflectionClass::export($broker, $className, $return);
	}


	/**
	 * Outputs the reflection subject source code.
	 *
	 * @return string
	 */
	public function getSource()
	{
		return '';
	}


	/**
	 * Returns the start position in the file token stream.
	 *
	 * @return int
	 */
	public function getStartPosition()
	{
		return -1;
	}


	/**
	 * Returns the end position in the file token stream.
	 *
	 * @return int
	 */
	public function getEndPosition()
	{
		return -1;
	}


	/**
	 * Invalid classes are always complete.
	 *
	 * @return bool
	 */
	public function isComplete()
	{
		return TRUE;
	}


	/**
	 * @return bool
	 */
	public function isValid()
	{
		return FALSE;
	}


	/**
	 * @return bool
	 */
	public function isDeprecated()
	{
		return FALSE;
	}


	/**
	 * @return Broker
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
		return ReflectionBase::get($this, $key);
	}


	/**
	 * Magic __isset method.
	 *
	 * @param string $key Variable name
	 * @return bool
	 */
	final public function __isset($key)
	{
		return ReflectionBase::exists($this, $key);
	}

}
