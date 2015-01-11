<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection;


interface IReflectionClass extends IReflection
{

	/**
	 * Returns the unqualified name (UQN).
	 *
	 * @return string
	 */
	function getShortName();


	/**
	 * Returns the namespace name.
	 *
	 * @return string
	 */
	function getNamespaceName();


	/**
	 * Returns if the class is defined within a namespace.
	 *
	 * @return bool
	 */
	function inNamespace();


	/**
	 * Returns imported namespaces and aliases from the declaring namespace.
	 *
	 * @return array
	 */
	function getNamespaceAliases();


	/**
	 * Returns the PHP extension reflection.
	 *
	 * @return ApiGen\TokenReflection\IReflectionExtension|null
	 */
	function getExtension();


	/**
	 * Returns the PHP extension name.
	 *
	 * @return string|null
	 */
	function getExtensionName();


	/**
	 * Returns the file name the reflection object is defined in.
	 *
	 * @return string
	 */
	function getFileName();


	/**
	 * Returns the definition start line number in the file.
	 *
	 * @return int
	 */
	function getStartLine();


	/**
	 * Returns the definition end line number in the file.
	 *
	 * @return int
	 */
	function getEndLine();


	/**
	 * Returns the appropriate docblock definition.
	 *
	 * @return string|bool
	 */
	function getDocComment();


	/**
	 * Returns modifiers.
	 *
	 * @return array
	 */
	function getModifiers();


	/**
	 * Returns if the class is abstract.
	 *
	 * @return bool
	 */
	function isAbstract();


	/**
	 * Returns if the class is final.
	 *
	 * @return bool
	 */
	function isFinal();


	/**
	 * Returns if the class is an interface.
	 *
	 * @return bool
	 */
	function isInterface();


	/**
	 * Returns if the class is an exception or its descendant.
	 *
	 * @return bool
	 */
	function isException();


	/**
	 * Returns if objects of this class are cloneable.
	 *
	 * Introduced in PHP 5.4.
	 *
	 * @return bool
	 * @see http://svn.php.net/viewvc/php/php-src/trunk/ext/reflection/php_reflection.c?revision=307971&view=markup#l4059
	 */
	function isCloneable();


	/**
	 * Returns if the class is iterateable.
	 *
	 * Returns true if the class implements the Traversable interface.
	 *
	 * @return bool
	 */
	function isIterateable();


	/**
	 * Returns if the current class is a subclass of the given class.
	 *
	 * @param string|object $class Class name or reflection object
	 * @return bool
	 */
	function isSubclassOf($class);


	/**
	 * Returns the parent class reflection.
	 *
	 * @return ApiGen\TokenReflection\IReflectionClass|null
	 */
	function getParentClass();


	/**
	 * Returns the parent class name.
	 *
	 * @return string|null
	 */
	function getParentClassName();


	/**
	 * Returns the parent classes reflections.
	 *
	 * @return array
	 */
	function getParentClasses();


	/**
	 * Returns the parent classes names.
	 *
	 * @return array
	 */
	function getParentClassNameList();


	/**
	 * Returns if the class implements the given interface.
	 *
	 * @param string|object $interface Interface name or reflection object
	 * @return bool
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If an invalid object was provided as interface.
	 */
	function implementsInterface($interface);


	/**
	 * Returns interface reflections.
	 *
	 * @return array
	 */
	function getInterfaces();


	/**
	 * Returns interface names.
	 *
	 * @return array
	 */
	function getInterfaceNames();


	/**
	 * Returns interface reflections implemented by this class, not its parents.
	 *
	 * @return array
	 */
	function getOwnInterfaces();


	/**
	 * Returns names of interfaces implemented by this class, not its parents.
	 *
	 * @return array
	 */
	function getOwnInterfaceNames();


	/**
	 * Returns the class constructor reflection.
	 *
	 * @return ApiGen\TokenReflection\IReflectionMethod|null
	 */
	function getConstructor();


	/**
	 * Returns the class desctructor reflection.
	 *
	 * @return ApiGen\TokenReflection\IReflectionMethod|null
	 */
	function getDestructor();


	/**
	 * Returns if the class implements the given method.
	 *
	 * @param string $name Method name
	 * @return bool
	 */
	function hasMethod($name);


	/**
	 * Returns a method reflection.
	 *
	 * @param string $name Method name
	 * @return ApiGen\TokenReflection\IReflectionMethod
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If the requested method does not exist.
	 */
	function getMethod($name);


	/**
	 * Returns method reflections.
	 *
	 * @param int $filter Methods filter
	 * @return array
	 */
	function getMethods($filter = NULL);


	/**
	 * Returns if the class implements (and not its parents) the given method.
	 *
	 * @param string $name Method name
	 * @return bool
	 */
	function hasOwnMethod($name);


	/**
	 * Returns method reflections declared by this class, not its parents.
	 *
	 * @param int $filter Methods filter
	 * @return array
	 */
	function getOwnMethods($filter = NULL);


	/**
	 * Returns if the class imports the given method from traits.
	 *
	 * @param string $name Method name
	 * @return bool
	 */
	function hasTraitMethod($name);


	/**
	 * Returns method reflections imported from traits.
	 *
	 * @param int $filter Methods filter
	 * @return array
	 */
	function getTraitMethods($filter = NULL);


	/**
	 * Returns if the class defines the given constant.
	 *
	 * @param string $name Constant name.
	 * @return bool
	 */
	function hasConstant($name);


	/**
	 * Returns a constant value.
	 *
	 * @param string $name Constant name
	 * @return mixed
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If the requested constant does not exist.
	 */
	function getConstant($name);


	/**
	 * Returns a constant reflection.
	 *
	 * @param string $name Constant name
	 * @return ApiGen\TokenReflection\IReflectionConstant
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If the requested constant does not exist.
	 */
	function getConstantReflection($name);


	/**
	 * Returns an array of constant values.
	 *
	 * @return array
	 */
	function getConstants();


	/**
	 * Returns constant reflections.
	 *
	 * @return array
	 */
	function getConstantReflections();


	/**
	 * Returns if the class (and not its parents) defines the given constant.
	 *
	 * @param string $name Constant name.
	 * @return bool
	 */
	function hasOwnConstant($name);


	/**
	 * Returns values of constants declared by this class, not by its parents.
	 *
	 * @return array
	 */
	function getOwnConstants();


	/**
	 * Returns constant reflections declared by this class, not by its parents.
	 *
	 * @return array
	 */
	function getOwnConstantReflections();


	/**
	 * Returns if the class defines the given property.
	 *
	 * @param string $name Property name
	 * @return bool
	 */
	function hasProperty($name);


	/**
	 * Return a property reflection.
	 *
	 * @param string $name Property name
	 * @return ApiGen\TokenReflection\ReflectionProperty
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If the requested property does not exist.
	 */
	function getProperty($name);


	/**
	 * Returns property reflections.
	 *
	 * @param int $filter Properties filter
	 * @return array
	 */
	function getProperties($filter = NULL);


	/**
	 * Returns if the class (and not its parents) defines the given property.
	 *
	 * @param string $name Property name
	 * @return bool
	 */
	function hasOwnProperty($name);


	/**
	 * Returns property reflections declared by this class, not its parents.
	 *
	 * @param int $filter Properties filter
	 * @return array
	 */
	function getOwnProperties($filter = NULL);


	/**
	 * Returns if the class imports the given property from traits.
	 *
	 * @param string $name Property name
	 * @return bool
	 */
	function hasTraitProperty($name);


	/**
	 * Returns property reflections imported from traits.
	 *
	 * @param int $filter Properties filter
	 * @return array
	 */
	function getTraitProperties($filter = NULL);


	/**
	 * Returns default properties.
	 *
	 * @return array
	 */
	function getDefaultProperties();


	/**
	 * Returns static properties reflections.
	 *
	 * @return array
	 */
	function getStaticProperties();


	/**
	 * Returns a value of a static property.
	 *
	 * @param string $name Property name
	 * @param mixed $default Default value
	 * @return mixed
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If the requested static property does not exist.
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If the requested static property is not accessible.
	 */
	function getStaticPropertyValue($name, $default = NULL);


	/**
	 * Returns reflections of direct subclasses.
	 *
	 * @return array
	 */
	function getDirectSubclasses();


	/**
	 * Returns names of direct subclasses.
	 *
	 * @return array
	 */
	function getDirectSubclassNames();


	/**
	 * Returns reflections of indirect subclasses.
	 *
	 * @return array
	 */
	function getIndirectSubclasses();


	/**
	 * Returns names of indirect subclasses.
	 *
	 * @return array
	 */
	function getIndirectSubclassNames();


	/**
	 * Returns reflections of classes directly implementing this interface.
	 *
	 * @return array
	 */
	function getDirectImplementers();


	/**
	 * Returns names of classes directly implementing this interface.
	 *
	 * @return array
	 */
	function getDirectImplementerNames();


	/**
	 * Returns reflections of classes indirectly implementing this interface.
	 *
	 * @return array
	 */
	function getIndirectImplementers();


	/**
	 * Returns names of classes indirectly implementing this interface.
	 *
	 * @return array
	 */
	function getIndirectImplementerNames();


	/**
	 * Returns if it is possible to create an instance of this class.
	 *
	 * @return bool
	 */
	function isInstantiable();


	/**
	 * Returns traits used by this class.
	 *
	 * @return array
	 */
	function getTraits();


	/**
	 * Returns traits used by this class and not its parents.
	 *
	 * @return array
	 */
	function getOwnTraits();


	/**
	 * Returns names of used traits.
	 *
	 * @return array
	 */
	function getTraitNames();


	/**
	 * Returns names of traits used by this class an not its parents.
	 *
	 * @return array
	 */
	function getOwnTraitNames();


	/**
	 * Returns method aliases from traits.
	 *
	 * @return array
	 */
	function getTraitAliases();


	/**
	 * Returns if the class uses a particular trait.
	 *
	 * @param \ReflectionClass|\TokenReflection\IReflectionClass|string $trait Trait reflection or name
	 * @return bool
	 */
	function usesTrait($trait);


	/**
	 * Returns if the class is a trait.
	 *
	 * @return bool
	 */
	function isTrait();


	/**
	 * Returns if the given object is an instance of this class.
	 *
	 * @param object $object Instance
	 * @return bool
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If the provided argument is not an object.
	 */
	function isInstance($object);


	/**
	 * Creates a new class instance without using a constructor.
	 *
	 * @return object
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If the class inherits from an internal class.
	 */
	function newInstanceWithoutConstructor();


	/**
	 * Creates a new instance using variable number of parameters.
	 *
	 * Use any number of constructor parameters as function parameters.
	 *
	 * @param mixed $args
	 * @return object
	 */
	function newInstance($args);


	/**
	 * Creates a new instance using an array of parameters.
	 *
	 * @param array $args Array of constructor parameters
	 * @return object
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If the required class does not exist.
	 */
	function newInstanceArgs(array $args = []);


	/**
	 * Sets a static property value.
	 *
	 * @param string $name Property name
	 * @param mixed $value Property value
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If the requested static property does not exist.
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If the requested static property is not accessible.
	 */
	function setStaticPropertyValue($name, $value);


	/**
	 * Returns the string representation of the reflection object.
	 *
	 * @return string
	 */
	function __toString();


	/**
	 * Returns if the class definition is complete.
	 *
	 * That means if there are no dummy classes among parents and implemented interfaces.
	 *
	 * @return bool
	 */
	function isComplete();


	/**
	 * Returns if the class definition is valid.
	 *
	 * That means that the source code is valid and the class name is unique within parsed files.
	 *
	 * @return bool
	 */
	function isValid();


	/**
	 * Returns if the class is deprecated.
	 *
	 * @return bool
	 */
	function isDeprecated();

}
