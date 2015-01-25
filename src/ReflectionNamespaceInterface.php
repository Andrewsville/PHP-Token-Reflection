<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection;

use ApiGen\TokenReflection\Reflection\ReflectionFileNamespace;


interface ReflectionNamespaceInterface extends ReflectionInterface
{

	/**
	 * Returns if the namespace contains a class of the given name.
	 *
	 * @param string $name Class name
	 * @return bool
	 */
	function hasClass($name);


	/**
	 * Return a class reflection.
	 *
	 * @param string $name
	 * @return ReflectionClassInterface
	 */
	function getClass($name);


	/**
	 * Returns class reflections.
	 *
	 * @return array|ReflectionClassInterface[]
	 */
	function getClasses();


	/**
	 * Returns if the namespace contains a constant of the given name.
	 *
	 * @param string $name
	 * @return bool
	 */
	function hasConstant($name);


	/**
	 * Returns a constant reflection.
	 *
	 * @param string $name
	 * @return ReflectionConstantInterface
	 */
	function getConstant($name);


	/**
	 * Returns constant reflections.
	 *
	 * @return array|ReflectionConstantInterface[]
	 */
	function getConstants();


	/**
	 * Returns if the namespace contains a function of the given name.
	 *
	 * @param string $name
	 * @return bool
	 */
	function hasFunction($name);


	/**
	 * Returns a function reflection.
	 *
	 * @param string $name
	 * @return ReflectionFunctionInterface
	 */
	function getFunction($name);


	/**
	 * Returns function reflections.
	 *
	 * @return array|ReflectionFunctionInterface[]
	 */
	function getFunctions();


	/**
	 * @return ReflectionNamespaceInterface
	 */
	function addFileNamespace(ReflectionFileNamespace $namespace);

}
