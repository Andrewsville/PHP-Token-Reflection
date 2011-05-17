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

/**
 * Common reflection classes interface.
 */
interface IReflectionClass extends IReflection
{
	/**
	 * Returns a constant value.
	 *
	 * @param string $name Constant name
	 * @return mixed
	 * @throws \TokenReflection\Exception\Runtime If the requested constant does not exist
	 */
	public function getConstant($name);

	/**
	 * Returns a constant reflection.
	 *
	 * @param string $name Constant name
	 * @return \TokenReflection\IReflectionConstant
	 * @throws \TokenReflection\Exception\Runtime If the requested constant does not exist
	 */
	public function getConstantReflection($name);

	/**
	 * Returns an array of constant values.
	 *
	 * @return array
	 */
	public function getConstants();

	/**
	 * Returns values of constants declared by this class, not by its parents.
	 *
	 * @return array
	 */
	public function getOwnConstants();

	/**
	 * Returns an array of constant reflections.
	 *
	 * @return array
	 */
	public function getConstantReflections();

	/**
	 * Returns reflections of constants declared by this class, not by its parents.
	 *
	 * @return array
	 */
	public function getOwnConstantReflections();

	/**
	 * Returns the class constructor reflection.
	 *
	 * @return \TokenReflection\IReflectionMethod|null
	 */
	public function getConstructor();


	/**
	 * Returns the class desctructor reflection.
	 *
	 * @return \TokenReflection\IReflectionMethod|null
	 */
	public function getDestructor();

	/**
	 * Returns default properties.
	 *
	 * @return array
	 */
	public function getDefaultProperties();

	/**
	 * Returns the appropriate docblock definition.
	 *
	 * @return string|false
	 */
	public function getDocComment();

	/**
	 * Returns the docblock definition of the class or its parent.
	 *
	 * @return string|false
	 */
	public function getInheritedDocComment();

	/**
	 * Returns the definition start line number in the file.
	 *
	 * @return integer
	 */
	public function getStartLine();

	/**
	 * Returns the definition end line number in the file.
	 *
	 * @return integer
	 */
	public function getEndLine();

	/**
	 * Returns the PHP extension reflection.
	 *
	 * @return \TokenReflection\IReflectionExtension|null
	 */
	public function getExtension();

	/**
	 * Returns the PHP extension name.
	 *
	 * @return string|null
	 */
	public function getExtensionName();

	/**
	 * Returns the file name the reflection object is defined in.
	 *
	 * @return string
	 */
	public function getFileName();

	/**
	 * Returns interface names.
	 *
	 * @return array
	 */
	public function getInterfaceNames();

	/**
	 * Returns names of interfaces implemented by this class, not its parents.
	 *
	 * @return array
	 */
	public function getOwnInterfaceNames();

	/**
	 * Returns interface reflections.
	 *
	 * @return array
	 */
	public function getInterfaces();

	/**
	 * Returns interfaces implemented by this class, not its parents.
	 *
	 * @return array
	 */
	public function getOwnInterfaces();

	/**
	 * Returns a method reflection.
	 *
	 * @param string $name Method name
	 * @return \TokenReflection\IReflectionMethod
	 * @throws \TokenReflection\Exception\Runtime If the requested method does not exist
	 */
	public function getMethod($name);

	/**
	 * Returns method reflections.
	 *
	 * @param integer $filter Method filter
	 * @return array
	 */
	public function getMethods($filter = null);

	/**
	 * Returns methods declared by this class, not its parents.
	 *
	 * @param integer $filter Method filter
	 * @return array
	 */
	public function getOwnMethods($filter = null);

	/**
	 * Returns modifiers.
	 *
	 * @return array
	 */
	public function getModifiers();

	/**
	 * Returns the namespace name.
	 *
	 * @return string
	 */
	public function getNamespaceName();

	/**
	 * Returns the parent class reflection.
	 *
	 * @return \TokenReflection\IReflectionClass|null
	 */
	public function getParentClass();

	/**
	 * Returns the parent classes reflections.
	 *
	 * @return array
	 */
	public function getParentClasses();

	/**
	 * Returns the parent class reflection.
	 *
	 * @return \TokenReflection\ReflectionClass
	 */
	public function getParentClassName();

	/**
	 * Returns the parent classes names.
	 *
	 * @return array
	 */
	public function getParentClassNameList();

	/**
	 * Returns class properties.
	 *
	 * @param integer $filter Property types
	 * @return array
	 */
	public function getProperties($filter = null);

	/**
	 * Returns properties declared by this class, not its parents.
	 *
	 * @param integer $filter Properties filter
	 * @return array
	 */
	public function getOwnProperties($filter = null);

	/**
	 * Return a property reflections.
	 *
	 * @param string $name Property name
	 * @return \TokenReflection\ReflectionProperty
	 * @throws \TokenReflection\Exception\Runtime If the requested property does not exist
	 */
	public function getProperty($name);

	/**
	 * Returns the unqualified name.
	 *
	 * @return string
	 */
	public function getShortName();

	/**
	 * Returns static properties reflections.
	 *
	 * @return array
	 */
	public function getStaticProperties();

	/**
	 * Returns a value of a static property.
	 *
	 * @param string $name Property name
	 * @param mixed $default Default value
	 * @return mixed
	 * @throws \TokenReflection\Exception\Runtime If the requested static property does not exist
	 * @throws \TokenReflection\Exception\Runtime If the requested static property is not accessible
	 */
	public function getStaticPropertyValue($name, $default = null);

	/**
	 * Returns if the class defines the given constant.
	 *
	 * @param string $name Constant name.
	 * @return boolean
	 */
	public function hasConstant($name);

	/**
	 * Returns if the class (and not its parents) defines the given constant.
	 *
	 * @param string $name Constant name.
	 * @return boolean
	 */
	public function hasOwnConstant($name);

	/**
	 * Returns if the class implements the given method.
	 *
	 * @param string $name Method name
	 * @return boolean
	 */
	public function hasMethod($name);

	/**
	 * Returns if the class implements (and not its parents) the given method.
	 *
	 * @param string $name Method name
	 * @return boolean
	 */
	public function hasOwnMethod($name);

	/**
	 * Returns if the class implements the given property.
	 *
	 * @param string $name Property name
	 * @return boolean
	 */
	public function hasProperty($name);

	/**
	 * Returns if the class (and not its parents) implements the given property.
	 *
	 * @param string $name Property name
	 * @return boolean
	 */
	public function hasOwnProperty($name);

	/**
	 * Returns if the class implements the given interface.
	 *
	 * @param string|object $interface Interface name or reflection object
	 * @return boolean
	 * @throws \TokenReflection\Exception\Runtime If an invalid object was provided as interface
	 */
	public function implementsInterface($interface);

	/**
	 * Returns if the class is defined within a namespace.
	 *
	 * @return boolean
	 */
	public function inNamespace();

	/**
	 * Returns if the class is abstract.
	 *
	 * @return boolean
	 */
	public function isAbstract();

	/**
	 * Returns if the class is final.
	 *
	 * @return boolean
	 */
	public function isFinal();

	/**
	 * Returns if the given object is an instance of this class.
	 *
	 * @param object $object Instance
	 * @return boolean
	 * @throws \TokenReflection\Exception\Runtime If the provided argument is not an object
	 */
	public function isInstance($object);

	/**
	 * Returns if it is possible to create an instance of this class.
	 *
	 * @return boolean
	 */
	public function isInterface();

	/**
	 * Returns if objects of this class are cloneable.
	 *
	 * Not implemented in 5.3, but in trunk though.
	 *
	 * @return boolean
	 * @see http://svn.php.net/viewvc/php/php-src/trunk/ext/reflection/php_reflection.c?revision=307971&view=markup#l4059
	 */
	public function isCloneable();

	/**
	 * Returns if the class is iterateable.
	 *
	 * Returns true if the class implements the Traversable interface.
	 *
	 * @return boolean
	 */
	public function isIterateable();

	/**
	 * Returns if the current class is a subclass of the given class.
	 *
	 * @param string|object $class Class name or reflection object
	 * @return boolean
	 */
	public function isSubclassOf($class);

	/**
	 * Returns reflections of direct subclasses.
	 *
	 * @return array
	 */
	public function getDirectSubclasses();

	/**
	 * Returns names of direct subclasses.
	 *
	 * @return array
	 */
	public function getDirectSubclassNames();

	/**
	 * Returns reflections of indirect subclasses.
	 *
	 * @return array
	 */
	public function getIndirectSubclasses();

	/**
	 * Returns names of indirect subclasses.
	 *
	 * @return array
	 */
	public function getIndirectSubclassNames();

	/**
	 * Returns reflections of classes directly implementing this interface.
	 *
	 * @return array
	 */
	public function getDirectImplementers();

	/**
	 * Returns names of classes directly implementing this interface.
	 *
	 * @return array
	 */
	public function getDirectImplementerNames();

	/**
	 * Returns reflections of classes indirectly implementing this interface.
	 *
	 * @return array
	 */
	public function getIndirectImplementers();

	/**
	 * Returns names of classes indirectly implementing this interface.
	 *
	 * @return array
	 */
	public function getIndirectImplementerNames();

	/**
	 * Creates a new instance using variable number of parameters.
	 *
	 * Use any number of constructor parameters as function parameters.
	 *
	 * @return object
	 */
	public function newInstance($args);

	/**
	 * Creates a new instance using an array of parameters.
	 *
	 * @param array $args Array of constructor parameters
	 * @return object
	 * @throws \TokenReflection\Exception\Runtime If the required class does not exist
	 */
	public function newInstanceArgs(array $args = array());

	/**
	 * Sets a static property value.
	 *
	 * @param string $name Property name
	 * @param mixed $value Property value
	 * @throws \TokenReflection\Exception\Runtime If the requested static property does not exist
	 * @throws \TokenReflection\Exception\Runtime If the requested static property is not accessible
	 */
	public function setStaticPropertyValue($name, $value);
}
