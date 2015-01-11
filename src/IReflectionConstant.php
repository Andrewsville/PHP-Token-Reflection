<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection;

/**
 * Common reflection constant interface.
 */
interface IReflectionConstant extends IReflection
{

	/**
	 * Returns the unqualified name (UQN).
	 *
	 * @return string
	 */
	public function getShortName();


	/**
	 * Returns the declaring class reflection.
	 *
	 * @return ApiGen\TokenReflection\IReflectionClass
	 */
	public function getDeclaringClass();


	/**
	 * Returns the declaring class name.
	 *
	 * @return string
	 */
	public function getDeclaringClassName();


	/**
	 * Returns the namespace name.
	 *
	 * @return string
	 */
	public function getNamespaceName();


	/**
	 * Returns if the constant is defined within a namespace.
	 *
	 * @return bool
	 */
	public function inNamespace();


	/**
	 * Returns imported namespaces and aliases from the declaring namespace.
	 *
	 * @return array
	 */
	public function getNamespaceAliases();


	/**
	 * Returns the file name the reflection object is defined in.
	 *
	 * @return string
	 */
	public function getFileName();


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
	 * Returns the constant value.
	 *
	 * @return mixed
	 */
	public function getValue();


	/**
	 * Returns the part of the source code defining the constant value.
	 *
	 * @return string
	 */
	public function getValueDefinition();


	/**
	 * Returns the string representation of the reflection object.
	 *
	 * @return string
	 */
	public function __toString();


	/**
	 * Returns if the constant definition is valid.
	 *
	 * That means that the source code is valid and the constant name is unique within parsed files.
	 *
	 * @return bool
	 */
	public function isValid();


	/**
	 * Returns if the constant is deprecated.
	 *
	 * @return bool
	 */
	public function isDeprecated();

}
