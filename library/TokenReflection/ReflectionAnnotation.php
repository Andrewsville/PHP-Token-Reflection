<?php
/**
 * PHP Token Reflection
 *
 * Version 1.0beta1
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
	 * @var string|false
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
	 * @param string|false $docComment Docblock definition
	 */
	public function __construct(ReflectionBase $reflection, $docComment = null)
	{
		$this->reflection = $reflection;
		$this->docComment = $docComment ?: false;
	}

	/**
	 * Returns the docblock.
	 *
	 * @return string|false
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
	 * @throws \TokenReflection\Exception\Runtime If an invalid annotation template was provided
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
			$docblock = trim(preg_replace(
				array(
					'~^' . preg_quote(ReflectionBase::DOCBLOCK_TEMPLATE_START, '~') . '~',
					'~^' . preg_quote(ReflectionBase::DOCBLOCK_TEMPLATE_END, '~') . '$~',
					'~^/\\*\\*~',
					'~\\*/$~'
				),
				'',
				$this->docComment
			));
			foreach (explode("\n", $docblock) as $line) {
				$line = preg_replace('~^\\*\\s*~', '', trim($line));

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

		// Merge docblock templates
		$this->mergeTemplates();

		// Process docblock inheritance if needed
		$willInherit = false === $this->docComment;
		if (!$willInherit && isset($this->annotations[self::SHORT_DESCRIPTION])) {
			$willInherit = false !== strpos($this->annotations[self::SHORT_DESCRIPTION], '{@inheritdoc}');
		}
		if (!$willInherit && isset($this->annotations[self::LONG_DESCRIPTION])) {
			$willInherit = false !== strpos($this->annotations[self::LONG_DESCRIPTION], '{@inheritdoc}');
		}
		if ($willInherit) {
			$this->inheritAnnotations();
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
	 */
	private function inheritAnnotations()
	{
		// Find the reflection to inherit from
		$parentReflection = null;
		if ($this->reflection instanceof ReflectionClass) {
			$parentClass = $this->reflection->getParentClass();
			if (null !== $parentClass && $parentClass->isTokenized()) {
				// Process parent only if tokenized; internal and dummy classes have no docblocks
				$parentReflection = $parentClass;
			}
		} elseif ($this->reflection instanceof ReflectionMethod || $this->reflection instanceof ReflectionProperty) {
			$parentClass = $this->reflection->getDeclaringClass()->getParentClass();
			if (null !== $parentClass && $parentClass->isTokenized()) {
				// Process parent only if tokenized;
				// internal and dummy classes' methods and properties have no docblocks
				try {
					if ($this->reflection instanceof ReflectionMethod) {
						$parentReflection = $parentClass->getMethod($this->reflection->getName());
					} else {
						$parentReflection = $parentClass->getProperty($this->reflection->getName());
					}
				} catch (Exception\Runtime $e) {
					// No usable parent reflection object exists
				}
			}
		}

		if (false === $this->docComment) {
			if (null !== $parentReflection) {
				// No documentation -> inherit everything
				$this->annotations = $parentReflection->getAnnotations();
			}
		} else {
			// Place parent short and long descriptions on defined positions
			if (isset($this->annotations[self::SHORT_DESCRIPTION]) && false !== strpos($this->annotations[self::SHORT_DESCRIPTION], '{@inheritdoc}')) {
				$this->annotations[self::SHORT_DESCRIPTION] = str_replace(
					'{@inheritdoc}',
					null === $parentReflection ? '' : $parentReflection->getAnnotation(self::SHORT_DESCRIPTION),
					$this->annotations[self::SHORT_DESCRIPTION]
				);
			}
			if (isset($this->annotations[self::LONG_DESCRIPTION]) && false !== strpos($this->annotations[self::LONG_DESCRIPTION], '{@inheritdoc}')) {
				$this->annotations[self::LONG_DESCRIPTION] = str_replace(
					'{@inheritdoc}',
					null === $parentReflection ? '' : $parentReflection->getAnnotation(self::LONG_DESCRIPTION),
					$this->annotations[self::LONG_DESCRIPTION]
				);
			}
		}
	}
}
