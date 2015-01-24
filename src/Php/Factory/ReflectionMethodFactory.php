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
use ApiGen\TokenReflection\Php\ReflectionMethod;
use ApiGen\TokenReflection\ReflectionMethodInterface;
use Reflector;
use ReflectionMethod as InternalReflectionMethod;


class ReflectionMethodFactory
{

	/**
	 * @var ReflectionMethodInterface[]
	 */
	public static $cache;


	/**
	 * @return ReflectionMethodInterface
	 */
	public static function create(Reflector $internalReflection, StorageInterface $storage)
	{
		if ( ! $internalReflection instanceof InternalReflectionMethod) {
			throw new RuntimeException('Invalid reflection instance provided, ReflectionMethod expected.');
		}

		$key = $internalReflection->getDeclaringClass()->getName() . '::' . $internalReflection->getName();
		if ( ! isset(self::$cache[$key])) {
			self::$cache[$key] = new ReflectionMethod($internalReflection->getDeclaringClass()->getName(), $internalReflection->getName(), $storage);
		}
		return self::$cache[$key];
	}

}
