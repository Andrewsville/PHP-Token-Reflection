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
 * Common reflection method interface.
 */
interface IReflectionMethod extends IReflectionFunctionBase
{
	/**
	 * Returns the declaring class reflection.
	 *
	 * @return \TokenReflection\IReflectionClass|null
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
	 * Returns the method prototype.
	 *
	 * @return \TokenReflection\IReflectionMethod
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
	 * Returns if the method is abstract.
	 *
	 * @return boolean
	 */
	public function isAbstract();

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
	public function is($filter = null);

	/**
	 * Sets a method to be accessible or not.
	 *
	 * @param boolean $accessible If the method should be accessible.
	 * @return boolean
	 */
	public function setAccessible($accessible);
}
