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
	 * @return mixed
	 */
	public function invoke();

	/**
	 * Calls the function.
	 *
	 * @param mixed $args Function parameter values
	 * @return mixed
	 */
	public function invokeArgs(array $args);
}
