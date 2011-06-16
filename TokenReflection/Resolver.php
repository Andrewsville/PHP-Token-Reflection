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
	 * Placeholder for non-existen constants.
	 *
	 * @var null
	 */
	const CONSTANT_NOT_FOUND = '~~NOT RESOLVED~~';

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

	/**
	 * Returns a property/parameter/constant value definition.
	 *
	 * @param array $tokens Tokenized definition
	 * @param \TokenReflection\ReflectionBase $reflection Caller reflection
	 * @return string
	 */
	final public static function getValueDefinition(array $tokens, ReflectionBase $reflection)
	{
		$source = self::getSourceCode($tokens);

		$constants = self::findConstants($tokens, $reflection);
		if (!empty($constants)) {
			$replacements = array();
			foreach ($constants as $constant) {
				try {
					if (0 === stripos($constant, 'self::') || 0 === stripos($constant, 'parent::')) {
						// handle self:: and parent:: definitions

						if ($reflection instanceof ReflectionConstant) {
							throw new Exception\Runtime('Constants cannot use self:: and parent:: references.', Exception\Runtime::INVALID_ARGUMENT);
						} elseif ($reflection instanceof ReflectionParameter && null === $reflection->getDeclaringClassName()) {
							throw new Exception\Runtime('Function parameters cannot use self:: and parent:: references.', Exception\Runtime::INVALID_ARGUMENT);
						}

						if (0 === stripos($constant, 'self::')) {
							$className = $reflection->getDeclaringClassName();
						} else {
							$declaringClass = $reflection->getDeclaringClass();
							$className = $declaringClass->getParentClassName() ?: self::CONSTANT_NOT_FOUND;
						}

						$constantName = $className . substr($constant, strpos($constant, '::'));
					} else {
						$constantName = $constant;
					}

					$reflection = $reflection->getBroker()->getConstant($constantName);
					$value = $reflection->getValue();
				} catch (Exception\Runtime $e) {
					$value = self::CONSTANT_NOT_FOUND;
				}

				$replacements[$constant] = var_export($value, true);
			}
			uksort($replacements, function($a, $b) {
				$ca = strspn($a, '\\');
				$cb = strspn($b, '\\');
				return $ca === $cb ? strcasecmp($b, $a) : $cb - $ca;
			});

			$source = strtr($source, $replacements);
		}

		return eval(sprintf('return %s;', $source));
	}

	/**
	 * Returns a part of the source code defined by given tokens.
	 *
	 * @param array $tokens Tokens array
	 * @return array
	 */
	final public static function getSourceCode(array $tokens)
	{
		if (empty($tokens)) {
			return null;
		}

		$source = '';
		foreach ($tokens as $token) {
			$source .= $token[1];
		}
		return $source;
	}

	/**
	 * Finds constant names in the token definition.
	 *
	 * @param array $tokens Tokenized source code
	 * @param \TokenReflection\ReflectionBase $reflection Caller reflection
	 * @return array
	 */
	final public static function findConstants(array $tokens, ReflectionBase $reflection)
	{
		static $accepted = array(T_DOUBLE_COLON => true, T_STRING => true, T_NS_SEPARATOR => true);
		static $dontResolve = array('true' => true, 'false' => true, 'null' => true);

		if ($reflection instanceof ReflectionConstant) {
			$namespace = $reflection->getNamespaceName();
		} elseif ($reflection instanceof ReflectionParameter) {
			$namespace = $reflection->getDeclaringFunction()->getNamespaceName();
		} elseif ($reflection instanceof ReflectionProperty) {
			$namespace = $reflection->getDeclaringClass()->getNamespaceName();
		} else {
			throw new Exception\Runtime(sprintf('Invalid reflection object given: "%s" ("%s")', get_class($reflection), $reflection->getName()), Exception\Runtime::INVALID_ARGUMENT);
		}

		// Adding a dummy token to the end
		$tokens[] = array(-1);
							 ;
		$constants = array();
		$constant = '';
		foreach ($tokens as $token) {
			if (isset($accepted[$token[0]])) {
				$constant .= $token[1];
			} elseif ('' !== $constant) {
				if (!isset($dontResolve[strtolower($constant)])) {
					if (0 === stripos($constant, 'self::') || 0 === stripos($constant, 'parent::')) {
						$resolvedConstant = $constant;
					} else {
						$resolvedConstant = self::resolveClassFQN($constant, $reflection->getNamespaceAliases(), $namespace);
						if ($cnt = strspn($constant, '\\')) {
							$resolvedConstant = str_repeat('\\', $cnt) . $resolvedConstant;
						}
					}
					$constants[$resolvedConstant] = true;
				}
				$constant = '';
			}
		}
		return array_keys($constants);
	}
}
