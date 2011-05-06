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

use ArrayIterator, InvalidArgumentException, RuntimeException;

/**
 * Token stream iterator.
 */
class Stream extends ArrayIterator
{
	/**
	 * Token source file name.
	 *
	 * @var string
	 */
	private $filename = 'unknown';

	/**
	 * Constructor.
	 *
	 * Creates a token substream.
	 *
	 * @param array $stream Base stream
	 * @param string $filename File name
	 * @return \TokenReflection\Stream
	 */
	public function __construct(array $stream, $filename)
	{
		parent::__construct($stream);
		$this->filename = $filename;
	}

	/**
	 * Returns the current token value.
	 *
	 * @return stirng
	 */
	public function getTokenValue()
	{
		$token = $this->current();
		return $token[1];
	}

	/**
	 * Removes a token.
	 *
	 * Unsupported.
	 *
	 * @param integer $offset Position
	 */
	public function offsetUnset($offset)
	{
		throw new Exception('Removing of tokens from the stream is not supported.', Exception::UNSUPPORTED);
	}

	/**
	 * Sets a value of a particular token.
	 *
	 * Unsupported
	 *
	 * @param integer $offset Position
	 * @param mixed $value Value
	 */
	public function offsetSet($offset, $value)
	{
		throw new Exception('Setting token values is not supported.', Exception::UNSUPPORTED);
	}

	/**
	 * Returns the file name this is a part of.
	 *
	 * @return string
	 */
	public function getFileName()
	{
		return $this->filename;
	}

	/**
	 * Returns a token substream containing the whole class definition
	 * including the docblock.
	 *
	 * @return \TokenReflection\Stream
	 * @throws \InvalidArgumentException If there is no class keyword at the given position
	 * @throws \TokenReflection\Exception If the token stream is invalid
	 */
	public function getClassStream()
	{
		$type = $this->getType();
		if (T_CLASS !== $type && T_INTERFACE !== $type) {
			throw new InvalidArgumentException(sprintf('There is no T_CLASS nor T_INTERFACE keyword at position [%d] in file [%s]', $this->key(), $this->filename));
		}

		static $prefixes = array(
			T_COMMENT,
			T_WHITESPACE,
			T_ABSTRACT,
			T_FINAL,
		);

		// Find the class declaration start
		$start = $this->key();
		while (in_array($this->getType($start - 1), $prefixes, true)) {
			$start--;
		}

		// Find the preceding docblock
		$start = $this->findPrecedingDocComment($ex = $start);

		if ($ex === $start) {
			while ($this->is(T_WHITESPACE, $start)) {
				$start++;
			}
		}

		// And let's find the definition end
		// First find the opening bracket
		$this->next();
		while (null !== ($type = $this->getType()) && '{' !== $type) {
			$this->skipWhitespaces();
		}

		if ('{' !== $type) {
			throw new RuntimeException(sprintf('Could not find the beginning of the class/interface with keyword at position [%d] in file [%s]', $this->key(), $this->filename));
		}

		$this->findMatchingBracket();

		return new Stream(array_slice($this->getArrayCopy(), $start, $this->key() - $start + 1), $this->filename);
	}

	/**
	 * Returns a token substream containing the whole namespace definition
	 * including the docblock.
	 *
	 * @return \TokenReflection\Stream
	 * @throws \InvalidArgumentException If there is no namespace keyword at the given position
	 * @throws \TokenReflection\Exception If the token stream is invalid
	 */
	public function getNamespaceStream()
	{
		if (!$this->is(T_NAMESPACE)) {
			throw new InvalidArgumentException(sprintf('There is no T_NAMESPACE keyword at position [%d] in file [%s]', $this->key(), $this->filename));
		}

		// Look if there is a docblock preceding the namespace keyword
		// If so, we will assume it belong to the namespace
		$start = $this->findPrecedingDocComment($this->key());

		// Let's find out how is the namespace defined
		while (null !== ($type = $this->getType()) && ';' !== $type && '{' !== $type) {
			$this->skipWhitespaces();
		}

		// Let's find the namespace definition end
		if (';' === $type) {
			// The namespace is not defined as a block; let's find next namespace definition stream end
			while (null !== ($key = $this->key()) && (!$this->is(T_NAMESPACE, $key + 1) || $this->is(T_NS_SEPARATOR, $key + 2))) {
				$this->next();
			}
		} elseif ('{' === $type) {
			// The namespace is defined as a block, let's find the closing bracket
			$this->findMatchingBracket();
		} else {
			throw new RuntimeException(sprintf('Could not find the beginning of the namespace with keywords at position [%d] in file [%s]', $end, $this->filename));
		}

		return new Stream(array_slice($this->getArrayCopy(), $start, ($this->key() ?: count($this) - 1) - $start + 1), $this->filename);
	}

	/**
	 * Returns a token substream containing the whole function definition
	 * including the docblock.
	 *
	 * @return \TokenReflection\Stream
	 */
	public function getFunctionStream()
	{
		if (!$this->is(T_FUNCTION)) {
			throw new InvalidArgumentException(sprintf('There is no T_FUNCTION keyword at position [%d] in file [%s]', $this->key(), $this->filename));
		}

		$start = $this->findPrecedingDocComment($this->key());

		$this->next();
		// Let's find out how is the namespace defined
		while (null !== ($type = $this->getType()) && ';' !== $type && '{' !== $type) {
			$this->skipWhitespaces();
		}

		if ('{' === $type) {
			$this->findMatchingBracket();
		} elseif (';' !== $type) {
			throw new RuntimeException(sprintf('Could not find the beginning of the function body definition with keywords at position [%d] in file [%s]', $this->key(), $this->filename));
		}

		return new Stream(array_slice($this->getArrayCopy(), $start, $this->key() - $start + 1), $this->filename);
	}

	/**
	 * Returns a token substream containing the whole constant definition
	 * including the docblock.
	 *
	 * @return \TokenReflection\Stream
	 */
	public function getConstantStream()
	{
		if (!$this->is(T_STRING)) {
			throw new InvalidArgumentException(sprintf('There is no T_STRING keyword at position [%d] in file [%s]', $this->key(), $this->filename));
		}

		static $prefixes = array(
			T_COMMENT,
			T_WHITESPACE,
		);

		// Find the constant declaration start
		$start = $this->key();
		while (in_array($type = $this->getType($start - 1), $prefixes, true)) {
			$start--;
		}
		if (T_CONST === $type) {
			$start = $this->findPrecedingDocComment($start - 1) ;
		} else {
			$start = $this->key();
		}

		while (null !== ($type = $this->getType()) && ';' !== $type && ',' !== $type) {
			$this->next();
		}

		if (';' !== $type && ',' !== $type) {
			throw new RuntimeException(sprintf('Could not find the end of constant definition at position [%d] in file [%s]', $this->key(), $this->filename));
		}

		return new Stream(array_slice($this->getArrayCopy(), $start, $this->key() - $start + 1), $this->filename);
	}

	/**
	 * Returns a token substream containing the whole class method definition
	 * including the docblock.
	 *
	 * @return \TokenReflection\Stream
	 */
	public function getMethodStream()
	{
		if (!$this->is(T_FUNCTION)) {
			throw new InvalidArgumentException(sprintf('There is no T_FUNCTION keyword at position [%d] in file [%s]', $this->key(), $this->filename));
		}

		$start = $this->key();

		static $acceptedPrevious = array(
			T_PUBLIC,
			T_PROTECTED,
			T_STATIC,
			T_PRIVATE,
			T_WHITESPACE,
			T_COMMENT,
			T_ABSTRACT,
			T_FINAL
		);
		while (null !== ($type = $this->getType($start - 1)) && in_array($type, $acceptedPrevious)) {
			$start--;
		}

		$start = $this->findPrecedingDocComment($ex = $start);
		if ($start === $ex) {
			while (null !== ($type = $this->getType($start)) && (T_WHITESPACE === $type || T_COMMENT === $type)) {
				$start++;
			}
		}

		$this->next();

		while (null !== ($type = $this->getType()) && '{' !== $type && ';' !== $type) {
			$this->next();
		}

		if ('{' === $type) {
			$this->findMatchingBracket();
		} elseif (';' !== $type) {
			throw new RuntimeException(sprintf('Could not find the beginning of the class method body definition with keywords at position [%d] in file [%s]', $this->key(), $this->filename));
		}

		return new Stream(array_slice($this->getArrayCopy(), $start, $this->key() - $start + 1), $this->filename);
	}

	/**
	 * Returns a token substream containing the whole function/method parameter definition.
	 *
	 * @return \TokenReflection\Stream
	 */
	public function getParameterStream()
	{
		if (!$this->is(T_VARIABLE)) {
			throw new InvalidArgumentException(sprintf('There is no T_VARIABLE keyword at position [%d] in file [%s]', $this->key(), $this->filename));
		}

		$start = $this->key();

		static $acceptedPrevious = array(
			T_WHITESPACE,
			T_COMMENT,
			T_ARRAY,
			T_STRING,
			T_NS_SEPARATOR,
			'&'
		);
		while (null !== ($type = $this->getType($start - 1)) && in_array($type, $acceptedPrevious)) {
			$start--;
		}

		while (null !== ($type = $this->getType($start)) && T_WHITESPACE === $type || T_COMMENT === $type) {
			$start++;
		}

		$this->next();

		while (null !== ($type = $this->getType()) && ',' !== $type && ')' !== $type) {
			if ('(' === $type) {
				$this->findMatchingBracket();
			}
			$this->next();
		}

		if (null === $type) {
			throw new RuntimeException(sprintf('Could not find the end of the parameter definition with keywords at position [%d] in file [%s]', $this->key(), $this->filename));
		}

		return new Stream(array_slice($this->getArrayCopy(), $start, $this->key() - $start), $this->filename);
	}

	/**
	 * Returns a token substream containing the whole class property definition.
	 *
	 * @return \TokenReflection\Stream
	 */
	public function getPropertyStream()
	{
		if (!$this->is(T_VARIABLE)) {
			throw new InvalidArgumentException(sprintf('There is no T_VARIABLE keyword at position [%d] in file [%s]', $this->key(), $this->filename));
		}

		$start = $this->key();

		static $acceptedPrevious = array(
			T_PUBLIC,
			T_PROTECTED,
			T_STATIC,
			T_PRIVATE,
			T_WHITESPACE,
			T_COMMENT,
			T_VAR
		);
		while (null !== ($type = $this->getType($start - 1)) && in_array($type, $acceptedPrevious)) {
			$start--;
		}

		$start = $this->findPrecedingDocComment($ex = $start);
		if ($ex === $start) {
			while (null !== ($type = $this->getType($start)) && (T_WHITESPACE === $type || T_COMMENT === $type)) {
				$start++;
			}
		}

		$this->next();

		while (null !== ($type = $this->getType()) && ';' !== $type && ',' !== $type) {
			if (T_ARRAY === $type) {
				$this->skipWhitespaces();
				$this->findMatchingBracket();
			} else {
				$this->next();
			}
		}

		if (';' !== $type && ',' !== $type) {
			throw new RuntimeException(sprintf('Could not find the end of the class property definition with keywords at position [%d] in file [%s]', $this->key(), $this->filename));
		}

		return new Stream(array_slice($this->getArrayCopy(), $start, $this->key() - $start + 1), $this->filename);
	}

	/**
	 * Returns the position of a directly preceding Docblock.
	 *
	 * @param integer $position Position in the token stream
	 * @return integer
	 */
	public function findPrecedingDocComment($position)
	{
		$prev = $this->getType($position - 1);
		if (T_DOC_COMMENT === $prev) {
			$position--;
		} elseif (T_WHITESPACE === $prev && T_DOC_COMMENT === $this->getType($position - 2)) {
			$position -= 2;
		}

		return $position;
	}

	/**
	 * Returns the position of the token with the matching bracket.
	 *
	 * @return \TokenReflection\Stream
	 * @throws \InvalidArgumentException If there is no bracket at the given position
	 * @throws \RuntimeException If the matching bracket could not be found
	 */
	public function findMatchingBracket()
	{
		static $brackets = array(
			'(' => ')',
			'{' => '}',
			'[' => ']'
		);

		$position = $this->key();
		$bracket = $this->getType();

		if (isset($brackets[$bracket])) {
			$searching = $brackets[$bracket];
		} else {
			throw new InvalidArgumentException(sprintf('There is no usable bracket at position [%d] in file [%s]', $this->key(), $this->filename));
		}

		$level = 0;
		while (null !== ($type = $this->getType())) {
			if ($bracket === $type || ($searching === '}' && (T_CURLY_OPEN === $type || T_DOLLAR_OPEN_CURLY_BRACES === $type))) {
				$level++;
			} elseif ($searching === $type) {
				$level--;
			}

			if (0 === $level) {
				return $this;
			}

			$this->next();
		}

		throw new RuntimeException(sprintf('Could not find the matching bracket "%s" of the bracket at position [%d] in file [%s]', $searching, $position, $this->filename));
	}

	/**
	 * Skips whitespaces and comments next to the current position.
	 *
	 * @param boolean $startAtNext Start with the next token
	 */
	public function skipWhitespaces($startAtNext = true)
	{
		if ($this->valid() && $startAtNext) {
			$this->next();
		}
		while (true) {
			$type = $this->getType();
			if ($type === T_WHITESPACE || $type === T_COMMENT) {
				$this->next();
				continue;
			}

			break;
		}
	}

	/**
	 * Checks if there is a token of the given type at the given position.
	 *
	 * @param integer|string $type Token type
	 * @param integer $position Position; if none given, consider the current iteration position
	 * @return boolean
	 */
	public function is($type, $position = -1)
	{
		return $type === $this->getType($position);
	}

	/**
	 * Returns the type of a token.
	 *
	 * @param integer $position Token position; if none given, consider the current iteration position
	 * @return string|integer|null
	 */
	public function getType($position = -1)
	{
		if (-1 === $position) {
			$token = $this->current();
			return $token[0];
		} else {
			return isset($this[$position]) ? $this[$position][0] : null;
		}
	}

	/**
	 * Returns the token type name.
	 *
	 * @param integer $position Token position; if none given, consider the current iteration position
	 * @return string|null
	 */
	public function getTokenName($position = -1)
	{
		$type = $this->getType($position);
		return @token_name($type) ?: $type;
	}

	/**
	 * Returns the stream source code.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return self::tokensToCode($this->getArrayCopy());
	}

	/**
	 * Converts a token array or iterator to the appropriate source code.
	 *
	 * @param array|\Traversable $tokens Token array
	 * @return string
	 */
	public static function tokensToCode($tokens)
	{
		if (!is_array($tokens) && (!is_object($tokens) || !$tokens instanceof Traversable)) {
			throw new InvalidArgumentException('You have to provide an array or an iterateable list of tokens');
		}

		$source = '';
		foreach ($tokens as $token) {
			$source .= $token[1];
		}
		return $source;
	}
}
