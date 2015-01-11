<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection;


interface IReflectionFunctionBase extends IReflection
{

	/**
	 * Returns the namespace name.
	 *
	 * @return string
	 */
	function getNamespaceName();


	/**
	 * Returns if the function/method is defined within a namespace.
	 *
	 * @return bool
	 */
	function inNamespace();


	/**
	 * Returns the PHP extension reflection.
	 *
	 * @return IReflectionExtension|null
	 */
	function getExtension();


	/**
	 * Returns the PHP extension name.
	 *
	 * @return string|null
	 */
	function getExtensionName();


	/**
	 * Returns the file name the reflection object is defined in.
	 *
	 * @return string
	 */
	function getFileName();


	/**
	 * Returns the definition start line number in the file.
	 *
	 * @return int
	 */
	function getStartLine();


	/**
	 * Returns the definition end line number in the file.
	 *
	 * @return int
	 */
	function getEndLine();


	/**
	 * Returns the appropriate docblock definition.
	 *
	 * @return string|bool
	 */
	function getDocComment();


	/**
	 * Returns if the function/method is a closure.
	 *
	 * @return bool
	 */
	function isClosure();


	/**
	 * Return if the function/method is variadic.
	 *
	 * @return bool
	 */
	function isVariadic();


	/**
	 * Returns if the function/method is deprecated.
	 *
	 * @return bool
	 */
	function isDeprecated();


	/**
	 * Returns if the function/method returns its value as reference.
	 *
	 * @return bool
	 */
	function returnsReference();


	/**
	 * Returns a function/method parameter.
	 *
	 * @param int|string $parameter Parameter name or position
	 * @return IReflectionParameter
	 */
	function getParameter($parameter);


	/**
	 * Returns function/method parameters.
	 *
	 * @return array
	 */
	function getParameters();


	/**
	 * Returns the number of parameters.
	 *
	 * @return int
	 */
	function getNumberOfParameters();


	/**
	 * Returns the number of required parameters.
	 *
	 * @return int
	 */
	function getNumberOfRequiredParameters();


	/**
	 * Returns static variables.
	 *
	 * @return array
	 */
	function getStaticVariables();

}
