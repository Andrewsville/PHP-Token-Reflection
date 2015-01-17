<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Php;

use ApiGen\TokenReflection;
use ApiGen\TokenReflection\Broker\Broker;
use Reflector;


interface ReflectionInterface extends TokenReflection\ReflectionInterface
{

	/**
	 * @return ReflectionInterface
	 */
	static function create(Reflector $internalReflection, Broker $broker);

}