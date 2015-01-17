<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection;


interface ReflectionNamespaceInterface extends ReflectionInterface
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
	 * @param string $className
	 * @return ReflectionClassInterface
	 */
	function getClass($className);


	/**
	 * Returns class reflections.
	 *
	 * @return array|ReflectionClassInterface[]
	 */
	function getClasses();


	/**
	 * Returns class names (FQN).
	 *
	 * @return array|string[]
	 */
	function getClassNames();


	/**
	 * Returns class unqualified names (UQN).
	 *
	 * @return array|string[]
	 */
	function getClassShortNames();


	/**
	 * Returns if the namespace contains a constant of the given name.
	 *
	 * @param string $constantName
	 * @return bool
	 */
	function hasConstant($constantName);


	/**
	 * Returns a constant reflection.
	 *
	 * @param string $constantName
	 * @return ReflectionConstantInterface
	 */
	function getConstant($constantName);


	/**
	 * Returns constant reflections.
	 *
	 * @return array|ReflectionConstantInterface[]
	 */
	function getConstants();


	/**
	 * Returns constant names (FQN).
	 *
	 * @return array|string[]
	 */
	function getConstantNames();


	/**
	 * Returns constant unqualified names (UQN).
	 *
	 * @return array|string[]
	 */
	function getConstantShortNames();


	/**
	 * Returns if the namespace contains a function of the given name.
	 *
	 * @param string $functionName
	 * @return bool
	 */
	function hasFunction($functionName);


	/**
	 * Returns a function reflection.
	 *
	 * @param string $functionName
	 * @return ReflectionFunctionInterface
	 */
	function getFunction($functionName);


	/**
	 * Returns function reflections.
	 *
	 * @return array|ReflectionFunctionInterface[]
	 */
	function getFunctions();


	/**
	 * Returns function names (FQN).
	 *
	 * @return array|string[]
	 */
	function getFunctionNames();


	/**
	 * Returns function unqualified names (UQN).
	 *
	 * @return array|string[]
	 */
	function getFunctionShortNames();

}
