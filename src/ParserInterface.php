<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection;

use ApiGen\TokenReflection\Reflection\ReflectionFile;
use ApiGen\TokenReflection\Storage\StorageInterface;


interface ParserInterface
{

	/**
	 * @param string $path
	 * @return ReflectionFile[]
	 */
	function parseDirectory($path);


	/**
	 * @param string $path
	 * @return ReflectionFile
	 */
	function parseFile($path);


	/**
	 * @return StorageInterface
	 */
	function getStorage();

}
