<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection;

use ApiGen\TokenReflection\Reflection\ReflectionProperty;


interface ReflectionClassInterface extends ReflectionInterface
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
	 * @return ReflectionClassInterface|NULL
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
	 * @return array|ReflectionClassInterface[]
	 */
	function getParentClasses();


	/**
	 * Returns the parent classes names.
	 *
	 * @return array|string[]
	 */
	function getParentClassNameList();


	/**
	 * Returns if the class implements the given interface.
	 *
	 * @param string|object $interface Interface name or reflection object
	 * @return bool
	 */
	function implementsInterface($interface);


	/**
	 * Returns interface reflections.
	 *
	 * @return array|ReflectionClassInterface[]
	 */
	function getInterfaces();


	/**
	 * Returns interface reflections implemented by this class, not its parents.
	 *
	 * @return ReflectionClassInterface[]
	 */
	function getOwnInterfaces();


	/**
	 * Returns the class constructor reflection.
	 *
	 * @return ReflectionMethodInterface|NULL
	 */
	function getConstructor();


	/**
	 * Returns the class destructor reflection.
	 *
	 * @return ReflectionMethodInterface|NULL
	 */
	function getDestructor();


	/**
	 * Returns if the class implements the given method.
	 *
	 * @param string $name
	 * @return bool
	 */
	function hasMethod($name);


	/**
	 * Returns a method reflection.
	 *
	 * @param string $name
	 * @return ReflectionMethodInterface
	 */
	function getMethod($name);


	/**
	 * Returns method reflections.
	 *
	 * @param int $filter Methods filter
	 * @return array|ReflectionMethodInterface[]
	 */
	function getMethods($filter = NULL);


	/**
	 * Returns if the class implements (and not its parents) the given method.
	 *
	 * @param string $name
	 * @return bool
	 */
	function hasOwnMethod($name);


	/**
	 * Returns method reflections declared by this class, not its parents.
	 *
	 * @param int $filter Methods filter
	 * @return ReflectionMethodInterface[]
	 */
	function getOwnMethods($filter = NULL);


	/**
	 * Returns if the class imports the given method from traits.
	 *
	 * @param string $name
	 * @return bool
	 */
	function hasTraitMethod($name);


	/**
	 * Returns method reflections imported from traits.
	 *
	 * @param int $filter Methods filter
	 * @return ReflectionMethodInterface[]
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
	 * @return mixed|ReflectionConstantInterface
	 */
	function getConstant($name);


	/**
	 * Returns a constant reflection.
	 *
	 * @param string $name Constant name
	 * @return ReflectionConstantInterface
	 */
	function getConstantReflection($name);


	/**
	 * Returns an array of constant values.
	 *
	 * @return array|ReflectionConstantInterface[]
	 */
	function getConstants();


	/**
	 * Returns constant reflections.
	 *
	 * @return array|ReflectionConstantInterface[]
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
	 * @return array|ReflectionConstantInterface[]
	 */
	function getOwnConstants();


	/**
	 * Returns constant reflections declared by this class, not by its parents.
	 *
	 * @return array|ReflectionConstantInterface[]
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
	 * @param string $name
	 * @return ReflectionProperty
	 */
	function getProperty($name);


	/**
	 * Returns property reflections.
	 *
	 * @param int $filter Properties filter
	 * @return array|ReflectionPropertyInterface[]
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
	 * @return array|ReflectionPropertyInterface[]
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
	 * @return array|ReflectionPropertyInterface[]
	 */
	function getTraitProperties($filter = NULL);


	/**
	 * Returns default properties.
	 *
	 * @return array|ReflectionPropertyInterface[]
	 */
	function getDefaultProperties();


	/**
	 * Returns static properties reflections.
	 *
	 * @return array|ReflectionPropertyInterface[]
	 */
	function getStaticProperties();


	/**
	 * Returns a value of a static property.
	 *
	 * @param string $name Property name
	 * @param mixed $default Default value
	 * @return mixed
	 */
	function getStaticPropertyValue($name, $default = NULL);


	/**
	 * Returns reflections of direct subclasses.
	 *
	 * @return array|ReflectionClassInterface[]
	 */
	function getDirectSubclasses();


	/**
	 * Returns reflections of indirect subclasses.
	 *
	 * @return array|ReflectionClassInterface[]
	 */
	function getIndirectSubclasses();


	/**
	 * Returns reflections of classes directly implementing this interface.
	 *
	 * @return array|ReflectionClassInterface[]
	 */
	function getDirectImplementers();


	/**
	 * Returns reflections of classes indirectly implementing this interface.
	 *
	 * @return array|ReflectionClassInterface[]
	 */
	function getIndirectImplementers();


	/**
	 * Returns if it is possible to create an instance of this class.
	 *
	 * @return bool
	 */
	function isInstantiable();


	/**
	 * Returns traits used by this class.
	 *
	 * @return array|ReflectionClassInterface[]
	 */
	function getTraits();


	/**
	 * Returns traits used by this class and not its parents.
	 *
	 * @return array|ReflectionClassInterface[]
	 */
	function getOwnTraits();


	/**
	 * Returns method aliases from traits.
	 *
	 * @return array
	 */
	function getTraitAliases();


	/**
	 * Returns if the class uses a particular trait.
	 *
	 * @param \ReflectionClass|ReflectionClassInterface|string $trait Trait reflection or name
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
	 */
	function isInstance($object);


	/**
	 * Sets a static property value.
	 *
	 * @param string $name Property name
	 * @param mixed $value Property value
	 */
	function setStaticPropertyValue($name, $value);


	/**
	 * Returns if the class definition is complete.
	 *
	 * That means if there are no dummy classes among parents and implemented interfaces.
	 *
	 * @return bool
	 */
	function isComplete();


	/**
	 * Returns if the class is deprecated.
	 *
	 * @return bool
	 */
	function isDeprecated();

}
