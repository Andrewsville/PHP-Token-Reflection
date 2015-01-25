<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection;


interface ReflectionInterface
{

	/**
	 * Returns the name (FQN).
	 *
	 * @return string
	 */
	function getName();


	/**
	 * Returns if the reflection object is internal.
	 *
	 * @return bool
	 */
	function isInternal();


	/**
	 * Returns if the reflection object is user defined.
	 *
	 * @return bool
	 */
	function isUserDefined();


	/**
	 * Returns if the current reflection comes from a tokenized source.
	 *
	 * @return bool
	 */
	function isTokenized();

}
