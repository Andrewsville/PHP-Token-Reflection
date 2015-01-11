<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection;


interface IReflectionNamespace extends IReflection
{

	/**
	 * Returns if the namespace contains a class of the given name.
	 *
	 * @param string $className Class name
	 * @return bool
	 */
	function hasClass($className);


	/**
	 * Return a class reflection.
	 *
	 * @param string $className Class name
	 * @return ApiGen\TokenReflection\IReflectionClass
	 */
	function getClass($className);


	/**
	 * Returns class reflections.
	 *
	 * @return array
	 */
	function getClasses();


	/**
	 * Returns class names (FQN).
	 *
	 * @return array
	 */
	function getClassNames();


	/**
	 * Returns class unqualified names (UQN).
	 *
	 * @return array
	 */
	function getClassShortNames();


	/**
	 * Returns if the namespace contains a constant of the given name.
	 *
	 * @param string $constantName Constant name
	 * @return bool
	 */
	function hasConstant($constantName);


	/**
	 * Returns a constant reflection.
	 *
	 * @param string $constantName Constant name
	 * @return ApiGen\TokenReflection\IReflectionConstant
	 */
	function getConstant($constantName);


	/**
	 * Returns constant reflections.
	 *
	 * @return array
	 */
	function getConstants();


	/**
	 * Returns constant names (FQN).
	 *
	 * @return array
	 */
	function getConstantNames();


	/**
	 * Returns constant unqualified names (UQN).
	 *
	 * @return array
	 */
	function getConstantShortNames();


	/**
	 * Returns if the namespace contains a function of the given name.
	 *
	 * @param string $functionName Function name
	 * @return bool
	 */
	function hasFunction($functionName);


	/**
	 * Returns a function reflection.
	 *
	 * @param string $functionName Function name
	 * @return ApiGen\TokenReflection\IReflectionFunction
	 */
	function getFunction($functionName);


	/**
	 * Returns function reflections.
	 *
	 * @return array
	 */
	function getFunctions();


	/**
	 * Returns function names (FQN).
	 *
	 * @return array
	 */
	function getFunctionNames();


	/**
	 * Returns function unqualified names (UQN).
	 *
	 * @return array
	 */
	function getFunctionShortNames();


	/**
	 * Returns the string representation of the reflection object.
	 *
	 * @return string
	 */
	function __toString();

}
