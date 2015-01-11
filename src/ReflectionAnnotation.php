<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection;

use ApiGen\TokenReflection\Behaviors\Annotations;
use ApiGen\TokenReflection\Exception\RuntimeException;


class ReflectionAnnotation implements Annotations
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
	 * List of applied templates.
	 *
	 * @var array
	 */
	private $templates = [];

	/**
	 * Parsed annotations.
	 *
	 * @var array
	 */
	private $annotations;

	/**
	 * Element docblock.
	 *
	 * False if none.
	 *
	 * @var string|bool
	 */
	private $docComment;

	/**
	 * Parent reflection object.
	 *
	 * @var ApiGen\TokenReflection\ReflectionBase
	 */
	private $reflection;


	/**
	 * @param ReflectionBase $reflection
	 * @param string|bool $docComment
	 */
	public function __construct(ReflectionBase $reflection, $docComment = FALSE)
	{
		$this->reflection = $reflection;
		$this->docComment = $docComment ?: FALSE;
	}


	/**
	 * @return string|bool
	 */
	public function getDocComment()
	{
		return $this->docComment;
	}


	/**
	 * {@inheritdoc}
	 */
	public function hasAnnotation($annotation)
	{
		if ($this->annotations === NULL) {
			$this->parse();
		}
		return isset($this->annotations[$annotation]);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getAnnotation($annotation)
	{
		if ($this->annotations === NULL) {
			$this->parse();
		}
		return isset($this->annotations[$annotation]) ? $this->annotations[$annotation] : NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getAnnotations()
	{
		if ($this->annotations === NULL) {
			$this->parse();
		}
		return $this->annotations;
	}


	/**
	 * Sets Docblock templates.
	 *
	 * @param array $templates Docblock templates
	 * @return ReflectionAnnotation
	 * @throws RuntimeException If an invalid annotation template was provided.
	 */
	public function setTemplates(array $templates)
	{
		foreach ($templates as $template) {
			if ( ! $template instanceof ReflectionAnnotation) {
				throw new RuntimeException(
					sprintf(
						'All templates have to be instances of \\TokenReflection\\ReflectionAnnotation; %s given.',
						is_object($template) ? get_class($template) : gettype($template)
					),
					RuntimeException::INVALID_ARGUMENT,
					$this->reflection
				);
			}
		}
		$this->templates = $templates;
		return $this;
	}


	/**
	 * Parses reflection object documentation.
	 */
	private function parse()
	{
		$this->annotations = [];
		if (FALSE !== $this->docComment) {
			// Parse docblock
			$name = self::SHORT_DESCRIPTION;
			$docblock = trim(
				preg_replace(
					[
						'~^' . preg_quote(ReflectionElement::DOCBLOCK_TEMPLATE_START, '~') . '~',
						'~^' . preg_quote(ReflectionElement::DOCBLOCK_TEMPLATE_END, '~') . '$~',
						'~^/\\*\\*~',
						'~\\*/$~'
					],
					'',
					$this->docComment
				)
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
					$this->annotations[$name][] = $matches[2];
					continue;
				}
				// Continuation
				if (self::SHORT_DESCRIPTION === $name || self::LONG_DESCRIPTION === $name) {
					if ( ! isset($this->annotations[$name])) {
						$this->annotations[$name] = $line;
					} else {
						$this->annotations[$name] .= "\n" . $line;
					}
				} else {
					$this->annotations[$name][count($this->annotations[$name]) - 1] .= "\n" . $line;
				}
			}
			array_walk_recursive($this->annotations, function (&$value) {
				// {@*} is a placeholder for */ (phpDocumentor compatibility)
				$value = str_replace('{@*}', '*/', $value);
				$value = trim($value);
			});
		}
		if ($this->reflection instanceof ReflectionElement) {
			// Merge docblock templates
			$this->mergeTemplates();
			// Process docblock inheritance for supported reflections
			if ($this->reflection instanceof ReflectionClass || $this->reflection instanceof ReflectionMethod || $this->reflection instanceof ReflectionProperty) {
				$this->inheritAnnotations();
			}
		}
	}


	/**
	 * Merges templates with the current docblock.
	 */
	private function mergeTemplates()
	{
		foreach ($this->templates as $index => $template) {
			if (0 === $index && $template->getDocComment() === $this->docComment) {
				continue;
			}
			foreach ($template->getAnnotations() as $name => $value) {
				if ($name === self::LONG_DESCRIPTION) {
					// Long description
					if (isset($this->annotations[self::LONG_DESCRIPTION])) {
						$this->annotations[self::LONG_DESCRIPTION] = $value . "\n" . $this->annotations[self::LONG_DESCRIPTION];
					} else {
						$this->annotations[self::LONG_DESCRIPTION] = $value;
					}
				} elseif ($name !== self::SHORT_DESCRIPTION) {
					// Tags; short description is not inherited
					if (isset($this->annotations[$name])) {
						$this->annotations[$name] = array_merge($this->annotations[$name], $value);
					} else {
						$this->annotations[$name] = $value;
					}
				}
			}
		}
	}


	/**
	 * Inherits annotations from parent classes/methods/properties if needed.
	 *
	 * @throws RuntimeException If unsupported reflection was used.
	 */
	private function inheritAnnotations()
	{
		if ($this->reflection instanceof ReflectionClass) {
			$declaringClass = $this->reflection;
		} elseif ($this->reflection instanceof ReflectionMethod || $this->reflection instanceof ReflectionProperty) {
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
			foreach ($parents as $parent) {
				if ($parent->hasProperty($name)) {
					$parentDefinitions[] = $parent->getProperty($name);
				}
			}
			$parents = $parentDefinitions;
		} elseif ($this->reflection instanceof ReflectionMethod) {
			$name = $this->reflection->getName();
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
				$annotations = $parent->getAnnotations();
				if ( ! empty($annotations)) {
					$this->annotations = $annotations;
					break;
				}
			}
		} else {
			if (isset($this->annotations[self::LONG_DESCRIPTION]) && FALSE !== stripos($this->annotations[self::LONG_DESCRIPTION], '{@inheritdoc}')) {
				// Inherit long description
				foreach ($parents as $parent) {
					if ($parent->hasAnnotation(self::LONG_DESCRIPTION)) {
						$this->annotations[self::LONG_DESCRIPTION] = str_ireplace(
							'{@inheritdoc}',
							$parent->getAnnotation(self::LONG_DESCRIPTION),
							$this->annotations[self::LONG_DESCRIPTION]
						);
						break;
					}
				}
				$this->annotations[self::LONG_DESCRIPTION] = str_ireplace('{@inheritdoc}', '', $this->annotations[self::LONG_DESCRIPTION]);
			}
			if (isset($this->annotations[self::SHORT_DESCRIPTION]) && FALSE !== stripos($this->annotations[self::SHORT_DESCRIPTION], '{@inheritdoc}')) {
				// Inherit short description
				foreach ($parents as $parent) {
					if ($parent->hasAnnotation(self::SHORT_DESCRIPTION)) {
						$this->annotations[self::SHORT_DESCRIPTION] = str_ireplace(
							'{@inheritdoc}',
							$parent->getAnnotation(self::SHORT_DESCRIPTION),
							$this->annotations[self::SHORT_DESCRIPTION]
						);
						break;
					}
				}
				$this->annotations[self::SHORT_DESCRIPTION] = str_ireplace('{@inheritdoc}', '', $this->annotations[self::SHORT_DESCRIPTION]);
			}
		}
		// In case of properties check if we need and can inherit the data type
		if ($this->reflection instanceof ReflectionProperty && empty($this->annotations['var'])) {
			foreach ($parents as $parent) {
				if ($parent->hasAnnotation('var')) {
					$this->annotations['var'] = $parent->getAnnotation('var');
					break;
				}
			}
		}
		if ($this->reflection instanceof ReflectionMethod) {
			if (0 !== $this->reflection->getNumberOfParameters() && (empty($this->annotations['param']) || count($this->annotations['param']) < $this->reflection->getNumberOfParameters())) {
				// In case of methods check if we need and can inherit parameter descriptions
				$params = isset($this->annotations['param']) ? $this->annotations['param'] : [];
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
					$this->annotations['param'] = $params;
				}
			}
			// And check if we need and can inherit the return and throws value
			foreach (['return', 'throws'] as $paramName) {
				if ( ! isset($this->annotations[$paramName])) {
					foreach ($parents as $parent) {
						if ($parent->hasAnnotation($paramName)) {
							$this->annotations[$paramName] = $parent->getAnnotation($paramName);
							break;
						}
					}
				}
			}
		}
	}

}
