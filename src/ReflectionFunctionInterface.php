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
	 * Returns an element pretty (docblock compatible) name.
	 *
	 * @return string
	 */
	function getPrettyName();


	/**
	 * Returns imported namespaces and aliases from the declaring namespace.
	 *
	 * @return array
	 */
	function getNamespaceAliases();

}
