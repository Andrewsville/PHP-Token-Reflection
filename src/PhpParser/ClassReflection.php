<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\PhpParser;

use ApiGen\TokenReflection\Parser;
use ApiGen\TokenReflection\Exception\RuntimeException;
use ApiGen\TokenReflection\Reflection\ReflectionProperty;
use ApiGen\TokenReflection\ReflectionClassInterface;
use ApiGen\TokenReflection\ReflectionConstantInterface;
use ApiGen\TokenReflection\ReflectionMethodInterface;
use ApiGen\TokenReflection\ReflectionPropertyInterface;


class ClassReflection implements ReflectionClassInterface
{

	/**
	 * @var string
	 */
	private $name;


	/**
	 * @param string $name
	 */
	public function __construct($name)
	{
		$this->name = $name;
	}


	/**
	 * Returns the unqualified name (UQN).
	 *
	 * @return string
	 */
	function getShortName()
	{
		// TODO: Implement getShortName() method.
	}


	/**
	 * Returns the namespace name.
	 *
	 * @return string
	 */
	function getNamespaceName()
	{
		// TODO: Implement getNamespaceName() method.
	}


	/**
	 * Returns if the class is defined within a namespace.
	 *
	 * @return bool
	 */
	function inNamespace()
	{
		// TODO: Implement inNamespace() method.
	}


	/**
	 * Returns imported namespaces and aliases from the declaring namespace.
	 *
	 * @return array
	 */
	function getNamespaceAliases()
	{
		// TODO: Implement getNamespaceAliases() method.
	}


	/**
	 * Returns the file name the reflection object is defined in.
	 *
	 * @return string
	 */
	function getFileName()
	{
		// TODO: Implement getFileName() method.
	}


	/**
	 * Returns the definition start line number in the file.
	 *
	 * @return int
	 */
	function getStartLine()
	{
		// TODO: Implement getStartLine() method.
	}


	/**
	 * Returns the definition end line number in the file.
	 *
	 * @return int
	 */
	function getEndLine()
	{
		// TODO: Implement getEndLine() method.
	}


	/**
	 * Returns the appropriate docblock definition.
	 *
	 * @return string|bool
	 */
	function getDocComment()
	{
		// TODO: Implement getDocComment() method.
	}


	/**
	 * Returns modifiers.
	 *
	 * @return array
	 */
	function getModifiers()
	{
		// TODO: Implement getModifiers() method.
	}


	/**
	 * Returns if the class is abstract.
	 *
	 * @return bool
	 */
	function isAbstract()
	{
		// TODO: Implement isAbstract() method.
	}


	/**
	 * Returns if the class is final.
	 *
	 * @return bool
	 */
	function isFinal()
	{
		// TODO: Implement isFinal() method.
	}


	/**
	 * Returns if the class is an interface.
	 *
	 * @return bool
	 */
	function isInterface()
	{
		// TODO: Implement isInterface() method.
	}


	/**
	 * Returns if the class is an exception or its descendant.
	 *
	 * @return bool
	 */
	function isException()
	{
		// TODO: Implement isException() method.
	}


	/**
	 * Returns if objects of this class are cloneable.
	 *
	 * @return bool
	 * @see http://svn.php.net/viewvc/php/php-src/trunk/ext/reflection/php_reflection.c?revision=307971&view=markup#l4059
	 */
	function isCloneable()
	{
		// TODO: Implement isCloneable() method.
	}


	/**
	 * Returns if the class is iterateable.
	 *
	 * Returns true if the class implements the Traversable interface.
	 *
	 * @return bool
	 */
	function isIterateable()
	{
		// TODO: Implement isIterateable() method.
	}


	/**
	 * Returns if the current class is a subclass of the given class.
	 *
	 * @param string|object $class Class name or reflection object
	 * @return bool
	 */
	function isSubclassOf($class)
	{
		// TODO: Implement isSubclassOf() method.
	}


	/**
	 * Returns the parent class reflection.
	 *
	 * @return ReflectionClassInterface|NULL
	 */
	function getParentClass()
	{
		// TODO: Implement getParentClass() method.
	}


	/**
	 * Returns the parent class name.
	 *
	 * @return string|null
	 */
	function getParentClassName()
	{
		// TODO: Implement getParentClassName() method.
	}


	/**
	 * Returns the parent classes reflections.
	 *
	 * @return array|ReflectionClassInterface[]
	 */
	function getParentClasses()
	{
		// TODO: Implement getParentClasses() method.
	}


	/**
	 * Returns the parent classes names.
	 *
	 * @return array|string[]
	 */
	function getParentClassNameList()
	{
		// TODO: Implement getParentClassNameList() method.
	}


	/**
	 * Returns if the class implements the given interface.
	 *
	 * @param string|object $interface Interface name or reflection object
	 * @return bool
	 * @throws RuntimeException If an invalid object was provided as interface.
	 */
	function implementsInterface($interface)
	{
		// TODO: Implement implementsInterface() method.
	}


	/**
	 * Returns interface reflections.
	 *
	 * @return array|ReflectionClassInterface[]
	 */
	function getInterfaces()
	{
		// TODO: Implement getInterfaces() method.
	}


	/**
	 * Returns interface names.
	 *
	 * @return array|string[]
	 */
	function getInterfaceNames()
	{
		// TODO: Implement getInterfaceNames() method.
	}


	/**
	 * Returns interface reflections implemented by this class, not its parents.
	 *
	 * @return array|ReflectionClassInterface[]
	 */
	function getOwnInterfaces()
	{
		// TODO: Implement getOwnInterfaces() method.
	}


	/**
	 * Returns names of interfaces implemented by this class, not its parents.
	 *
	 * @return array|string[]
	 */
	function getOwnInterfaceNames()
	{
		// TODO: Implement getOwnInterfaceNames() method.
	}


	/**
	 * Returns the class constructor reflection.
	 *
	 * @return ReflectionMethodInterface|NULL
	 */
	function getConstructor()
	{
		// TODO: Implement getConstructor() method.
	}


	/**
	 * Returns the class destructor reflection.
	 *
	 * @return ReflectionMethodInterface|NULL
	 */
	function getDestructor()
	{
		// TODO: Implement getDestructor() method.
	}


	/**
	 * Returns if the class implements the given method.
	 *
	 * @param string $name Method name
	 * @return bool
	 */
	function hasMethod($name)
	{
		// TODO: Implement hasMethod() method.
	}


	/**
	 * Returns a method reflection.
	 *
	 * @param string $name Method name
	 * @return ReflectionMethodInterface
	 * @throws RuntimeException If the requested method does not exist.
	 */
	function getMethod($name)
	{
		// TODO: Implement getMethod() method.
	}


	/**
	 * Returns method reflections.
	 *
	 * @param int $filter Methods filter
	 * @return array|ReflectionMethodInterface[]
	 */
	function getMethods($filter = NULL)
	{
		// TODO: Implement getMethods() method.
	}


	/**
	 * Returns if the class implements (and not its parents) the given method.
	 *
	 * @param string $name
	 * @return bool
	 */
	function hasOwnMethod($name)
	{
		// TODO: Implement hasOwnMethod() method.
	}


	/**
	 * Returns method reflections declared by this class, not its parents.
	 *
	 * @param int $filter Methods filter
	 * @return array|ReflectionMethodInterface[]
	 */
	function getOwnMethods($filter = NULL)
	{
		// TODO: Implement getOwnMethods() method.
	}


	/**
	 * Returns if the class imports the given method from traits.
	 *
	 * @param string $name Method name
	 * @return bool
	 */
	function hasTraitMethod($name)
	{
		// TODO: Implement hasTraitMethod() method.
	}


	/**
	 * Returns method reflections imported from traits.
	 *
	 * @param int $filter Methods filter
	 * @return array|ReflectionMethodInterface[]
	 */
	function getTraitMethods($filter = NULL)
	{
		// TODO: Implement getTraitMethods() method.
	}


	/**
	 * Returns if the class defines the given constant.
	 *
	 * @param string $name Constant name.
	 * @return bool
	 */
	function hasConstant($name)
	{
		// TODO: Implement hasConstant() method.
	}


	/**
	 * Returns a constant value.
	 *
	 * @param string $name Constant name
	 * @return mixed|ReflectionConstantInterface
	 * @throws RuntimeException If the requested constant does not exist.
	 */
	function getConstant($name)
	{
		// TODO: Implement getConstant() method.
	}


	/**
	 * Returns a constant reflection.
	 *
	 * @param string $name Constant name
	 * @return ReflectionConstantInterface
	 * @throws RuntimeException If the requested constant does not exist.
	 */
	function getConstantReflection($name)
	{
		// TODO: Implement getConstantReflection() method.
	}


	/**
	 * Returns an array of constant values.
	 *
	 * @return array|ReflectionConstantInterface[]
	 */
	function getConstants()
	{
		// TODO: Implement getConstants() method.
	}


	/**
	 * Returns constant reflections.
	 *
	 * @return array|ReflectionConstantInterface[]
	 */
	function getConstantReflections()
	{
		// TODO: Implement getConstantReflections() method.
	}


	/**
	 * Returns if the class (and not its parents) defines the given constant.
	 *
	 * @param string $name Constant name.
	 * @return bool
	 */
	function hasOwnConstant($name)
	{
		// TODO: Implement hasOwnConstant() method.
	}


	/**
	 * Returns values of constants declared by this class, not by its parents.
	 *
	 * @return array|ReflectionConstantInterface[]
	 */
	function getOwnConstants()
	{
		// TODO: Implement getOwnConstants() method.
	}


	/**
	 * Returns constant reflections declared by this class, not by its parents.
	 *
	 * @return array|ReflectionConstantInterface[]
	 */
	function getOwnConstantReflections()
	{
		// TODO: Implement getOwnConstantReflections() method.
	}


	/**
	 * Returns if the class defines the given property.
	 *
	 * @param string $name Property name
	 * @return bool
	 */
	function hasProperty($name)
	{
		// TODO: Implement hasProperty() method.
	}


	/**
	 * Return a property reflection.
	 *
	 * @param string $name Property name
	 * @return ReflectionProperty
	 * @throws RuntimeException If the requested property does not exist.
	 */
	function getProperty($name)
	{
		// TODO: Implement getProperty() method.
	}


	/**
	 * Returns property reflections.
	 *
	 * @param int $filter Properties filter
	 * @return array|ReflectionPropertyInterface[]
	 */
	function getProperties($filter = NULL)
	{
		// TODO: Implement getProperties() method.
	}


	/**
	 * Returns if the class (and not its parents) defines the given property.
	 *
	 * @param string $name Property name
	 * @return bool
	 */
	function hasOwnProperty($name)
	{
		// TODO: Implement hasOwnProperty() method.
	}


	/**
	 * Returns property reflections declared by this class, not its parents.
	 *
	 * @param int $filter Properties filter
	 * @return array|ReflectionPropertyInterface[]
	 */
	function getOwnProperties($filter = NULL)
	{
		// TODO: Implement getOwnProperties() method.
	}


	/**
	 * Returns if the class imports the given property from traits.
	 *
	 * @param string $name Property name
	 * @return bool
	 */
	function hasTraitProperty($name)
	{
		// TODO: Implement hasTraitProperty() method.
	}


	/**
	 * Returns property reflections imported from traits.
	 *
	 * @param int $filter Properties filter
	 * @return array|ReflectionPropertyInterface[]
	 */
	function getTraitProperties($filter = NULL)
	{
		// TODO: Implement getTraitProperties() method.
	}


	/**
	 * Returns default properties.
	 *
	 * @return array|ReflectionPropertyInterface[]
	 */
	function getDefaultProperties()
	{
		// TODO: Implement getDefaultProperties() method.
	}


	/**
	 * Returns static properties reflections.
	 *
	 * @return array|ReflectionPropertyInterface[]
	 */
	function getStaticProperties()
	{
		// TODO: Implement getStaticProperties() method.
	}


	/**
	 * Returns a value of a static property.
	 *
	 * @param string $name Property name
	 * @param mixed $default Default value
	 * @return mixed
	 * @throws RuntimeException If the requested static property does not exist.
	 * @throws RuntimeException If the requested static property is not accessible.
	 */
	function getStaticPropertyValue($name, $default = NULL)
	{
		// TODO: Implement getStaticPropertyValue() method.
	}


	/**
	 * Returns reflections of direct subclasses.
	 *
	 * @return array|ReflectionClassInterface[]
	 */
	function getDirectSubclasses()
	{
		// TODO: Implement getDirectSubclasses() method.
	}


	/**
	 * Returns names of direct subclasses.
	 *
	 * @return array|ReflectionClassInterface[]
	 */
	function getDirectSubclassNames()
	{
		// TODO: Implement getDirectSubclassNames() method.
	}


	/**
	 * Returns reflections of indirect subclasses.
	 *
	 * @return array|ReflectionClassInterface[]
	 */
	function getIndirectSubclasses()
	{
		// TODO: Implement getIndirectSubclasses() method.
	}


	/**
	 * Returns names of indirect subclasses.
	 *
	 * @return array|string[]
	 */
	function getIndirectSubclassNames()
	{
		// TODO: Implement getIndirectSubclassNames() method.
	}


	/**
	 * Returns reflections of classes directly implementing this interface.
	 *
	 * @return array|ReflectionClassInterface[]
	 */
	function getDirectImplementers()
	{
		// TODO: Implement getDirectImplementers() method.
	}


	/**
	 * Returns names of classes directly implementing this interface.
	 *
	 * @return array|string[]
	 */
	function getDirectImplementerNames()
	{
		// TODO: Implement getDirectImplementerNames() method.
	}


	/**
	 * Returns reflections of classes indirectly implementing this interface.
	 *
	 * @return array|ReflectionClassInterface[]
	 */
	function getIndirectImplementers()
	{
		// TODO: Implement getIndirectImplementers() method.
	}


	/**
	 * Returns names of classes indirectly implementing this interface.
	 *
	 * @return array|string[]
	 */
	function getIndirectImplementerNames()
	{
		// TODO: Implement getIndirectImplementerNames() method.
	}


	/**
	 * Returns if it is possible to create an instance of this class.
	 *
	 * @return bool
	 */
	function isInstantiable()
	{
		// TODO: Implement isInstantiable() method.
	}


	/**
	 * Returns traits used by this class.
	 *
	 * @return array|ReflectionClassInterface[]
	 */
	function getTraits()
	{
		// TODO: Implement getTraits() method.
	}


	/**
	 * Returns traits used by this class and not its parents.
	 *
	 * @return array|ReflectionClassInterface[]
	 */
	function getOwnTraits()
	{
		// TODO: Implement getOwnTraits() method.
	}


	/**
	 * Returns names of used traits.
	 *
	 * @return array
	 */
	function getTraitNames()
	{
		// TODO: Implement getTraitNames() method.
	}


	/**
	 * Returns names of traits used by this class an not its parents.
	 *
	 * @return array
	 */
	function getOwnTraitNames()
	{
		// TODO: Implement getOwnTraitNames() method.
	}


	/**
	 * Returns method aliases from traits.
	 *
	 * @return array
	 */
	function getTraitAliases()
	{
		// TODO: Implement getTraitAliases() method.
	}


	/**
	 * Returns if the class uses a particular trait.
	 *
	 * @param \ReflectionClass|ReflectionClassInterface|string $trait Trait reflection or name
	 * @return bool
	 */
	function usesTrait($trait)
	{
		// TODO: Implement usesTrait() method.
	}


	/**
	 * Returns if the class is a trait.
	 *
	 * @return bool
	 */
	function isTrait()
	{
		// TODO: Implement isTrait() method.
	}


	/**
	 * Returns if the given object is an instance of this class.
	 *
	 * @param object $object Instance
	 * @return bool
	 * @throws RuntimeException If the provided argument is not an object.
	 */
	function isInstance($object)
	{
		// TODO: Implement isInstance() method.
	}


	/**
	 * Sets a static property value.
	 *
	 * @param string $name Property name
	 * @param mixed $value Property value
	 * @throws RuntimeException If the requested static property does not exist.
	 * @throws RuntimeException If the requested static property is not accessible.
	 */
	function setStaticPropertyValue($name, $value)
	{
		// TODO: Implement setStaticPropertyValue() method.
	}


	/**
	 * Returns if the class definition is complete.
	 *
	 * That means if there are no dummy classes among parents and implemented interfaces.
	 *
	 * @return bool
	 */
	function isComplete()
	{
		// TODO: Implement isComplete() method.
	}


	/**
	 * Returns if the class is deprecated.
	 *
	 * @return bool
	 */
	function isDeprecated()
	{
		// TODO: Implement isDeprecated() method.
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
	function getPrettyName()
	{
		// TODO: Implement getPrettyName() method.
	}


	/**
	 * Returns if the reflection object is internal.
	 *
	 * @return bool
	 */
	function isInternal()
	{
		// TODO: Implement isInternal() method.
	}


	/**
	 * Returns if the reflection object is user defined.
	 *
	 * @return bool
	 */
	function isUserDefined()
	{
		// TODO: Implement isUserDefined() method.
	}


	/**
	 * Returns if the current reflection comes from a tokenized source.
	 *
	 * @return bool
	 */
	function isTokenized()
	{
		// TODO: Implement isTokenized() method.
	}

}
