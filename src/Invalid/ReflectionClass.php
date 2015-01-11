<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Invalid;

use ApiGen\TokenReflection;
use ApiGen\TokenReflection\Broker;
use ApiGen\TokenReflection\IReflectionClass;
use ApiGen\TokenReflection\ReflectionBase;
use ReflectionClass as InternalReflectionClass;
use ApiGen\TokenReflection\Exception;


/**
 * Invalid class reflection.
 *
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
	 * Reflection broker.
	 *
	 * @var ApiGen\TokenReflection\Broker
	 */
	private $broker;


	/**
	 * Constructor.
	 *
	 * @param string $className Class name
	 * @param string $fileName Original definiton file name
	 * @param ApiGen\TokenReflection\Broker $broker Reflection broker
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
	 * @return boolean
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
	 * @return boolean
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
	 * Returns a file reflection.
	 *
	 * @return ApiGen\TokenReflection\ReflectionFile
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If the file is not stored inside the broker
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
	 * @return boolean
	 */
	public function getDocComment()
	{
		return FALSE;
	}


	/**
	 * Checks if there is a particular annotation.
	 *
	 * @param string $name Annotation name
	 * @return boolean
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
	 * Returns modifiers.
	 *
	 * @return integer
	 */
	public function getModifiers()
	{
		return 0;
	}


	/**
	 * Returns if the class is abstract.
	 *
	 * @return boolean
	 */
	public function isAbstract()
	{
		return FALSE;
	}


	/**
	 * Returns if the class is final.
	 *
	 * @return boolean
	 */
	public function isFinal()
	{
		return FALSE;
	}


	/**
	 * Returns if the class is an interface.
	 *
	 * @return boolean
	 */
	public function isInterface()
	{
		return FALSE;
	}


	/**
	 * Returns if the class is an exception or its descendant.
	 *
	 * @return boolean
	 */
	public function isException()
	{
		return FALSE;
	}


	/**
	 * Returns if it is possible to create an instance of this class.
	 *
	 * @return boolean
	 */
	public function isInstantiable()
	{
		return FALSE;
	}


	/**
	 * Returns traits used by this class.
	 *
	 * @return array
	 */
	public function getTraits()
	{
		return [];
	}


	/**
	 * Returns traits used by this class and not its parents.
	 *
	 * @return array
	 */
	public function getOwnTraits()
	{
		return [];
	}


	/**
	 * Returns names of used traits.
	 *
	 * @return array
	 */
	public function getTraitNames()
	{
		return [];
	}


	/**
	 * Returns traits used by this class and not its parents.
	 *
	 * @return array
	 */
	public function getOwnTraitNames()
	{
		return [];
	}


	/**
	 * Returns method aliases from traits.
	 *
	 * @return array
	 */
	public function getTraitAliases()
	{
		return [];
	}


	/**
	 * Returns if the class is a trait.
	 *
	 * @return boolean
	 */
	public function isTrait()
	{
		return FALSE;
	}


	/**
	 * Returns if the class uses a particular trait.
	 *
	 * @param \ReflectionClass|\TokenReflection\IReflectionClass|string $trait Trait reflection or name
	 * @return boolean
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
	 * @return boolean
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
	 * @return boolean
	 */
	public function isIterateable()
	{
		return FALSE;
	}


	/**
	 * Returns if the reflection object is internal.
	 *
	 * @return boolean
	 */
	public function isInternal()
	{
		return FALSE;
	}


	/**
	 * Returns if the reflection object is user defined.
	 *
	 * @return boolean
	 */
	public function isUserDefined()
	{
		return TRUE;
	}


	/**
	 * Returns if the current reflection comes from a tokenized source.
	 *
	 * @return boolean
	 */
	public function isTokenized()
	{
		return TRUE;
	}


	/**
	 * Returns if the current class is a subclass of the given class.
	 *
	 * @param string|object $class Class name or reflection object
	 * @return boolean
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
	 * @return boolean
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If the provided parameter is not an interface.
	 */
	public function implementsInterface($interface)
	{
		if (is_object($interface)) {
			if (!$interface instanceof IReflectionClass) {
				throw new Exception\RuntimeException(sprintf('Parameter must be a string or an instance of class reflection, "%s" provided.', get_class($interface)), Exception\RuntimeException::INVALID_ARGUMENT, $this);
			}
			$interfaceName = $interface->getName();
			if (!$interface->isInterface()) {
				throw new Exception\RuntimeException(sprintf('"%s" is not an interface.', $interfaceName), Exception\RuntimeException::INVALID_ARGUMENT, $this);
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
	 * @return boolean
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
		throw new Exception\RuntimeException(sprintf('There is no method "%s".', $name), Exception\RuntimeException::DOES_NOT_EXIST, $this);
	}


	/**
	 * Returns method reflections.
	 *
	 * @param integer $filter Methods filter
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
	 * @return boolean
	 */
	public function hasOwnMethod($name)
	{
		return FALSE;
	}


	/**
	 * Returns methods declared by this class, not its parents.
	 *
	 * @param integer $filter Methods filter
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
	 * @return boolean
	 */
	public function hasTraitMethod($name)
	{
		return FALSE;
	}


	/**
	 * Returns method reflections imported from traits.
	 *
	 * @param integer $filter Methods filter
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
	 * @return boolean
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
		throw new Exception\RuntimeException(sprintf('There is no constant "%s".', $name), Exception\RuntimeException::DOES_NOT_EXIST, $this);
	}


	/**
	 * Returns a constant reflection.
	 *
	 * @param string $name Constant name
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If the requested constant does not exist.
	 */
	public function getConstantReflection($name)
	{
		throw new Exception\RuntimeException(sprintf('There is no constant "%s".', $name), Exception\RuntimeException::DOES_NOT_EXIST, $this);
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
	 * @return boolean
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
	 * @return boolean
	 */
	public function hasProperty($name)
	{
		return FALSE;
	}


	/**
	 * Returns class properties.
	 *
	 * @param integer $filter Property types
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
		throw new Exception\RuntimeException(sprintf('There is no property "%s".', $name), Exception\RuntimeException::DOES_NOT_EXIST, $this);
	}


	/**
	 * Returns if the class (and not its parents) implements the given property.
	 *
	 * @param string $name Property name
	 * @return boolean
	 */
	public function hasOwnProperty($name)
	{
		return FALSE;
	}


	/**
	 * Returns properties declared by this class, not its parents.
	 *
	 * @param integer $filter Properties filter
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
	 * @return boolean
	 */
	public function hasTraitProperty($name)
	{
		return FALSE;
	}


	/**
	 * Returns property reflections imported from traits.
	 *
	 * @param integer $filter Properties filter
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
		throw new Exception\RuntimeException(sprintf('There is no static property "%s".', $name), Exception\RuntimeException::DOES_NOT_EXIST, $this);
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
	 * @return boolean
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If the provided argument is not an object.
	 */
	public function isInstance($object)
	{
		if (!is_object($object)) {
			throw new Exception\RuntimeException(sprintf('Parameter must be a class instance, "%s" provided.', gettype($object)), Exception\RuntimeException::INVALID_ARGUMENT, $this);
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
		if (!class_exists($this->name, TRUE)) {
			throw new Exception\RuntimeException('Could not create an instance; class does not exist.', Exception\RuntimeException::DOES_NOT_EXIST, $this);
		}
		$reflection = new ApiGen\TokenReflection\Php\ReflectionClass($this->name, $this->getBroker());
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
		if (!class_exists($this->name, TRUE)) {
			throw new Exception\RuntimeException('Could not create an instance of class; class does not exist.', Exception\RuntimeException::DOES_NOT_EXIST, $this);
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
		throw new Exception\RuntimeException(sprintf('There is no static property "%s".', $name), Exception\RuntimeException::DOES_NOT_EXIST, $this);
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
	 * @param boolean $return Return the export instead of outputting it
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
	 * @return integer
	 */
	public function getStartPosition()
	{
		return -1;
	}


	/**
	 * Returns the end position in the file token stream.
	 *
	 * @return integer
	 */
	public function getEndPosition()
	{
		return -1;
	}


	/**
	 * Returns if the class definition is complete.
	 *
	 * Invalid classes are always complete.
	 *
	 * @return boolean
	 */
	public function isComplete()
	{
		return TRUE;
	}


	/**
	 * Returns if the class definition is valid.
	 *
	 * @return boolean
	 */
	public function isValid()
	{
		return FALSE;
	}


	/**
	 * Returns if the reflection subject is deprecated.
	 *
	 * @return boolean
	 */
	public function isDeprecated()
	{
		return FALSE;
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
		return ReflectionBase::get($this, $key);
	}


	/**
	 * Magic __isset method.
	 *
	 * @param string $key Variable name
	 * @return boolean
	 */
	final public function __isset($key)
	{
		return ReflectionBase::exists($this, $key);
	}

}
