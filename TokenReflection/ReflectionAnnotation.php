<?php
/**
 * PHP Token Reflection
 *
 * Version 1.0.2
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this library in the file LICENSE.
 *
 * @author Ondřej Nešpor
 * @author Jaroslav Hanslík
 */

namespace TokenReflection;

use TokenReflection\Exception;

/**
 * Docblock parser.
 */
class ReflectionAnnotation
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
	 * Copydoc recursion stack.
	 *
	 * Prevents from infinite loops when using the @copydoc annotation.
	 *
	 * @var array
	 */
	private static $copydocStack = array();

	/**
	 * List of applied templates.
	 *
	 * @var array
	 */
	private $templates = array();

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
	 * @var string|boolean
	 */
	private $docComment;

	/**
	 * Parent reflection object.
	 *
	 * @var \TokenReflection\ReflectionBase
	 */
	private $reflection;

	/**
	 * Constructor.
	 *
	 * @param \TokenReflection\ReflectionBase $reflection Parent reflection object
	 * @param string|boolean $docComment Docblock definition
	 */
	public function __construct(ReflectionBase $reflection, $docComment = false)
	{
		$this->reflection = $reflection;
		$this->docComment = $docComment ?: false;
	}

	/**
	 * Returns the docblock.
	 *
	 * @return string|boolean
	 */
	public function getDocComment()
	{
		return $this->docComment;
	}

	/**
	 * Returns if the current docblock contains the requrested annotation.
	 *
	 * @param string $annotation Annotation name
	 * @return boolean
	 */
	public function hasAnnotation($annotation)
	{
		if (null === $this->annotations) {
			$this->parse();
		}

		return isset($this->annotations[$annotation]);
	}

	/**
	 * Returns a particular annotation value.
	 *
	 * @param string $annotation Annotation name
	 * @return string|array|null
	 */
	public function getAnnotation($annotation)
	{
		if (null === $this->annotations) {
			$this->parse();
		}

		return isset($this->annotations[$annotation]) ? $this->annotations[$annotation] : null;
	}

	/**
	 * Returns all parsed annotations.
	 *
	 * @return array
	 */
	public function getAnnotations()
	{
		if (null === $this->annotations) {
			$this->parse();
		}

		return $this->annotations;
	}

	/**
	 * Sets Docblock templates.
	 *
	 * @param array $templates Docblock templates
	 * @return \TokenReflection\ReflectionAnnotation
	 * @throws \TokenReflection\Exception\Runtime If an invalid annotation template was provided.
	 */
	public function setTemplates(array $templates)
	{
		foreach ($templates as $template) {
			if (!$template instanceof ReflectionAnnotation) {
				throw new Exception\Runtime(
					sprintf(
						'All templates have to be instances of \\TokenReflection\\ReflectionAnnotation; %s given.',
						is_object($template) ? get_class($template) : gettype($template)
					),
					Exception\Runtime::INVALID_ARGUMENT
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
		$this->annotations = array();

		if (false !== $this->docComment) {
			// Parse docblock
			$name = self::SHORT_DESCRIPTION;
			$docblock = trim(
				preg_replace(
					array(
						'~^' . preg_quote(ReflectionElement::DOCBLOCK_TEMPLATE_START, '~') . '~',
						'~^' . preg_quote(ReflectionElement::DOCBLOCK_TEMPLATE_END, '~') . '$~',
						'~^/\\*\\*~',
						'~\\*/$~'
					),
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
				if (preg_match('~^@([\\S]+)\\s*(.*)~', $line, $matches)) {
					$name = $matches[1];
					$this->annotations[$name][] = $matches[2];
					continue;
				}

				// Continuation
				if (self::SHORT_DESCRIPTION === $name || self::LONG_DESCRIPTION === $name) {
					if (!isset($this->annotations[$name])) {
						$this->annotations[$name] = $line;
					} else {
						$this->annotations[$name] .= "\n" . $line;
					}
				} else {
					$this->annotations[$name][count($this->annotations[$name]) - 1] .= "\n" . $line;
				}
			}

			array_walk_recursive($this->annotations, function(&$value) {
				// {@*} is a placeholder for */ (phpDocumentor compatibility)
				$value = str_replace('{@*}', '*/', $value);
				$value = trim($value);
			});
		}

		if ($this->reflection instanceof ReflectionElement) {
			// Merge docblock templates
			$this->mergeTemplates();

			// Copy annotations if the @copydoc tag is present.
			if (!empty($this->annotations['copydoc'])) {
				$this->copyAnnotation();
			}

			// Process docblock inheritance for supported reflections
			if ($this->reflection instanceof ReflectionClass || $this->reflection instanceof ReflectionMethod || $this->reflection instanceof ReflectionProperty) {
				$this->inheritAnnotations();
			}
		}
	}

	/**
	 * Copies annotations if the @copydoc tag is present.
	 */
	private function copyAnnotation()
	{
		self::$copydocStack[] = $this->reflection;
		$broker = $this->reflection->getBroker();

		$parentNames = $this->annotations['copydoc'];
		unset($this->annotations['copydoc']);

		foreach ($parentNames as $parentName) {
			try {
				if ($this->reflection instanceof ReflectionClass) {
					$parent = $broker->getClass($parentName);
					if ($parent instanceof Dummy\ReflectionClass) {
						// The class to copy from does not exist
						return;
					}
				} elseif ($this->reflection instanceof ReflectionFunction) {
					$parent = $broker->getFunction($parentName);
				} elseif ($this->reflection instanceof ReflectionConstant && null === $this->reflection->getDeclaringClassName()) {
					$parent = $broker->getConstant($parentName);
				} elseif ($this->reflection instanceof ReflectionMethod || $this->reflection instanceof ReflectionParameter || $this->reflection instanceof ReflectionConstant) {
					if (false !== strpos($parentName, '::')) {
						list($className, $parentName) = explode('::', $parentName, 2);
						$class = $broker->getClass($className);
					} else {
						$class = $this->reflection->getDeclaringClass();
					}

					if ($class instanceof Dummy\ReflectionClass) {
						// The source element class does not exist
						return;
					}

					if ($this->reflection instanceof ReflectionMethod) {
						$parent = $class->getMethod(rtrim($parentName, '()'));
					} elseif ($this->reflection instanceof ReflectionConstant) {
						$parent = $class->getConstantReflection($parentName);
					} else {
						$parent = $class->getProperty($parentName);
					}
				}

				if (!empty($parent)) {
					// Don't get into an infinite recursion loop
					if (in_array($parent, self::$copydocStack, true)) {
						throw new Exception\Runtime('Infinite loop detected.', Exception\Runtime::INVALID_ARGUMENT);
					}

					self::$copydocStack[] = $parent;

					// We can get into an infinite loop here (e.g. when two methods @copydoc from each other)
					foreach ($parent->getAnnotations() as $name => $value) {
						// Add annotations that are not already present
						if (empty($this->annotations[$name])) {
							$this->annotations[$name] = $value;
						}
					}

					array_pop(self::$copydocStack);
				}
			} catch (Exception $e) {
				// Fall through
			}
		}

		array_pop(self::$copydocStack);
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
	 * @throws \TokenReflection\Exception\Parse If unsupported reflection was used.
	 */
	private function inheritAnnotations()
	{
		if ($this->reflection instanceof ReflectionClass) {
			$declaringClass = $this->reflection;
		} elseif ($this->reflection instanceof ReflectionMethod || $this->reflection instanceof ReflectionProperty) {
			$declaringClass = $this->reflection->getDeclaringClass();
		} else {
			throw new Exception\Parse(sprintf('Unsupported reflection type: "%s".', get_class($this->reflection)), Exception\Parse::UNSUPPORTED);
		}

		$parents = array_filter(array_merge(array($declaringClass->getParentClass()), $declaringClass->getOwnInterfaces()), function($class) {
			return $class instanceof ReflectionClass;
		});

		// In case of properties and methods, look for a property/method of the same name and return
		// and array of such members.
		$parentDefinitions = array();
		if ($this->reflection instanceof ReflectionProperty) {
			$name = $this->reflection->getName();
			foreach ($parents as $parent) {
				try {
					$parentDefinitions[] = $parent->getProperty($name);
				} catch (Exception\Runtime $e) {
					if (Exception\Runtime::DOES_NOT_EXIST === $e->getCode()) {
						continue;
					}

					throw $e;
				}
			}

			$parents = $parentDefinitions;
		} elseif ($this->reflection instanceof ReflectionMethod) {
			$name = $this->reflection->getName();
			foreach ($parents as $parent) {
				try {
					$parentDefinitions[] = $parent->getMethod($name);
				} catch (Exception\Runtime $e) {
					if (Exception\Runtime::DOES_NOT_EXIST === $e->getCode()) {
						continue;
					}

					throw $e;
				}
			}

			$parents = $parentDefinitions;
		}

		if (false === $this->docComment) {
			// Inherit the entire docblock
			foreach ($parents as $parent) {
				$annotations = $parent->getAnnotations();
				if (!empty($annotations)) {
					$this->annotations = $annotations;
					break;
				}
			}
		} else {
			if (isset($this->annotations[self::LONG_DESCRIPTION]) && false !== stripos($this->annotations[self::LONG_DESCRIPTION], '{@inheritdoc}')) {
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
			if (isset($this->annotations[self::SHORT_DESCRIPTION]) && false !== stripos($this->annotations[self::SHORT_DESCRIPTION], '{@inheritdoc}')) {
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
				$params = isset($this->annotations['param']) ? $this->annotations['param'] : array();
				foreach ($parents as $parent) {
					if ($parent->hasAnnotation('param')) {
						$parentParams = array_slice($parent->getAnnotation('param'), count($params));

						while (!empty($parentParams)) {
							array_push($params, array_shift($parentParams));

							if (count($params) === $this->reflection->getNumberOfParameters()) {
								break 2;
							}
						}
					}
				}

				if (!empty($params)) {
					$this->annotations['param'] = $params;
				}
			}

			// And check if we need and can inherit the return and throws value
			foreach (array('return', 'throws') as $paramName) {
				if (!isset($this->annotations[$paramName])) {
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
