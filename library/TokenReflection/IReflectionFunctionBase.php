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
 * Common reflection function\method interface.
 */
interface IReflectionFunctionBase extends IReflection
{
	/**
	 * Returns the appropriate docblock definition.
	 *
	 * @return string|boolean
	 */
	public function getDocComment();

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
	 * Returns the PHP extension reflection.
	 *
	 * @return \TokenReflection\IReflectionExtension|null
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
	 * Returns the namespace name.
	 *
	 * @return string
	 */
	public function getNamespaceName();

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
	 * Returns function/method parameters.
	 *
	 * @return array
	 */
	public function getParameters();

	/**
	 * Returns a function/method parameter.
	 *
	 * @param integer|string $parameter Parameter name or position
	 * @return \TokenReflection\IReflectionParameter
	 */
	public function getParameter($parameter);

	/**
	 * Returns static variables.
	 *
	 * @return array
	 */
	public function getStaticVariables();

	/**
	 * Returns if the function/method is defined within a namespace.
	 *
	 * @return boolean
	 */
	public function inNamespace();

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
}
