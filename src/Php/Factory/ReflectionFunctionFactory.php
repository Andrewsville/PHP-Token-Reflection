<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Php\Factory;

use ApiGen\TokenReflection\Broker\StorageInterface;
use ApiGen\TokenReflection\Exception\RuntimeException;
use ApiGen\TokenReflection\Php\ReflectionFunction;
use Reflector;
use ReflectionFunction as InternalReflectionFunction;


class ReflectionFunctionFactory
{

	/**
	 * @return ReflectionFunction
	 */
	public static function create(Reflector $internalReflection, StorageInterface $storage)
	{
		if ( ! $internalReflection instanceof InternalReflectionFunction) {
			throw new RuntimeException('Invalid reflection instance provided, ReflectionFunction expected.');
		}

		return $storage->getFunction($internalReflection->getName());
	}

}
