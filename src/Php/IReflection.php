<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Php;

use ApiGen\TokenReflection;
use Reflector;


/**
 * Basic internal reflection interface.
 *
 * Common interface for all internal reflection classes.
 */
interface IReflection extends TokenReflection\IReflection
{

	/**
	 * Creates a reflection instance.
	 *
	 * @param \Reflector $internalReflection Internal reflection instance
	 * @param ApiGen\TokenReflection\Broker $broker Reflection broker instance
	 * @return ApiGen\TokenReflection\Php\IReflection
	 */
	public static function create(Reflector $internalReflection, TokenReflection\Broker $broker);
}
