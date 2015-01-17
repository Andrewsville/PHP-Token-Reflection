<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Parser;

use ApiGen\TokenReflection\ReflectionBase;
use ApiGen\TokenReflection\ReflectionClass;
use ApiGen\TokenReflection\ReflectionElement;
use ApiGen\TokenReflection\ReflectionMethod;
use ApiGen\TokenReflection\ReflectionParameter;
use ApiGen\TokenReflection\ReflectionProperty;


class AnnotationParser
{

	/**
	 * Main description annotation identifier.
	 *
	 * White space at the beginning on purpose.
	 *
	 * @var string
	 */
	const SHORT_DESCRIPTION = ' short_description';

	/**
	 * Sub description annotation identifier.
	 *
	 * White space at the beginning on purpose.
	 *
	 * @var string
	 */
	const LONG_DESCRIPTION = ' long_description';

	/**
	 * @var ReflectionBase|ReflectionClass|ReflectionMethod|ReflectionParameter
	 */
	private $reflection;

	/**
	 * @var string|bool
	 */
	private $docComment;


	/**
	 * @param ReflectionBase $reflection
	 * @param string|bool $docComment
	 */
	public function __construct(ReflectionBase $reflection, $docComment)
	{
		$this->reflection = $reflection;
		$this->docComment = $docComment;
	}


	/**
	 * @return array
	 */
	public function parse()
	{
		$annotations = [];
		if (FALSE !== $this->docComment) {
			// Parse docblock
			$name = self::SHORT_DESCRIPTION;
			$docblock = trim(
				preg_replace(['~^/\\*\\*~', '~\\*/$~'], '', $this->docComment)
			);
			foreach (explode("\n", $docblock) as $line) {
				$line = preg_replace('~^\\*\\s?~', '', trim($line));
				// End of short description
				if ('' === $line && self::SHORT_DESCRIPTION === $name) {
					$name = self::LONG_DESCRIPTION;
					continue;
				}
				// @annotation
				if (preg_match('~^\\s*@([\\S]+)\\s*(.*)~', $line, $matches)) {
					$name = $matches[1];
					$annotations[$name][] = $matches[2];
					continue;
				}
				// Continuation
				if (self::SHORT_DESCRIPTION === $name || self::LONG_DESCRIPTION === $name) {
					if ( ! isset($annotations[$name])) {
						$annotations[$name] = $line;
					} else {
						$annotations[$name] .= "\n" . $line;
					}
				} else {
					$annotations[$name][count($annotations[$name]) - 1] .= "\n" . $line;
				}
			}
			array_walk_recursive($annotations, function (&$value) {
				// {@*} is a placeholder for */ (phpDocumentor compatibility)
				$value = str_replace('{@*}', '*/', $value);
				$value = trim($value);
			});
		}

		if ($this->reflection instanceof ReflectionElement) {
			// Process docblock inheritance for supported reflections
			if ($this->reflection instanceof ReflectionClass || $this->reflection instanceof ReflectionMethod || $this->reflection instanceof ReflectionProperty) {
				$annotations = $this->inheritAnnotations($annotations);
			}
		}

		return $annotations;
	}


	/**
	 * Inherits annotations from parent classes/methods/properties if needed.
	 *
	 * @param array $annotations
	 * @return array
	 */
	private function inheritAnnotations($annotations)
	{
		if ($this->reflection instanceof ReflectionClass) {
			$declaringClass = $this->reflection;

		} else {
			$declaringClass = $this->reflection->getDeclaringClass();
		}

		$parents = array_filter(array_merge([$declaringClass->getParentClass()], $declaringClass->getOwnInterfaces()), function ($class) {
			return $class instanceof ReflectionClass;
		});
		// In case of properties and methods, look for a property/method of the same name and return
		// and array of such members.
		$parentDefinitions = [];
		if ($this->reflection instanceof ReflectionProperty) {
			$name = $this->reflection->getName();
			/** @var ReflectionClass $parent */
			foreach ($parents as $parent) {
				if ($parent->hasProperty($name)) {
					$parentDefinitions[] = $parent->getProperty($name);
				}
			}
			$parents = $parentDefinitions;

		} elseif ($this->reflection instanceof ReflectionMethod) {
			$name = $this->reflection->getName();
			/** @var ReflectionClass $parent */
			foreach ($parents as $parent) {
				if ($parent->hasMethod($name)) {
					$parentDefinitions[] = $parent->getMethod($name);
				}
			}
			$parents = $parentDefinitions;
		}

		if (FALSE === $this->docComment) {
			// Inherit the entire docblock
			foreach ($parents as $parent) {
				if (count($parent->getAnnotations())) {
					$annotations = $parent->getAnnotations();
					break;
				}
			}

		} else {
			if (isset($annotations[self::LONG_DESCRIPTION]) && FALSE !== stripos($annotations[self::LONG_DESCRIPTION], '{@inheritdoc}')) {
				// Inherit long description
				foreach ($parents as $parent) {
					if ($parent->hasAnnotation(self::LONG_DESCRIPTION)) {
						$annotations[self::LONG_DESCRIPTION] = str_ireplace(
							'{@inheritdoc}',
							$parent->getAnnotation(self::LONG_DESCRIPTION),
							$annotations[self::LONG_DESCRIPTION]
						);
						break;
					}
				}
				$annotations[self::LONG_DESCRIPTION] = str_ireplace('{@inheritdoc}', '', $annotations[self::LONG_DESCRIPTION]);
			}
			if (isset($annotations[self::SHORT_DESCRIPTION]) && FALSE !== stripos($annotations[self::SHORT_DESCRIPTION], '{@inheritdoc}')) {
				// Inherit short description
				foreach ($parents as $parent) {
					if ($parent->hasAnnotation(self::SHORT_DESCRIPTION)) {
						$annotations[self::SHORT_DESCRIPTION] = str_ireplace(
							'{@inheritdoc}',
							$parent->getAnnotation(self::SHORT_DESCRIPTION),
							$annotations[self::SHORT_DESCRIPTION]
						);
						break;
					}
				}
				$annotations[self::SHORT_DESCRIPTION] = str_ireplace('{@inheritdoc}', '', $annotations[self::SHORT_DESCRIPTION]);
			}
		}
		// In case of properties check if we need and can inherit the data type
		if ($this->reflection instanceof ReflectionProperty && empty($annotations['var'])) {
			foreach ($parents as $parent) {
				if ($parent->hasAnnotation('var')) {
					$annotations['var'] = $parent->getAnnotation('var');
					break;
				}
			}
		}

		if ($this->reflection instanceof ReflectionMethod) {
			if (0 !== $this->reflection->getNumberOfParameters() && (empty($annotations['param']) || count($annotations['param']) < $this->reflection->getNumberOfParameters())) {
				// In case of methods check if we need and can inherit parameter descriptions
				$params = isset($annotations['param']) ? $annotations['param'] : [];
				$complete = FALSE;
				foreach ($parents as $parent) {
					if ($parent->hasAnnotation('param')) {
						$parentParams = array_slice($parent->getAnnotation('param'), count($params));
						while ( ! empty($parentParams) && !$complete) {
							array_push($params, array_shift($parentParams));
							if (count($params) === $this->reflection->getNumberOfParameters()) {
								$complete = TRUE;
							}
						}
					}
					if ($complete) {
						break;
					}
				}
				if ( ! empty($params)) {
					$annotations['param'] = $params;
				}
			}

			// And check if we need and can inherit the return and throws value
			foreach (['return', 'throws'] as $paramName) {
				if ( ! isset($annotations[$paramName])) {
					foreach ($parents as $parent) {
						if ($parent->hasAnnotation($paramName)) {
							$annotations[$paramName] = $parent->getAnnotation($paramName);
							break;
						}
					}
				}
			}
		}

		return $annotations;
	}

}
