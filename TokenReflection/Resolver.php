<?php
/**
 * PHP Token Reflection
 *
 * Version 1.0 beta 2
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this library in the file license.txt.
 *
 * @author Ondřej Nešpor <andrew@andrewsville.cz>
 * @author Jaroslav Hanslík <kukulich@kukulich.cz>
 */

namespace TokenReflection;

/**
 * TokenReflection Resolver class.
 */
class Resolver
{
	/**
	 * Constructor.
	 *
	 * Prevents from creating instances.
	 *
	 * @throws LogicException When trying to create a class instance
	 */
	final public function __construct()
	{
		throw new \LogicException('Static class cannot be instantiated.');
	}

	/**
	 * Returns a fully qualified name of a class using imported/aliased namespaces.
	 *
	 * @param string $className Input class name
	 * @param array $aliases Namespace import aliases
	 * @param string $namespaceName Context namespace name
	 * @return string
	 */
	final public static function resolveClassFQN($className, array $aliases, $namespaceName = null)
	{
		if ($className{0} == '\\') {
			// FQN
			return ltrim($className, '\\');
		}

		if (false === ($position = strpos($className, '\\'))) {
			// Plain class name
			if (isset($aliases[$className])) {
				return $aliases[$className];
			}
		} else {
			// Namespaced class name
			$alias = substr($className, 0, $position);
			if (isset($aliases[$alias])) {
				return $aliases[$alias] . '\\' . substr($className, $position + 1);
			}
		}

		return null === $namespaceName || '' === $namespaceName || $namespaceName === ReflectionNamespace::NO_NAMESPACE_NAME ? $className : $namespaceName . '\\' . $className;
	}
}
