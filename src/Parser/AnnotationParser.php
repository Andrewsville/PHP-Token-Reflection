<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Parser;

use ApiGen\TokenReflection\Behaviors\AnnotationsInterface;
use ApiGen\TokenReflection\Reflection\ReflectionBase;
use ApiGen\TokenReflection\Reflection\ReflectionClass;
use ApiGen\TokenReflection\Reflection\ReflectionMethod;
use ApiGen\TokenReflection\Reflection\ReflectionParameter;
use ApiGen\TokenReflection\Reflection\ReflectionProperty;


class AnnotationParser
{

	/**
	 * @var string
	 */
	const INHERITDOC = '{@inheritdoc}';

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
	 * @var \ApiGen\TokenReflection\Reflection\ReflectionBase|ReflectionClass|ReflectionMethod|ReflectionParameter
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
		if ($this->docComment !== FALSE) {
			// Parse docblock
			$name = self::SHORT_DESCRIPTION;
			$docblock = trim(
				preg_replace(['~^/\\*\\*~', '~\\*/$~'], '', $this->docComment)
			);
			$annotations = $this->parseDocblockByLine($name, $docblock);
			array_walk_recursive($annotations, function (&$value) {
				$value = trim($value);
			});
		}

		if ($this->reflection instanceof ReflectionClass || $this->reflection instanceof ReflectionMethod || $this->reflection instanceof ReflectionProperty) {
			$annotations = $this->inheritAnnotations($annotations);
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
		$parentDefinitions = $this->getParentDefinitions();

		if ($this->docComment === FALSE) {
			// Inherit the entire docblock
			foreach ($parentDefinitions as $parent) {
				if (count($parent->getAnnotations())) {
					$annotations = $parent->getAnnotations();
					break;
				}
			}

		} else {
			$annotations = $this->inheritLongDescription($parentDefinitions, $annotations);
			$annotations = $this->inheritShortDescription($parentDefinitions, $annotations);
		}

		$annotations = $this->inheritVar($parentDefinitions, $annotations);

		if ($this->reflection instanceof ReflectionMethod) {
			$annotations = $this->inheritParam($parentDefinitions, $annotations);

			// And check if we need and can inherit the return and throws value
			foreach (['return', 'throws'] as $paramName) {
				if ( ! isset($annotations[$paramName])) {
					foreach ($parentDefinitions as $parent) {
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


	/**
	 * @param string $name
	 * @param string $docblock
	 * @return array
	 */
	private function parseDocblockByLine($name, $docblock)
	{
		$annotations = [];
		foreach (explode("\n", $docblock) as $line) {
			$line = preg_replace('~^\\*\\s?~', '', trim($line));

			// End of short description
			if ($line === '' && $name === self::SHORT_DESCRIPTION) {
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
			if ($name === self::SHORT_DESCRIPTION || $name === self::LONG_DESCRIPTION) {
				if ( ! isset($annotations[$name])) {
					$annotations[$name] = $line;

				} else {
					$annotations[$name] .= "\n" . $line;
				}

			} else {
				$annotations[$name][count($annotations[$name]) - 1] .= "\n" . $line;
			}
		}
		return $annotations;
	}


	/**
	 * @return array
	 */
	private function getParents()
	{
		if ($this->reflection instanceof ReflectionClass) {
			$declaringClass = $this->reflection;

		} else {
			$declaringClass = $this->reflection->getDeclaringClass();
		}

		$parents = array_filter(array_merge([$declaringClass->getParentClass()], $declaringClass->getOwnInterfaces()), function ($class) {
			return $class instanceof ReflectionClass;
		});
		return $parents;
	}


	/**
	 * @return array|\ApiGen\TokenReflection\Reflection\ReflectionClass[]|ReflectionMethod[]|ReflectionProperty[]
	 */
	private function getParentDefinitions()
	{
		$parents = $this->getParents();

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
			return $parentDefinitions;

		} elseif ($this->reflection instanceof ReflectionMethod) {
			$name = $this->reflection->getName();
			/** @var \ApiGen\TokenReflection\Reflection\ReflectionClass $parent */
			foreach ($parents as $parent) {
				if ($parent->hasMethod($name)) {
					$parentDefinitions[] = $parent->getMethod($name);
				}
			}
			return $parentDefinitions;
		}

		return $parents;
	}


	/**
	 * @param array|AnnotationsInterface[] $parentDefinitions
	 * @param array $annotations
	 * @return array
	 */
	private function inheritLongDescription(array $parentDefinitions, array $annotations)
	{
		if (isset($annotations[self::LONG_DESCRIPTION]) && FALSE !== stripos($annotations[self::LONG_DESCRIPTION], self::INHERITDOC)) {
			// Inherit long description
			foreach ($parentDefinitions as $parent) {
				if ($parent->hasAnnotation(self::LONG_DESCRIPTION)) {
					$annotations[self::LONG_DESCRIPTION] = str_ireplace(
						self::INHERITDOC,
						$parent->getAnnotation(self::LONG_DESCRIPTION),
						$annotations[self::LONG_DESCRIPTION]
					);
					break;
				}
			}
			$annotations[self::LONG_DESCRIPTION] = str_ireplace(self::INHERITDOC, '', $annotations[self::LONG_DESCRIPTION]);
		}
		return $annotations;
	}


	/**
	 * @param array|AnnotationsInterface[] $parentDefinitions
	 * @param array $annotations
	 * @return array
	 */
	private function inheritShortDescription(array $parentDefinitions, array $annotations)
	{
		if (isset($annotations[self::SHORT_DESCRIPTION]) && FALSE !== stripos($annotations[self::SHORT_DESCRIPTION], self::INHERITDOC)) {
			// Inherit short description
			foreach ($parentDefinitions as $parent) {
				if ($parent->hasAnnotation(self::SHORT_DESCRIPTION)) {
					$annotations[self::SHORT_DESCRIPTION] = str_ireplace(
						self::INHERITDOC,
						$parent->getAnnotation(self::SHORT_DESCRIPTION),
						$annotations[self::SHORT_DESCRIPTION]
					);
					break;
				}
			}
			$annotations[self::SHORT_DESCRIPTION] = str_ireplace(self::INHERITDOC, '', $annotations[self::SHORT_DESCRIPTION]);
		}
		return $annotations;
	}


	/**
	 * @param array|AnnotationsInterface[] $parentDefinitions
	 * @param array $annotations
	 * @return array
	 */
	private function inheritVar(array $parentDefinitions, array $annotations)
	{
		// In case of properties check if we need and can inherit the data type
		if ($this->reflection instanceof ReflectionProperty && empty($annotations['var'])) {
			foreach ($parentDefinitions as $parent) {
				if ($parent->hasAnnotation('var')) {
					$annotations['var'] = $parent->getAnnotation('var');
					break;
				}
			}
		}
		return $annotations;
	}


	/**
	 * @param array|AnnotationsInterface[] $parentDefinitions
	 * @param array $annotations
	 * @return array
	 */
	private function inheritParam(array $parentDefinitions, array $annotations)
	{
		if ($this->reflection->getNumberOfParameters() !== 0 &&
			(empty($annotations['param']) || count($annotations['param']) < $this->reflection->getNumberOfParameters())
		) {
			// In case of methods check if we need and can inherit parameter descriptions
			$params = isset($annotations['param']) ? $annotations['param'] : [];
			$complete = FALSE;
			foreach ($parentDefinitions as $parent) {
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
		return $annotations;
	}

}
