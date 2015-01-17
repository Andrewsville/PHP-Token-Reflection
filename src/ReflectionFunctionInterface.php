<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection;


interface ReflectionFunctionInterface extends ReflectionFunctionBaseInterface
{

	/**
	 * Returns if the method is is disabled via the disable_functions directive.
	 *
	 * @return bool
	 */
	function isDisabled();


	/**
	 * Returns if the function definition is valid.
	 *
	 * That means that the source code is valid and the function name is unique within parsed files.
	 *
	 * @return bool
	 */
	function isValid();


	/**
	 * Returns imported namespaces and aliases from the declaring namespace.
	 *
	 * @return array
	 */
	function getNamespaceAliases();

}
