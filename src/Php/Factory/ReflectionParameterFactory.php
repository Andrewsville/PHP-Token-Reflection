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
use ApiGen\TokenReflection\Php\ReflectionParameter;
use ApiGen\TokenReflection\ReflectionParameterInterface;
use Reflector;
use ReflectionParameter as InternalReflectionParameter;


class ReflectionParameterFactory
{

	/**
	 * @var ReflectionParameterInterface[]
	 */
	public static $cache;


	/**
	 * @return ReflectionParameterInterface
	 */
	public static function create(Reflector $internalReflection, StorageInterface $storage)
	{
		if ( ! $internalReflection instanceof InternalReflectionParameter) {
			throw new RuntimeException('Invalid reflection instance provided, ReflectionParameter expected.');
		}

		$class = $internalReflection->getDeclaringClass();
		$function = $internalReflection->getDeclaringFunction();
		$key = $class ? $class->getName() . '::' : '';
		$key .= $function->getName() . '(' . $internalReflection->getName() . ')';
		if ( ! isset(self::$cache[$key])) {
			$name = $class ? [$class->getName(), $function->getName()] : $function->getName();
			self::$cache[$key] = new ReflectionParameter($name, $internalReflection->getName(), $storage, $function);
		}
		return self::$cache[$key];
	}

}
