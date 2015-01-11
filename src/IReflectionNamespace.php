<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection;

/**
 * Common reflection namespace interface.
 */
interface IReflectionNamespace extends IReflection
{

	/**
	 * Returns if the namespace contains a class of the given name.
	 *
	 * @param string $className Class name
	 * @return boolean
	 */
	public function hasClass($className);


	/**
	 * Return a class reflection.
	 *
	 * @param string $className Class name
	 * @return ApiGen\TokenReflection\IReflectionClass
	 */
	public function getClass($className);


	/**
	 * Returns class reflections.
	 *
	 * @return array
	 */
	public function getClasses();


	/**
	 * Returns class names (FQN).
	 *
	 * @return array
	 */
	public function getClassNames();


	/**
	 * Returns class unqualified names (UQN).
	 *
	 * @return array
	 */
	public function getClassShortNames();


	/**
	 * Returns if the namespace contains a constant of the given name.
	 *
	 * @param string $constantName Constant name
	 * @return boolean
	 */
	public function hasConstant($constantName);


	/**
	 * Returns a constant reflection.
	 *
	 * @param string $constantName Constant name
	 * @return ApiGen\TokenReflection\IReflectionConstant
	 */
	public function getConstant($constantName);


	/**
	 * Returns constant reflections.
	 *
	 * @return array
	 */
	public function getConstants();


	/**
	 * Returns constant names (FQN).
	 *
	 * @return array
	 */
	public function getConstantNames();


	/**
	 * Returns constant unqualified names (UQN).
	 *
	 * @return array
	 */
	public function getConstantShortNames();


	/**
	 * Returns if the namespace contains a function of the given name.
	 *
	 * @param string $functionName Function name
	 * @return boolean
	 */
	public function hasFunction($functionName);


	/**
	 * Returns a function reflection.
	 *
	 * @param string $functionName Function name
	 * @return ApiGen\TokenReflection\IReflectionFunction
	 */
	public function getFunction($functionName);


	/**
	 * Returns function reflections.
	 *
	 * @return array
	 */
	public function getFunctions();


	/**
	 * Returns function names (FQN).
	 *
	 * @return array
	 */
	public function getFunctionNames();


	/**
	 * Returns function unqualified names (UQN).
	 *
	 * @return array
	 */
	public function getFunctionShortNames();


	/**
	 * Returns the string representation of the reflection object.
	 *
	 * @return string
	 */
	public function __toString();
}
