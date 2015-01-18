<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Parser;

use ApiGen\TokenReflection\Reflection\ReflectionMethod;
use ReflectionMethod as InternalReflectionMethod;


class MethodParser extends FunctionBaseParser
{

	/**
	 * @return int
	 */
	public function parseBaseModifiers()
	{
		$modifiers = 0;
		while (TRUE) {
			switch ($this->tokenStream->getType()) {
				case T_ABSTRACT:
					$modifiers |= InternalReflectionMethod::IS_ABSTRACT;
					break;
				case T_FINAL:
					$modifiers |= InternalReflectionMethod::IS_FINAL;
					break;
				case T_PUBLIC:
					$modifiers |= InternalReflectionMethod::IS_PUBLIC;
					break;
				case T_PRIVATE:
					$modifiers |= InternalReflectionMethod::IS_PRIVATE;
					break;
				case T_PROTECTED:
					$modifiers |= InternalReflectionMethod::IS_PROTECTED;
					break;
				case T_STATIC:
					$modifiers |= InternalReflectionMethod::IS_STATIC;
					break;
				case T_FUNCTION:
				case NULL:
					break 2;
				default:
					break;
			}
			$this->tokenStream->skipWhitespaces();
		}
		if ( ! ($modifiers & (InternalReflectionMethod::IS_PRIVATE | InternalReflectionMethod::IS_PROTECTED))) {
			$modifiers |= InternalReflectionMethod::IS_PUBLIC;
		}
		return $modifiers;
	}


	/**
	 * @param int $modifiers
	 * @return int
	 */
	public function parseInternalModifiers($modifiers)
	{
		$class = $this->parent;
		$name = strtolower($this->reflectionFunction->getName());
		if ('__construct' === $name || ( ! $class->inNamespace() && strtolower($class->getShortName()) === $name)) {
			$modifiers |= ReflectionMethod::IS_CONSTRUCTOR;

		} elseif ('__destruct' === $name) {
			$modifiers |= ReflectionMethod::IS_DESTRUCTOR;

		} elseif ('__clone' === $name) {
			$modifiers |= ReflectionMethod::IS_CLONE;
		}

		if ($class->isInterface()) {
			$modifiers |= InternalReflectionMethod::IS_ABSTRACT;

		} else {
			static $notAllowed = ['__clone' => TRUE, '__tostring' => TRUE, '__get' => TRUE, '__set' => TRUE, '__isset' => TRUE, '__unset' => TRUE];
			if ( ! $this->reflectionFunction->isStatic() && !$this->reflectionFunction->isConstructor() && !$this->reflectionFunction->isDestructor() && !isset($notAllowed[$name])) {
				$modifiers |= ReflectionMethod::IS_ALLOWED_STATIC;
			}
		}
		return $modifiers;
	}

}
