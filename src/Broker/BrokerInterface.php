<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Broker;

use ApiGen\TokenReflection\Reflection\ReflectionFile;


interface BrokerInterface
{

	/**
	 * @param string $name
	 * @return ReflectionFile[]
	 */
	function processFile($name);


	/**
	 * @param string $path
	 * @return ReflectionFile[]
	 */
	function processDirectory($path);

}
