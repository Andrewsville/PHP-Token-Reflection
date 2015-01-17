<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection;


interface ReflectionExtensionInterface extends ReflectionInterface
{

	/**
	 * Returns a class reflection.
	 *
	 * @param string $name
	 * @return ReflectionClassInterface|NULL
	 */
	function getClass($name);


	/**
	 * Returns reflections of classes defined by this extension.
	 *
	 * @return array
	 */
	function getClasses();


	/**
	 * Returns class names defined by this extension.
	 *
	 * @return array
	 */
	function getClassNames();


	/**
	 * Returns a constant reflection.
	 *
	 * @param string $name Constant name
	 * @return ApiGen\TokenReflection\IReflectionConstant
	 */
	function getConstantReflection($name);


	/**
	 * Returns reflections of constants defined by this extension.
	 *
	 * This method has this name just for consistence with the rest of reflection.
	 *
	 * @return array
	 * @see ApiGen\TokenReflection\IReflectionExtension::getConstantReflections()
	 */
	function getConstantReflections();


	/**
	 * Returns a constant value.
	 *
	 * @param string $name Constant name
	 * @return mixed|false
	 */
	function getConstant($name);


	/**
	 * Returns values of constants defined by this extension.
	 *
	 * This method exists just for consistence with the rest of reflection.
	 *
	 * @return array
	 */
	function getConstants();


	/**
	 * Returns a function reflection.
	 *
	 * @param string $name Function name
	 * @return ApiGen\TokenReflection\IReflectionFunction
	 */
	function getFunction($name);


	/**
	 * Returns reflections of functions defined by this extension.
	 *
	 * @return array
	 */
	function getFunctions();


	/**
	 * Returns function names defined by this extension.
	 *
	 * @return array
	 */
	function getFunctionNames();

}
