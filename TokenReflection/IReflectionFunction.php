<?php
/**
 * PHP Token Reflection
 *
 * Version 1.0.0 beta 6
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this library in the file LICENSE.
 *
 * @author Ondřej Nešpor
 * @author Jaroslav Hanslík
 */

namespace TokenReflection;

/**
 * Common reflection function interface.
 */
interface IReflectionFunction extends IReflectionFunctionBase
{
	/**
	 * Returns if the method is is disabled via the disable_functions directive.
	 *
	 * @return boolean
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
	 * @param mixed $args Function parameter values
	 * @return mixed
	 */
	public function invokeArgs(array $args);
}
