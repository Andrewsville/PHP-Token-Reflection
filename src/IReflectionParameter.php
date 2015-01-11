<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection;

/**
 * Common reflection parameter interface.
 */
interface IReflectionParameter extends IReflection
{

	/**
	 * Returns the declaring class.
	 *
	 * @return ApiGen\TokenReflection\IReflectionClass|null
	 */
	public function getDeclaringClass();


	/**
	 * Returns the declaring class name.
	 *
	 * @return string|null
	 */
	public function getDeclaringClassName();


	/**
	 * Returns the declaring function.
	 *
	 * @return ApiGen\TokenReflection\IReflectionFunctionBase
	 */
	public function getDeclaringFunction();


	/**
	 * Returns the declaring function name.
	 *
	 * @return string
	 */
	public function getDeclaringFunctionName();


	/**
	 * Returns the definition start line number in the file.
	 *
	 * @return int
	 */
	public function getStartLine();


	/**
	 * Returns the definition end line number in the file.
	 *
	 * @return int
	 */
	public function getEndLine();


	/**
	 * Returns the appropriate docblock definition.
	 *
	 * @return string|bool
	 */
	public function getDocComment();


	/**
	 * Returns the default value.
	 *
	 * @return mixed
	 */
	public function getDefaultValue();


	/**
	 * Returns the part of the source code defining the paramter default value.
	 *
	 * @return string
	 */
	public function getDefaultValueDefinition();


	/**
	 * Retutns if a default value for the parameter is available.
	 *
	 * @return bool
	 */
	public function isDefaultValueAvailable();


	/**
	 * Returns if the default value is defined by a constant.
	 *
	 * @return bool
	 */
	public function isDefaultValueConstant();


	/**
	 * Returns the name of the default value constant.
	 *
	 * @return string
	 */
	public function getDefaultValueConstantName();


	/**
	 * Returns the position within all parameters.
	 *
	 * @return int
	 */
	public function getPosition();


	/**
	 * Returns if the parameter expects an array.
	 *
	 * @return bool
	 */
	public function isArray();


	/**
	 * Returns reflection of the required class of the value.
	 *
	 * @return ApiGen\TokenReflection\IReflectionClass|null
	 */
	public function getClass();


	/**
	 * Returns the required class name of the value.
	 *
	 * @return string|null
	 */
	public function getClassName();


	/**
	 * Returns if the the parameter allows NULL.
	 *
	 * @return bool
	 */
	public function allowsNull();


	/**
	 * Returns if the parameter is optional.
	 *
	 * @return bool
	 */
	public function isOptional();


	/**
	 * Returns if the parameter value is passed by reference.
	 *
	 * @return bool
	 */
	public function isPassedByReference();


	/**
	 * Returns if the paramter value can be passed by value.
	 *
	 * @return bool
	 */
	public function canBePassedByValue();


	/**
	 * Returns the string representation of the reflection object.
	 *
	 * @return string
	 */
	public function __toString();

}
