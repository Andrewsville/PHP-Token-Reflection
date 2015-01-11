<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection;

use ApiGen\TokenReflection\Exception;
use ApiGen\TokenReflection\Stream\StreamBase as Stream;


/**
 * Reflection of a namespace parsed from a file.
 */
class ReflectionFileNamespace extends ReflectionElement
{

	/**
	 * List of class reflections.
	 *
	 * @var array
	 */
	private $classes = [];

	/**
	 * List of constant reflections.
	 *
	 * @var array
	 */
	private $constants = [];

	/**
	 * List of function reflections.
	 *
	 * @var array
	 */
	private $functions = [];

	/**
	 * Namespace aliases.
	 *
	 * @var array
	 */
	private $aliases = [];


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
	 * @param ApiGen\TokenReflection\IReflection $parent Parent reflection object
	 * @param ApiGen\TokenReflection\Stream\StreamBase $tokenStream Token substream
	 * @return ApiGen\TokenReflection\ReflectionElement
	 * @throws ApiGen\TokenReflection\Exception\ParseException If an invalid parent reflection object was provided.
	 */
	protected function processParent(IReflection $parent, Stream $tokenStream)
	{
		if ( ! $parent instanceof ReflectionFile) {
			throw new Exception\ParseException($this, $tokenStream, 'The parent object has to be an instance of TokenReflection\ReflectionFile.', Exception\ParseException::INVALID_PARENT);
		}
		return parent::processParent($parent, $tokenStream);
	}


	/**
	 * Parses reflected element metadata from the token stream.
	 *
	 * @param ApiGen\TokenReflection\Stream\StreamBase $tokenStream Token substream
	 * @param ApiGen\TokenReflection\IReflection $parent Parent reflection object
	 * @return ApiGen\TokenReflection\ReflectionFileNamespace
	 */
	protected function parse(Stream $tokenStream, IReflection $parent)
	{
		return $this->parseName($tokenStream);
	}


	/**
	 * Find the appropriate docblock.
	 *
	 * @param ApiGen\TokenReflection\Stream\StreamBase $tokenStream Token substream
	 * @param ApiGen\TokenReflection\IReflection $parent Parent reflection
	 * @return ApiGen\TokenReflection\ReflectionElement
	 */
	protected function parseDocComment(Stream $tokenStream, IReflection $parent)
	{
		if ( ! $tokenStream->is(T_NAMESPACE)) {
			$this->docComment = new ReflectionAnnotation($this);
			return $this;
		} else {
			return parent::parseDocComment($tokenStream, $parent);
		}
	}


	/**
	 * Parses the namespace name.
	 *
	 * @param ApiGen\TokenReflection\Stream\StreamBase $tokenStream Token substream
	 * @return ApiGen\TokenReflection\ReflectionFileNamespace
	 * @throws ApiGen\TokenReflection\Exception\ParseException If the namespace name could not be determined.
	 */
	protected function parseName(Stream $tokenStream)
	{
		if ( ! $tokenStream->is(T_NAMESPACE)) {
			$this->name = ReflectionNamespace::NO_NAMESPACE_NAME;
			return $this;
		}
		$tokenStream->skipWhitespaces();
		$name = '';
		// Iterate over the token stream
		while (TRUE) {
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
			$tokenStream->skipWhitespaces(TRUE);
		}
		$name = ltrim($name, '\\');
		if (empty($name)) {
			$this->name = ReflectionNamespace::NO_NAMESPACE_NAME;
		} else {
			$this->name = $name;
		}
		if ( ! $tokenStream->is(';') && !$tokenStream->is('{')) {
			throw new Exception\ParseException($this, $tokenStream, 'Invalid namespace name end, expecting ";" or "{".', Exception\ParseException::UNEXPECTED_TOKEN);
		}
		$tokenStream->skipWhitespaces();
		return $this;
	}


	/**
	 * Parses child reflection objects from the token stream.
	 *
	 * @param ApiGen\TokenReflection\Stream\StreamBase $tokenStream Token substream
	 * @param ApiGen\TokenReflection\IReflection $parent Parent reflection object
	 * @return ApiGen\TokenReflection\ReflectionFileNamespace
	 * @throws ApiGen\TokenReflection\Exception\ParseException If child elements could not be parsed.
	 */
	protected function parseChildren(Stream $tokenStream, IReflection $parent)
	{
		static $skipped = [T_WHITESPACE => TRUE, T_COMMENT => TRUE, T_DOC_COMMENT => TRUE];
		$depth = 0;
		$firstChild = NULL;
		while (TRUE) {
			switch ($tokenStream->getType()) {
				case T_USE:
					while (TRUE) {
						$namespaceName = '';
						$alias = NULL;
						$tokenStream->skipWhitespaces(TRUE);
						while (TRUE) {
							switch ($tokenStream->getType()) {
								case T_STRING:
								case T_NS_SEPARATOR:
									$namespaceName .= $tokenStream->getTokenValue();
									break;
								default:
									break 2;
							}
							$tokenStream->skipWhitespaces(TRUE);
						}
						$namespaceName = ltrim($namespaceName, '\\');
						if (empty($namespaceName)) {
							throw new Exception\ParseException($this, $tokenStream, 'Imported namespace name could not be determined.', Exception\ParseException::LOGICAL_ERROR);
						} elseif ('\\' === substr($namespaceName, -1)) {
							throw new Exception\ParseException($this, $tokenStream, sprintf('Invalid namespace name "%s".', $namespaceName), Exception\ParseException::LOGICAL_ERROR);
						}
						if ($tokenStream->is(T_AS)) {
							// Alias defined
							$tokenStream->skipWhitespaces(TRUE);
							if ( ! $tokenStream->is(T_STRING)) {
								throw new Exception\ParseException($this, $tokenStream, sprintf('The imported namespace "%s" seems aliased but the alias name could not be determined.', $namespaceName), Exception\ParseException::LOGICAL_ERROR);
							}
							$alias = $tokenStream->getTokenValue();
							$tokenStream->skipWhitespaces(TRUE);
						} else {
							// No explicit alias
							if (FALSE !== ($pos = strrpos($namespaceName, '\\'))) {
								$alias = substr($namespaceName, $pos + 1);
							} else {
								$alias = $namespaceName;
							}
						}
						if (isset($this->aliases[$alias])) {
							throw new Exception\ParseException($this, $tokenStream, sprintf('Namespace alias "%s" already defined.', $alias), Exception\ParseException::LOGICAL_ERROR);
						}
						$this->aliases[$alias] = $namespaceName;
						$type = $tokenStream->getType();
						if (';' === $type) {
							$tokenStream->skipWhitespaces();
							break 2;
						} elseif (',' === $type) {
							// Next namespace in the current "use" definition
							continue;
						}
						throw new Exception\ParseException($this, $tokenStream, 'Unexpected token found.', Exception\ParseException::UNEXPECTED_TOKEN);
					}
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
					$tokenStream->next();
					$depth++;
					break;
				case '}':
					if (0 === $depth--) {
						break 2;
					}
					$tokenStream->next();
					break;
				case NULL:
				case T_NAMESPACE:
					break 2;
				case T_ABSTRACT:
				case T_FINAL:
				case T_CLASS:
				case T_TRAIT:
				case T_INTERFACE:
					$class = new ReflectionClass($tokenStream, $this->getBroker(), $this);
					$firstChild = $firstChild ?: $class;
					$className = $class->getName();
					if (isset($this->classes[$className])) {
						if ( ! $this->classes[$className] instanceof Invalid\ReflectionClass) {
							$this->classes[$className] = new Invalid\ReflectionClass($className, $this->classes[$className]->getFileName(), $this->getBroker());
						}
						if ( ! $this->classes[$className]->hasReasons()) {
							$this->classes[$className]->addReason(new Exception\ParseException(
								$this,
								$tokenStream,
								sprintf('Class %s is defined multiple times in the file.', $className),
								Exception\ParseException::ALREADY_EXISTS
							));
						}
					} else {
						$this->classes[$className] = $class;
					}
					$tokenStream->next();
					break;
				case T_CONST:
					$tokenStream->skipWhitespaces(TRUE);
					do {
						$constant = new ReflectionConstant($tokenStream, $this->getBroker(), $this);
						$firstChild = $firstChild ?: $constant;
						$constantName = $constant->getName();
						if (isset($this->constants[$constantName])) {
							if ( ! $this->constants[$constantName] instanceof Invalid\ReflectionConstant) {
								$this->constants[$constantName] = new Invalid\ReflectionConstant($constantName, $this->constants[$constantName]->getFileName(), $this->getBroker());
							}
							if ( ! $this->constants[$constantName]->hasReasons()) {
								$this->constants[$constantName]->addReason(new Exception\ParseException(
									$this,
									$tokenStream,
									sprintf('Constant %s is defined multiple times in the file.', $constantName),
									Exception\ParseException::ALREADY_EXISTS
								));
							}
						} else {
							$this->constants[$constantName] = $constant;
						}
						if ($tokenStream->is(',')) {
							$tokenStream->skipWhitespaces(TRUE);
						} else {
							$tokenStream->next();
						}
					} while ($tokenStream->is(T_STRING));
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
							->skipWhiteSpaces(TRUE);
						if ($tokenStream->is(T_USE)) {
							$tokenStream
								->skipWhitespaces(TRUE)
								->findMatchingBracket()
								->skipWhitespaces(TRUE);
						}
						$tokenStream
							->findMatchingBracket()
							->next();
						continue;
					}
					$function = new ReflectionFunction($tokenStream, $this->getBroker(), $this);
					$firstChild = $firstChild ?: $function;
					$functionName = $function->getName();
					if (isset($this->functions[$functionName])) {
						if ( ! $this->functions[$functionName] instanceof Invalid\ReflectionFunction) {
							$this->functions[$functionName] = new Invalid\ReflectionFunction($functionName, $this->functions[$functionName]->getFileName(), $this->getBroker());
						}
						if ( ! $this->functions[$functionName]->hasReasons()) {
							$this->functions[$functionName]->addReason(new Exception\ParseException(
								$this,
								$tokenStream,
								sprintf('Function %s is defined multiple times in the file.', $functionName),
								Exception\ParseException::ALREADY_EXISTS
							));
						}
					} else {
						$this->functions[$functionName] = $function;
					}
					$tokenStream->next();
					break;
				default:
					$tokenStream->next();
					break;
			}
		}
		if ($firstChild) {
			$this->startPosition = min($this->startPosition, $firstChild->getStartPosition());
		}
		return $this;
	}

}