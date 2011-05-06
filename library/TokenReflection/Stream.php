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
	 * @return \TokenReflection\Stream
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
