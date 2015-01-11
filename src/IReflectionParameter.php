<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection;


interface IReflectionParameter extends IReflection
{

	/**
	 * Returns the declaring class.
	 *
	 * @return IReflectionClass|NULL
	 */
	function getDeclaringClass();


	/**
	 * Returns the declaring class name.
	 *
	 * @return string|null
	 */
	function getDeclaringClassName();


	/**
	 * Returns the declaring function.
	 *
	 * @return ApiGen\TokenReflection\IReflectionFunctionBase
	 */
	function getDeclaringFunction();


	/**
	 * Returns the declaring function name.
	 *
	 * @return string
	 */
	function getDeclaringFunctionName();


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
	 * Returns the default value.
	 *
	 * @return mixed
	 */
	function getDefaultValue();


	/**
	 * Returns the part of the source code defining the paramter default value.
	 *
	 * @return string
	 */
	function getDefaultValueDefinition();


	/**
	 * Retutns if a default value for the parameter is available.
	 *
	 * @return bool
	 */
	function isDefaultValueAvailable();


	/**
	 * Returns if the default value is defined by a constant.
	 *
	 * @return bool
	 */
	function isDefaultValueConstant();


	/**
	 * Returns the name of the default value constant.
	 *
	 * @return string
	 */
	function getDefaultValueConstantName();


	/**
	 * Returns the position within all parameters.
	 *
	 * @return int
	 */
	function getPosition();


	/**
	 * Returns if the parameter expects an array.
	 *
	 * @return bool
	 */
	function isArray();


	/**
	 * Returns reflection of the required class of the value.
	 *
	 * @return ApiGen\TokenReflection\IReflectionClass|null
	 */
	function getClass();


	/**
	 * Returns the required class name of the value.
	 *
	 * @return string|null
	 */
	function getClassName();


	/**
	 * Returns if the the parameter allows NULL.
	 *
	 * @return bool
	 */
	function allowsNull();


	/**
	 * Returns if the parameter is optional.
	 *
	 * @return bool
	 */
	function isOptional();


	/**
	 * Return if the parameter is variadic.
	 *
	 * @return bool
	 */
	function isVariadic();


	/**
	 * Returns if the parameter value is passed by reference.
	 *
	 * @return bool
	 */
	function isPassedByReference();


	/**
	 * Returns if the paramter value can be passed by value.
	 *
	 * @return bool
	 */
	function canBePassedByValue();


	/**
	 * Returns the string representation of the reflection object.
	 *
	 * @return string
	 */
	function __toString();

}
