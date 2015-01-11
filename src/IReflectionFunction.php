<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection;

/**
 * Common reflection function interface.
 */
interface IReflectionFunction extends IReflectionFunctionBase
{

	/**
	 * Returns if the method is is disabled via the disable_functions directive.
	 *
	 * @return bool
	 */
	public function isDisabled();

	/**
	 * Calls the function.
	 *
	 * This function is commented out because its actual declaration in ReflectionFunction
	 * is different in PHP 5.3.0 (http://bugs.php.net/bug.php?id=48757).
	 *
	 * If you use PHP > 5.3.0, you can uncomment it.
	 *
	 * @return mixed
	 */
	// public function invoke();
	/**
	 * Calls the function.
	 *
	 * @param array $args Function parameter values
	 * @return mixed
	 */
	public function invokeArgs(array $args);


	/**
	 * Returns the function/method as closure.
	 *
	 * @return \Closure
	 */
	public function getClosure();


	/**
	 * Returns if the function definition is valid.
	 *
	 * That means that the source code is valid and the function name is unique within parsed files.
	 *
	 * @return bool
	 */
	public function isValid();


	/**
	 * Returns imported namespaces and aliases from the declaring namespace.
	 *
	 * @return array
	 */
	public function getNamespaceAliases();

}
