<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection;


interface ReflectionMethodInterface extends ReflectionFunctionBaseInterface
{

	/**
	 * Returns an element pretty (docblock compatible) name.
	 *
	 * @return string
	 */
	function getPrettyName();


	/**
	 * Returns the declaring class reflection.
	 *
	 * @return ReflectionClassInterface|NULL
	 */
	function getDeclaringClass();


	/**
	 * Returns the declaring class name.
	 *
	 * @return string|NULL
	 */
	function getDeclaringClassName();


	/**
	 * Returns method modifiers.
	 *
	 * @return int
	 */
	function getModifiers();


	/**
	 * Returns if the method is abstract.
	 *
	 * @return bool
	 */
	function isAbstract();


	/**
	 * Returns if the method is final.
	 *
	 * @return bool
	 */
	function isFinal();


	/**
	 * Returns if the method is private.
	 *
	 * @return bool
	 */
	function isPrivate();


	/**
	 * Returns if the method is protected.
	 *
	 * @return bool
	 */
	function isProtected();


	/**
	 * Returns if the method is public.
	 *
	 * @return bool
	 */
	function isPublic();


	/**
	 * Returns if the method is static.
	 *
	 * @return bool
	 */
	function isStatic();


	/**
	 * Shortcut for isPublic(), ... methods that allows or-ed modifiers.
	 *
	 * @param int $filter Filter
	 * @return bool
	 */
	function is($filter = NULL);


	/**
	 * Returns if the method is a constructor.
	 *
	 * @return bool
	 */
	function isConstructor();


	/**
	 * Returns if the method is a destructor.
	 *
	 * @return bool
	 */
	function isDestructor();


	/**
	 * Sets a method to be accessible or not.
	 *
	 * @param bool $accessible If the method should be accessible.
	 */
	function setAccessible($accessible);


	/**
	 * Returns the original name when importing from a trait.
	 *
	 * @return string|null
	 */
	function getOriginalName();


	/**
	 * Returns the original method when importing from a trait.
	 *
	 * @return ReflectionMethodInterface|null
	 */
	function getOriginal();


	/**
	 * Returns the original modifiers value when importing from a trait.
	 *
	 * @return int|null
	 */
	function getOriginalModifiers();


	/**
	 * Returns the defining trait.
	 *
	 * @return ReflectionClassInterface|null
	 */
	function getDeclaringTrait();


	/**
	 * Returns the declaring trait name.
	 *
	 * @return string|null
	 */
	function getDeclaringTraitName();

}
