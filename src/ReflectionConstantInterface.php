<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection;


interface ReflectionConstantInterface extends ReflectionInterface
{

	/**
	 * Returns the unqualified name (UQN).
	 *
	 * @return string
	 */
	function getShortName();


	/**
	 * Returns the declaring class reflection.
	 *
	 * @return ReflectionClassInterface
	 */
	function getDeclaringClass();


	/**
	 * Returns the declaring class name.
	 *
	 * @return string
	 */
	function getDeclaringClassName();


	/**
	 * Returns the namespace name.
	 *
	 * @return string
	 */
	function getNamespaceName();


	/**
	 * Returns if the constant is defined within a namespace.
	 *
	 * @return bool
	 */
	function inNamespace();


	/**
	 * Returns imported namespaces and aliases from the declaring namespace.
	 *
	 * @return array
	 */
	function getNamespaceAliases();


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
	 * Returns the constant value.
	 *
	 * @return mixed
	 */
	function getValue();


	/**
	 * Returns the part of the source code defining the constant value.
	 *
	 * @return string
	 */
	function getValueDefinition();


	/**
	 * Returns if the constant is deprecated.
	 *
	 * @return bool
	 */
	function isDeprecated();

}
