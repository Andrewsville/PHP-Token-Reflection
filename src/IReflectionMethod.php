<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection;

/**
 * Common reflection method interface.
 */
interface IReflectionMethod extends IReflectionFunctionBase
{

	/**
	 * Returns the declaring class reflection.
	 *
	 * @return ApiGen\TokenReflection\IReflectionClass|null
	 */
	public function getDeclaringClass();


	/**
	 * Returns the declaring class name.
	 *
	 * @return string|null
	 */
	public function getDeclaringClassName();


	/**
	 * Returns method modifiers.
	 *
	 * @return integer
	 */
	public function getModifiers();


	/**
	 * Returns if the method is abstract.
	 *
	 * @return boolean
	 */
	public function isAbstract();


	/**
	 * Returns if the method is final.
	 *
	 * @return boolean
	 */
	public function isFinal();


	/**
	 * Returns if the method is private.
	 *
	 * @return boolean
	 */
	public function isPrivate();


	/**
	 * Returns if the method is protected.
	 *
	 * @return boolean
	 */
	public function isProtected();


	/**
	 * Returns if the method is public.
	 *
	 * @return boolean
	 */
	public function isPublic();


	/**
	 * Returns if the method is static.
	 *
	 * @return boolean
	 */
	public function isStatic();


	/**
	 * Shortcut for isPublic(), ... methods that allows or-ed modifiers.
	 *
	 * @param integer $filter Filter
	 * @return boolean
	 */
	public function is($filter = NULL);


	/**
	 * Returns if the method is a constructor.
	 *
	 * @return boolean
	 */
	public function isConstructor();


	/**
	 * Returns if the method is a destructor.
	 *
	 * @return boolean
	 */
	public function isDestructor();


	/**
	 * Returns the method prototype.
	 *
	 * @return ApiGen\TokenReflection\IReflectionMethod
	 */
	public function getPrototype();


	/**
	 * Calls the method on an given instance.
	 *
	 * @param object $object Class instance
	 * @param mixed $args
	 * @return mixed
	 */
	public function invoke($object, $args);


	/**
	 * Calls the method on an given object.
	 *
	 * @param object $object Class instance
	 * @param array $args Method parameter values
	 * @return mixed
	 */
	public function invokeArgs($object, array $args);


	/**
	 * Sets a method to be accessible or not.
	 *
	 * @param boolean $accessible If the method should be accessible.
	 */
	public function setAccessible($accessible);


	/**
	 * Returns the function/method as closure.
	 *
	 * @param object $object Object
	 * @return \Closure
	 */
	public function getClosure($object);


	/**
	 * Returns the original name when importing from a trait.
	 *
	 * @return string|null
	 */
	public function getOriginalName();


	/**
	 * Returns the original method when importing from a trait.
	 *
	 * @return ApiGen\TokenReflection\IReflectionMethod|null
	 */
	public function getOriginal();


	/**
	 * Returns the original modifiers value when importing from a trait.
	 *
	 * @return integer|null
	 */
	public function getOriginalModifiers();


	/**
	 * Returns the defining trait.
	 *
	 * @return ApiGen\TokenReflection\IReflectionClass|null
	 */
	public function getDeclaringTrait();


	/**
	 * Returns the declaring trait name.
	 *
	 * @return string|null
	 */
	public function getDeclaringTraitName();
}
