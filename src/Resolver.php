<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection;

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
	 * Returns a fully qualified name of a class using imported/aliased namespaces.
	 *
	 * @param string $className Input class name
	 * @param array $aliases Namespace import aliases
	 * @param string $namespaceName Context namespace name
	 * @return string
	 */
	final public static function resolveClassFQN($className, array $aliases, $namespaceName = NULL)
	{
		if ($className{0} == '\\') {
			// FQN
			return ltrim($className, '\\');
		}
		if (FALSE === ($position = strpos($className, '\\'))) {
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
		return NULL === $namespaceName || '' === $namespaceName || $namespaceName === ReflectionNamespace::NO_NAMESPACE_NAME ? $className : $namespaceName . '\\' . $className;
	}


	/**
	 * Returns a property/parameter/constant/static variable value definition.
	 *
	 * @param array $tokens Tokenized definition
	 * @param ApiGen\TokenReflection\ReflectionElement $reflection Caller reflection
	 * @return string
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If an invalid reflection object was provided.
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If an invalid source code was provided.
	 */
	final public static function getValueDefinition(array $tokens, ReflectionElement $reflection)
	{
		if ($reflection instanceof ReflectionConstant || $reflection instanceof ReflectionFunction) {
			$namespace = $reflection->getNamespaceName();
		} elseif ($reflection instanceof ReflectionParameter) {
			$namespace = $reflection->getDeclaringFunction()->getNamespaceName();
		} elseif ($reflection instanceof ReflectionProperty || $reflection instanceof ReflectionMethod) {
			$namespace = $reflection->getDeclaringClass()->getNamespaceName();
		} else {
			throw new Exception\RuntimeException('Invalid reflection object given.', Exception\RuntimeException::INVALID_ARGUMENT, $reflection);
		}
		// Process __LINE__ constants; replace with the line number of the corresponding token
		foreach ($tokens as $index => $token) {
			if (T_LINE === $token[0]) {
				$tokens[$index] = [
					T_LNUMBER,
					$token[2],
					$token[2]
				];
			}
		}
		$source = self::getSourceCode($tokens);
		$constants = self::findConstants($tokens, $reflection);
		if (!empty($constants)) {
			foreach (array_reverse($constants, TRUE) as $offset => $constant) {
				$value = '';
				try {
					switch ($constant) {
						case '__FILE__':
							$value = $reflection->getFileName();
							break;
						case '__DIR__':
							$value = dirname($reflection->getFileName());
							break;
						case '__FUNCTION__':
							if ($reflection instanceof IReflectionParameter) {
								$value = $reflection->getDeclaringFunctionName();
							} elseif ($reflection instanceof IReflectionFunctionBase) {
								$value = $reflection->getName();
							}
							break;
						case '__CLASS__':
							if ($reflection instanceof IReflectionConstant || $reflection instanceof IReflectionParameter || $reflection instanceof IReflectionProperty || $reflection instanceof IReflectionMethod) {
								$value = $reflection->getDeclaringClassName() ?: '';
							}
							break;
						case '__TRAIT__':
							if ($reflection instanceof IReflectionMethod || $reflection instanceof IReflectionProperty) {
								$value = $reflection->getDeclaringTraitName() ?: '';
							} elseif ($reflection instanceof IReflectionParameter) {
								$method = $reflection->getDeclaringFunction();
								if ($method instanceof IReflectionMethod) {
									$value = $method->getDeclaringTraitName() ?: '';
								}
							}
							break;
						case '__METHOD__':
							if ($reflection instanceof IReflectionParameter) {
								if (NULL !== $reflection->getDeclaringClassName()) {
									$value = $reflection->getDeclaringClassName() . '::' . $reflection->getDeclaringFunctionName();
								} else {
									$value = $reflection->getDeclaringFunctionName();
								}
							} elseif ($reflection instanceof IReflectionConstant || $reflection instanceof IReflectionProperty) {
								$value = $reflection->getDeclaringClassName() ?: '';
							} elseif ($reflection instanceof IReflectionMethod) {
								$value = $reflection->getDeclaringClassName() . '::' . $reflection->getName();
							} elseif ($reflection instanceof IReflectionFunction) {
								$value = $reflection->getName();
							}
							break;
						case '__NAMESPACE__':
							if (($reflection instanceof IReflectionConstant && NULL !== $reflection->getDeclaringClassName()) || $reflection instanceof IReflectionProperty) {
								$value = $reflection->getDeclaringClass()->getNamespaceName();
							} elseif ($reflection instanceof IReflectionParameter) {
								if (NULL !== $reflection->getDeclaringClassName()) {
									$value = $reflection->getDeclaringClass()->getNamespaceName();
								} else {
									$value = $reflection->getDeclaringFunction()->getNamespaceName();
								}
							} elseif ($reflection instanceof IReflectionMethod) {
								$value = $reflection->getDeclaringClass()->getNamespaceName();
							} else {
								$value = $reflection->getNamespaceName();
							}
							break;
						default:
							if (0 === stripos($constant, 'self::') || 0 === stripos($constant, 'parent::')) {
								// Handle self:: and parent:: definitions
								if ($reflection instanceof ReflectionConstant && NULL === $reflection->getDeclaringClassName()) {
									throw new Exception\RuntimeException('Top level constants cannot use self:: and parent:: references.', Exception\RuntimeException::UNSUPPORTED, $reflection);
								} elseif ($reflection instanceof ReflectionParameter && NULL === $reflection->getDeclaringClassName()) {
									throw new Exception\RuntimeException('Function parameters cannot use self:: and parent:: references.', Exception\RuntimeException::UNSUPPORTED, $reflection);
								}
								if (0 === stripos($constant, 'self::')) {
									$className = $reflection->getDeclaringClassName();
								} else {
									$declaringClass = $reflection->getDeclaringClass();
									$className = $declaringClass->getParentClassName() ?: self::CONSTANT_NOT_FOUND;
								}
								$constantName = $className . substr($constant, strpos($constant, '::'));
							} else {
								$constantName = self::resolveClassFQN($constant, $reflection->getNamespaceAliases(), $namespace);
								if ($cnt = strspn($constant, '\\')) {
									$constantName = str_repeat('\\', $cnt) . $constantName;
								}
							}
							$constantReflection = $reflection->getBroker()->getConstant($constantName);
							$value = $constantReflection->getValue();
					}
				} catch (Exception\RuntimeException $e) {
					$value = self::CONSTANT_NOT_FOUND;
				}
				$source = substr_replace($source, var_export($value, TRUE), $offset, strlen($constant));
			}
		}
		return self::evaluate(sprintf("return %s;\n", $source));
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
			return NULL;
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
	 * @param ApiGen\TokenReflection\ReflectionElement $reflection Caller reflection
	 * @return array
	 */
	final public static function findConstants(array $tokens, ReflectionElement $reflection)
	{
		static $accepted = [
			T_DOUBLE_COLON => TRUE,
			T_STRING => TRUE,
			T_NS_SEPARATOR => TRUE,
			T_CLASS_C => TRUE,
			T_DIR => TRUE,
			T_FILE => TRUE,
			T_LINE => TRUE,
			T_FUNC_C => TRUE,
			T_METHOD_C => TRUE,
			T_NS_C => TRUE,
			T_TRAIT_C => TRUE
		];
		static $dontResolve = ['true' => TRUE, 'false' => TRUE, 'null' => TRUE];
		// Adding a dummy token to the end
		$tokens[] = [NULL];
		$constants = [];
		$constant = '';
		$offset = 0;
		foreach ($tokens as $token) {
			if (isset($accepted[$token[0]])) {
				$constant .= $token[1];
			} elseif ('' !== $constant) {
				if (!isset($dontResolve[strtolower($constant)])) {
					$constants[$offset - strlen($constant)] = $constant;
				}
				$constant = '';
			}
			if (NULL !== $token[0]) {
				$offset += strlen($token[1]);
			}
		}
		return $constants;
	}


	/**
	 * Evaluates a source code.
	 *
	 * @param string $source Source code
	 * @return mixed
	 */
	final private static function evaluate($source)
	{
		return eval($source);
	}

}
