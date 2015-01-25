<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Php\Factory;

use ApiGen\TokenReflection\Storage\StorageInterface;
use ApiGen\TokenReflection\Exception\RuntimeException;
use ApiGen\TokenReflection\Php\ReflectionProperty;
use ApiGen\TokenReflection\ReflectionParameterInterface;
use ApiGen\TokenReflection\ReflectionPropertyInterface;
use Reflector;
use ReflectionProperty as InternalReflectionProperty;


class ReflectionPropertyFactory
{

	/**
	 * @var ReflectionPropertyInterface[]
	 */
	public static $cache;


	/**
	 * @return ReflectionParameterInterface
	 */
	public static function create(Reflector $internalReflection, StorageInterface $storage)
	{
		if ( ! $internalReflection instanceof InternalReflectionProperty) {
			throw new RuntimeException('Invalid reflection instance provided, ReflectionProperty expected.');
		}

		$key = $internalReflection->getDeclaringClass()->getName() . '::' . $internalReflection->getName();
		if ( ! isset(self::$cache[$key])) {
			self::$cache[$key] = new ReflectionProperty($internalReflection->getDeclaringClass()->getName(), $internalReflection->getName(), $storage);
		}
		return self::$cache[$key];
	}

}
