<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Behaviors;


interface AnnotationsInterface
{

	/**
	 * Checks if there is a particular annotation.
	 *
	 * @param string $name
	 * @return bool
	 */
	function hasAnnotation($name);


	/**
 	 * Returns a particular annotation value.
	 *
	 * @param string $name
	 * @return string|NULL
	 */
	function getAnnotation($name);


	/**
 	 * Returns parsed docblock.
	 *
	 * @return array
	 */
	function getAnnotations();

}
