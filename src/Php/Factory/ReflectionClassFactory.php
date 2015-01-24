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
use ApiGen\TokenReflection\ReflectionClassInterface;
use Reflector;
use ReflectionClass as InternalReflectionClass;


class ReflectionClassFactory
{

	/**
	 * @return ReflectionClassInterface|NULL
	 */
	public static function create(Reflector $internalReflection, StorageInterface $storage)
	{
		if ( ! $internalReflection instanceof InternalReflectionClass) {
			throw new RuntimeException('Invalid reflection instance provided, ReflectionClass expected.');
		}
		return $storage->getClass($internalReflection->getName());
	}

}
