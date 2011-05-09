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

use SeekableIterator, Countable, ArrayAccess, InvalidArgumentException, RuntimeException;

/**
 * Token stream iterator.
 */
class Stream implements SeekableIterator, Countable, ArrayAccess
{
	/**
	 * Token source file name.
	 *
	 * @var string
	 */
	private $filename = 'unknown';

	/**
	 * Cache of token types.
	 *
	 * @var array
	 */
	private $types = array();

	/**
	 * Cache of token contents.
	 *
	 * @var array
	 */
	private $contents = array();

	/**
	 * Tokens storage.
	 *
	 * @var array
	 */
	private $tokens = array();

	/**
	 * Internal pointer.
	 *
	 * @var integer
	 */
	private $position = 0;

	/**
	 * Token stream size.
	 *
	 * @var integer
	 */
	private $count = 0;

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
		$this->filename = $filename;
		$this->tokens = $stream;
		$this->count = count($stream);

		$types = array();
		$contents = array();
		array_walk($this->tokens, function($token) use(&$types, &$contents) {
			list($types[], $contents[]) = $token;
		});

		$this->types = $types;
		$this->contents = $contents;
	}

	/**
	 * Checks of there is a token with the given index.
	 *
	 * @param integer $offset Token index
	 * @return boolean
	 */
	public function offsetExists($offset)
	{
		return isset($this->tokens[$offset]);
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
	 * Returns a token at the given index.
	 *
	 * @param integer $offset Token index
	 * @return mixed
	 */
	public function offsetGet($offset)
	{
		return isset($this->contents[$offset]) ? $this->contents[$offset] : null;
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
	 * Returns the current internal pointer value.
	 *
	 * @return integer
	 */
	public function key()
	{
		return $this->position;
	}

	/**
	 * Advances the internal pointer.
	 *
	 * @return \TokenReflection\Stream
	 */
	public function next()
	{
		$this->position++;
		return $this;
	}

	/**
	 * Sets the internal pointer to zero.
	 *
	 * @return \TokenReflection\Stream
	 */
	public function rewind()
	{
		$this->position = 0;
		return $this;
	}

	/**
	 * Returns the current token.
	 *
	 * @return array|null
	 */
	public function current()
	{
		return isset($this->tokens[$this->position]) ? $this->tokens[$this->position] : null;
	}

	/**
	 * Checks if there is a token on the current position.
	 *
	 * @return boolean
	 */
	public function valid()
	{
		return isset($this->tokens[$this->position]);
	}

	/**
	 * Returns the number of tokens in the stream.
	 *
	 * @return integer
	 */
	public function count()
	{
		return $this->count;
	}

	/**
	 * Sets the internal pointer to the given value.
	 *
	 * @param integer $position New position
	 * @return \TokenReflection\Stream
	 */
	public function seek($position)
	{
		$this->position = (int) $position;
		return $this;
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

		if (!$this->valid()) {
			throw new InvalidArgumentException('Out of array');
		}

		$position = $this->position;

		$bracket = $this->contents[$this->position];

		if (isset($brackets[$bracket])) {
			$searching = $brackets[$bracket];
		} else {
			throw new InvalidArgumentException(sprintf('There is no usable bracket at position [%d] in file [%s]', $this->key(), $this->filename));
		}

		$level = 0;
		while (isset($this->tokens[$this->position])) {
			$type = $this->types[$this->position];
			if ($searching === $type) {
				$level--;
			} elseif ($bracket === $type || ($searching === '}' && (T_CURLY_OPEN === $type || T_DOLLAR_OPEN_CURLY_BRACES === $type))) {
				$level++;
			}

			if (0 === $level) {
				return $this;
			}

			$this->position++;
		}

		throw new RuntimeException(sprintf('Could not find the matching bracket "%s" of the bracket at position [%d] in file [%s]', $searching, $position, $this->filename));
	}

	/**
	 * Skips whitespaces and comments next to the current position.
	 *
	 * @param boolean $startAtNext Start with the next token
	 * @return \TokenReflection\Stream
	 */
	public function skipWhitespaces($startAtNext = true)
	{
		if ($startAtNext && isset($this->tokens[$this->position])) {
			$this->position++;
		}

		while (isset($this->types[$this->position])) {
			if (T_WHITESPACE === $this->types[$this->position] || T_COMMENT === $this->types[$this->position]) {
				$this->position++;
				continue;
			}

			break;
		}

		return $this;
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
			$position = $this->position;
		}

		return isset($this->types[$position]) ? $this->types[$position] : null;
	}

	/**
	 * Returns the current token value.
	 *
	 * @param integer $position Token position; if none given, consider the current iteration position
	 * @return stirng
	 */
	public function getTokenValue($position = -1)
	{
		if (-1 === $position) {
			$position = $this->position;
		}

		return isset($this->contents[$position]) ? $this->contents[$position] : null;
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
		return token_name($type) ?: $type;
	}

	/**
	 * Returns the stream source code.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return self::tokensToCode($this->contents);
	}

	/**
	 * Converts a token array or iterator to the appropriate source code.
	 *
	 * @param array $tokens Token array
	 * @return string
	 */
	public static function tokensToCode(array $tokens = array())
	{
		return implode('', $tokens);
	}
}
