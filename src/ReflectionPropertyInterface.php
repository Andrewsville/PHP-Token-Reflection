<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection;


interface ReflectionPropertyInterface extends ReflectionInterface
{

	/**
	 * Returns a reflection of the declaring class.
	 *
	 * @return ReflectionClassInterface
	 */
	function getDeclaringClass();


	/**
	 * Returns the name of the declaring class.
	 *
	 * @return string
	 */
	function getDeclaringClassName();


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
	 * Returns the property default value.
	 *
	 * @return mixed
	 */
	function getDefaultValue();


	/**
	 * Returns the part of the source code defining the property default value.
	 *
	 * @return string
	 */
	function getDefaultValueDefinition();


	/**
	 * Returns the property value for a particular class instance.
	 *
	 * @param object $object
	 * @return mixed
	 */
	function getValue($object);


	/**
	 * Returns property modifiers.
	 *
	 * @return int
	 */
	function getModifiers();


	/**
	 * Returns if the property is private.
	 *
	 * @return bool
	 */
	function isPrivate();


	/**
	 * Returns if the property is protected.
	 *
	 * @return bool
	 */
	function isProtected();


	/**
	 * Returns if the property is public.
	 *
	 * @return bool
	 */
	function isPublic();


	/**
	 * Returns if the property is static.
	 *
	 * @return bool
	 */
	function isStatic();


	/**
	 * Returns if the property was defined at compile time.
	 *
	 * @return bool
	 */
	function isDefault();


	/**
	 * Sets a property to be accessible or not.
	 *
	 * @param bool $accessible If the property should be accessible.
	 */
	function setAccessible($accessible);


	/**
	 * Returns if the property is set accessible.
	 *
	 * @return bool
	 */
	function isAccessible();


	/**
	 * Sets value of a property for a particular class instance.
	 *
	 * @param object $object Class instance
	 * @param mixed $value Property value
	 */
	function setValue($object, $value);


	/**
	 * Returns the defining trait.
	 *
	 * @return ReflectionClassInterface|NULL
	 */
	function getDeclaringTrait();


	/**
	 * Returns the declaring trait name.
	 *
	 * @return string|null
	 */
	function getDeclaringTraitName();


	/**
	 * Returns if the property is deprecated.
	 *
	 * @return bool
	 */
	function isDeprecated();

}
