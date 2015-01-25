<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Behaviors;


interface ExtensionInterface
{

	/**
	 * Returns the PHP extension reflection.
	 *
	 * @return NULL
	 */
	function getExtension();


	/**
	 * Returns the PHP extension name.
	 *
	 * @return bool
	 */
	function getExtensionName();

}
