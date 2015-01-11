<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection;

/**
 * Common reflection function\method interface.
 */
interface IReflectionFunctionBase extends IReflection
{

	/**
	 * Returns the namespace name.
	 *
	 * @return string
	 */
	public function getNamespaceName();


	/**
	 * Returns if the function/method is defined within a namespace.
	 *
	 * @return boolean
	 */
	public function inNamespace();


	/**
	 * Returns the PHP extension reflection.
	 *
	 * @return ApiGen\TokenReflection\IReflectionExtension|null
	 */
	public function getExtension();


	/**
	 * Returns the PHP extension name.
	 *
	 * @return string|null
	 */
	public function getExtensionName();


	/**
	 * Returns the file name the reflection object is defined in.
	 *
	 * @return string
	 */
	public function getFileName();


	/**
	 * Returns the definition start line number in the file.
	 *
	 * @return integer
	 */
	public function getStartLine();


	/**
	 * Returns the definition end line number in the file.
	 *
	 * @return integer
	 */
	public function getEndLine();


	/**
	 * Returns the appropriate docblock definition.
	 *
	 * @return string|boolean
	 */
	public function getDocComment();


	/**
	 * Returns if the function/method is a closure.
	 *
	 * @return boolean
	 */
	public function isClosure();


	/**
	 * Returns if the function/method is deprecated.
	 *
	 * @return boolean
	 */
	public function isDeprecated();


	/**
	 * Returns if the function/method returns its value as reference.
	 *
	 * @return boolean
	 */
	public function returnsReference();


	/**
	 * Returns a function/method parameter.
	 *
	 * @param integer|string $parameter Parameter name or position
	 * @return ApiGen\TokenReflection\IReflectionParameter
	 */
	public function getParameter($parameter);


	/**
	 * Returns function/method parameters.
	 *
	 * @return array
	 */
	public function getParameters();


	/**
	 * Returns the number of parameters.
	 *
	 * @return integer
	 */
	public function getNumberOfParameters();


	/**
	 * Returns the number of required parameters.
	 *
	 * @return integer
	 */
	public function getNumberOfRequiredParameters();


	/**
	 * Returns static variables.
	 *
	 * @return array
	 */
	public function getStaticVariables();
}
