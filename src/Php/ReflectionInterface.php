<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Php;

use ApiGen;
use ApiGen\TokenReflection\Broker\StorageInterface;
use Reflector;


interface ReflectionInterface extends ApiGen\TokenReflection\ReflectionInterface
{

	/**
	 * @return ReflectionInterface
	 */
	static function create(Reflector $internalReflection, StorageInterface $storage);

}
