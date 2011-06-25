<?php
/**
 * PHP Token Reflection
 *
 * Version 1.0 beta 3
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
 * Reflection of a namespace parsed from a file.
 */
class ReflectionFileNamespace extends ReflectionBase
{
	/**
	 * List of class reflections.
	 *
	 * @var array
	 */
	private $classes = array();

	/**
	 * List of constant reflections.
	 *
	 * @var array
	 */
	private $constants = array();

	/**
	 * List of function reflections.
	 *
	 * @var array
	 */
	private $functions = array();

	/**
	 * Namespace aliases.
	 *
	 * @var array
	 */
	private $aliases = array();

	/**
	 * Returns class reflections.
	 *
	 * @return array
	 */
	public function getClasses()
	{
		return $this->classes;
	}

	/**
	 * Returns constant reflections.
	 *
	 * @return array
	 */
	public function getConstants()
	{
		return $this->constants;
	}

	/**
	 * Returns function reflections.
	 *
	 * @return array
	 */
	public function getFunctions()
	{
		return $this->functions;
	}

	/**
	 * Returns all imported namespaces and aliases.
	 *
	 * @return array
	 */
	public function getNamespaceAliases()
	{
		return $this->aliases;
	}

	/**
	 * Processes the parent reflection object.
	 *
	 * @param \TokenReflection\IReflection $parent Parent reflection object
	 * @return \TokenReflection\ReflectionBase
	 * @throws \TokenReflection\Exception\Parse If an invalid parent reflection object was provided
	 */
	protected function processParent(IReflection $parent)
	{
		if (!$parent instanceof ReflectionFile) {
			throw new Exception\Parse(sprintf('The parent object has to be an instance of TokenReflection\ReflectionFile, "%s" given.', get_class($parent)), Exception\Parse::INVALID_PARENT);
		}

		return parent::processParent($parent);
	}

	/**
	 * Parses reflected element metadata from the token stream.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @param \TokenReflection\IReflection $parent Parent reflection object
	 * @return \TokenReflection\ReflectionFileNamespace
	 */
	protected function parse(Stream $tokenStream, IReflection $parent)
	{
		return $this
			->parseName($tokenStream)
			->parseAliases($tokenStream);
	}

	/**
	 * Parses the namespace name.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @return \TokenReflection\ReflectionFileNamespace
	 * @throws \TokenReflection\Exception\Parse If the namespace name could not be determined
	 */
	protected function parseName(Stream $tokenStream)
	{
		if (!$tokenStream->is(T_NAMESPACE)) {
			$this->name = ReflectionNamespace::NO_NAMESPACE_NAME;
			return $this;
		}

		try {
			$tokenStream->skipWhitespaces();

			$name = '';
			// Iterate over the token stream
			while (true) {
				switch ($tokenStream->getType()) {
					// If the current token is a T_STRING, it is a part of the namespace name
					case T_STRING:
					case T_NS_SEPARATOR:
						$name .= $tokenStream->getTokenValue();
						break;
					default:
						// Stop iterating when other token than string or ns separator found
						break 2;
				}

				$tokenStream->skipWhitespaces();
			}

			$name = ltrim($name, '\\');

			if (empty($name)) {
				$this->name = ReflectionNamespace::NO_NAMESPACE_NAME;
			} else {
				$this->name = $name;
			}

			if (!$tokenStream->is(';') && !$tokenStream->is('{')) {
				throw new Exception\Parse(sprintf('Invalid namespace name end: "%s", expecting ";" or "{".', $tokenStream->getTokenName()), Exception\Parse::PARSE_ELEMENT_ERROR);
			}

			$tokenStream->skipWhitespaces();

			return $this;
		} catch (Exception $e) {
			throw new Exception\Parse('Could not parse namespace name.', Exception\Parse::PARSE_ELEMENT_ERROR, $e);
		}
	}

	/**
	 * Parses other namespaces usage and aliases from the token stream.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @return \TokenReflection\ReflectionFileNamespace
	 * @throws \TokenReflection\Exception\Parse If aliases could not be parsed
	 */
	private function parseAliases(Stream $tokenStream)
	{
		if (ReflectionNamespace::NO_NAMESPACE_NAME === $this->name) {
			return $this;
		}

		try {
			$aliases = array();

			while (true) {
				if (!$tokenStream->is(T_USE)) {
					break;
				}

				while (true) {
					$namespaceName = '';
					$alias = null;

					$tokenStream->skipWhitespaces();

					while (true) {
						switch ($tokenStream->getType()) {
							case T_STRING:
							case T_NS_SEPARATOR:
								$namespaceName .= $tokenStream->getTokenValue();
								break;
							default:
								break 2;
						}
						$tokenStream->skipWhitespaces();
					}
					$namespaceName = ltrim($namespaceName, '\\');

					if (empty($namespaceName)) {
						throw new Exception\Parse('Imported namespace name could not be determined.', Exception\Parse::PARSE_ELEMENT_ERROR);
					} elseif ('\\' === substr($namespaceName, -1)) {
						throw new Exception\Parse(sprintf('Invalid namespace name "%s".', $namespaceName), Exception\Parse::PARSE_ELEMENT_ERROR);
					}

					if ($tokenStream->is(T_AS)) {
						// Alias defined
						$tokenStream->skipWhitespaces();

						if (!$tokenStream->is(T_STRING)) {
							throw new Exception\Parse(sprintf('The imported namespace "%s" seems aliased but the alias name could not be determined.', $namespaceName), Exception\Parse::PARSE_ELEMENT_ERROR);
						}

						$alias = $tokenStream->getTokenValue();

						$tokenStream->skipWhitespaces();
					} else {
						// No explicit alias
						if (false !== ($pos = strrpos($namespaceName, '\\'))) {
							$alias = substr($namespaceName, $pos + 1);
						} else {
							$alias = $namespaceName;
						}
					}

					if (isset($aliases[$alias])) {
						throw new Exception\Parse(sprintf('Namespace alias "%s" already defined.', $alias), Exception\Parse::PARSE_ELEMENT_ERROR);
					}

					$aliases[$alias] = $namespaceName;

					$type = $tokenStream->getType();
					if (';' === $type) {
						// Next "use" definition
						$tokenStream->skipWhitespaces();
						continue 2;
					} elseif (',' === $type) {
						// Next namespace in the current "use" definition
						continue;
					}

					throw new Exception\Parse(sprintf('Unexpected token found: "%s".', $tokenStream->getTokenName()), Exception\Parse::PARSE_ELEMENT_ERROR);
				}
			}

			$this->aliases = $aliases;

			return $this;
		} catch (Exception\Parse $e) {
			throw new Exception\Parse('Could not parse namespace aliases.', 0, $e);
		}
	}

	/**
	 * Parses child reflection objects from the token stream.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @param \TokenReflection\IReflection $parent Parent reflection object
	 * @return \TokenReflection\ReflectionFileNamespace
	 * @throws \TokenReflection\Exception\Parse If child elements could not be parsed
	 */
	protected function parseChildren(Stream $tokenStream, IReflection $parent)
	{
		static $skipped = array(T_WHITESPACE => true, T_COMMENT => true, T_DOC_COMMENT => true);

		while (true) {
			switch ($tokenStream->getType()) {
				case T_COMMENT:
				case T_DOC_COMMENT:
					$docblock = $tokenStream->getTokenValue();
					if (preg_match('~^' . preg_quote(self::DOCBLOCK_TEMPLATE_START, '~') . '~', $docblock)) {
						array_unshift($this->docblockTemplates, new ReflectionAnnotation($this, $docblock));
					} elseif (self::DOCBLOCK_TEMPLATE_END === $docblock) {
						array_shift($this->docblockTemplates);
					}
					$tokenStream->next();
					break;
				case '{':
					$tokenStream->findMatchingBracket()->next();
					break;
				case '}':
				case null:
				case T_NAMESPACE:
					break 2;
				case T_ABSTRACT:
				case T_FINAL:
				case T_CLASS:
				case T_INTERFACE:
					$class = new ReflectionClass($tokenStream, $this->getBroker(), $this);
					$this->classes[$class->getName()] = $class;
					$tokenStream->next();
					break;
				case T_CONST:
					$tokenStream->skipWhitespaces();
					while ($tokenStream->is(T_STRING)) {
						$constant = new ReflectionConstant($tokenStream, $this->getBroker(), $this);
						$this->constants[$constant->getName()] = $constant;
						if ($tokenStream->is(',')) {
							$tokenStream->skipWhitespaces();
						} else {
							$tokenStream->next();
						}
					}
					break;
				case T_FUNCTION:
					$position = $tokenStream->key() + 1;
					while (isset($skipped[$type = $tokenStream->getType($position)])) {
						$position++;
					}
					if ('(' === $type) {
						// Skipping anonymous functions

						$tokenStream
							->seek($position)
							->findMatchingBracket()
							->skipWhiteSpaces();

						if ($tokenStream->is(T_USE)) {
							$tokenStream
								->skipWhitespaces()
								->findMatchingBracket()
								->skipWhitespaces();
						}

						$tokenStream
							->findMatchingBracket()
							->next();

						continue;
					}

					$function = new ReflectionFunction($tokenStream, $this->getBroker(), $this);
					$this->functions[$function->getName()] = $function;
					$tokenStream->next();
					break;
				default:
					$tokenStream->next();
					break;
			}
		}

		return $this;
	}

}
