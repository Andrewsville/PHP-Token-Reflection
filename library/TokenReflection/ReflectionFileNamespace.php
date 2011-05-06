<?php
/**
 * PHP Token Reflection
 *
 * Development version
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

use RuntimeException;

/**
 * Reflection of a namespace parsed from a file.
 */
class ReflectionFileNamespace extends ReflectionBase
{
	/**
	 * Namespace aliases.
	 *
	 * @var array
	 */
	private $aliases = array();

	/**
	 * List of class reflections.
	 *
	 * @var array
	 */
	private $classes = array();

	/**
	 * List of function reflections.
	 *
	 * @var array
	 */
	private $functions = array();

	/**
	 * List of constant reflections.
	 *
	 * @var array
	 */
	private $constants = array();

	/**
	 * Returns an array of all class reflections.
	 *
	 * @return array
	 */
	public function getClasses()
	{
		return $this->classes;
	}

	/**
	 * Returns all function reflections.
	 *
	 * @return array
	 */
	public function getFunctions()
	{
		return $this->functions;
	}

	/**
	 * Returns all constant reflections.
	 *
	 * @return array
	 */
	public function getConstants()
	{
		return $this->constants;
	}

	/**
	 * Returns the docblock definition of the namespace.
	 *
	 * @return string|false
	 */
	public function getInheritedDocComment()
	{
		return $this->getDocComment();
	}

	/**
	 * Returns all imported namespaces and aliases.
	 *
	 * @return array
	 */
	public function getAliases()
	{
		return $this->aliases;
	}

	/**
	 * Processes the parent reflection object.
	 *
	 * @param \TokenReflection\IReflection $parent Parent reflection object
	 * @return \TokenReflection\ReflectionBase
	 */
	protected function processParent(IReflection $parent)
	{
		if (!$parent instanceof ReflectionFile) {
			throw new RuntimeException(sprintf('The parent object has to be an instance of TokenReflection\ReflectionFile, %s given.', get_class($parent)));
		}

		return parent::processParent($parent);
	}

	/**
	 * Parses the token stream.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @param \TokenReflection\IReflection $parent Parent reflection object
	 * @return \TokenReflection\ReflectionFileNamespace
	 */
	protected function parse(Stream $tokenStream, IReflection $parent)
	{
		return parent::parse($tokenStream, $parent)
			->parseName($tokenStream)
			->parseAliases($tokenStream);
	}

	/**
	 * Parses child reflection objects from the token stream.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @return \TokenReflection\ReflectionFileNamespace
	 */
	protected function parseChildren(Stream $tokenStream)
	{
		while (true) {
			switch ($tokenStream->getType()) {
				case null:
					break 2;
				case T_CLASS:
				case T_INTERFACE:
					$stream = $tokenStream->getClassStream();
					$class = new ReflectionClass($stream, $this->getBroker(), $this);
					$this->classes[$class->getName()] = $class;
					break;
				case T_CONST:
					$tokenStream->skipWhitespaces();
					while ($tokenStream->is(T_STRING)) {
						$stream = $tokenStream->getConstantStream();
						$constant = new ReflectionConstant($stream, $this->getBroker(), $this);
						$this->constants[$constant->getName()] = $constant;
						$tokenStream->skipWhitespaces();
					}
					break;
				case T_FUNCTION:
					static $skipped = array(T_WHITESPACE, T_COMMENT, T_DOC_COMMENT);

					$position = $tokenStream->key();
					while (in_array($type = $tokenStream->getType($position + 1), $skipped)) {
						$position++;
					}
					if ('(' === $type) {
						// Skipping anonymous functions
						$tokenStream->skipWhitespaces();
						continue;
					}

					$stream = $tokenStream->getFunctionStream();
					$function = new ReflectionFunction($stream, $this->getBroker(), $this);
					$this->functions[$function->getName()] = $function;
					break;
				default:
					$tokenStream->skipWhitespaces();
			}
		}

		return $this;
	}

	/**
	 * Parses the namespace name.
	 *
	 * @param \TokenReflection\Stream Token substream
	 * @return \TokenReflection\ReflectionFileNamespace
	 */
	protected function parseName(Stream $tokenStream)
	{
		if (!$tokenStream->is(T_NAMESPACE)) {
			$this->name = ReflectionNamespace::NO_NAMESPACE_NAME;
			return $this;
		}

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

			$tokenStream->next();
		}

		$name = ltrim($name, '\\');

		if (empty($name)) {
			$this->name = ReflectionNamespace::NO_NAMESPACE_NAME;
		} else {
			$this->name = $name;

			$tokenStream->skipWhitespaces();
		}

		return $this;
	}

	/**
	 * Parses other namespaces usage and aliases from the token stream.
	 *
	 * @param \TokenReflection\Stream Token substream
	 * @return \TokenReflection\ReflectionFileNamespace
	 */
	private function parseAliases(Stream $tokenStream)
	{
		if (ReflectionNamespace::NO_NAMESPACE_NAME === $this->name) {
			return $this;
		}

		$aliases = array();

		while (true) {
			if ($tokenStream->is(T_USE)) {
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
						$tokenStream->next();
					}
					$namespaceName = ltrim($namespaceName, '\\');

					if (empty($namespaceName)) {
						throw new RuntimeException('Imported namespace name could not be determined');
					} elseif ('\\' === substr($namespaceName, -1)) {
						throw new RuntimeException(sprintf('Invalid namespace name "%s"', $namespaceName));
					}

					$tokenStream->skipWhitespaces(false);

					if ($tokenStream->is(T_AS)) {
						// Alias defined
						$tokenStream->skipWhitespaces();

						if (!$tokenStream->is(T_STRING)) {
							throw new RuntimeException(sprintf('The imported namespace "%s" seems aliased but the alias name could not be determined', $namespaceName));
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
						throw new RuntimeException(sprintf('Namespace alias "%s" already defined', $alias));
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

					throw new RuntimeException(sprintf('Invalid token found: "%s"', $tokenStream->getTokenName()));
				}
			} else {
				break;
			}
		}

		$this->aliases = $aliases;

		return $this;
	}
}
