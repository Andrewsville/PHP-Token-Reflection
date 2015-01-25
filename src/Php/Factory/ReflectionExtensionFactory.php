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
use ApiGen\TokenReflection\Php\ReflectionClass;
use ApiGen\TokenReflection\Php\ReflectionExtension;
use ApiGen\TokenReflection\ReflectionExtensionInterface;
use Reflector;
use ReflectionExtension as InternalReflectionExtension;


class ReflectionExtensionFactory
{

	/**
	 * @var ReflectionExtensionInterface[]
	 */
	public static $cache = [];


	/**
	 * @return ReflectionExtensionInterface
	 */
	public static function create(Reflector $internalReflection, StorageInterface $storage)
	{
		if ( ! $internalReflection instanceof InternalReflectionExtension) {
			throw new RuntimeException('Invalid reflection instance provided, ReflectionExtension expected.');
		}

		$key = $internalReflection->getName();
		if ( ! isset(self::$cache[$key])) {
			self::$cache[$key] = new ReflectionExtension($internalReflection->getName(), $storage);
		}
		return self::$cache[$key];
	}

}
