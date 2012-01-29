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

namespace TokenReflection\Exception;

use TokenReflection\Stream\StreamBase;
use TokenReflection\IReflection;

/**
 * Parse exception.
 *
 * Thrown while parsing source codes.
 */
class ParseException extends StreamException
{
	/**
	 * An unexpected  token was encountered.
	 *
	 * @var integer
	 */
	const UNEXPECTED_TOKEN = 1101;

	/**
	 * A logical error was encountered.
	 *
	 * @var integer
	 */
	const LOGICAL_ERROR = 1102;

	/**
	 * An invalid reflection parent was provided.
	 *
	 * @var integer
	 */
	const INVALID_PARENT = 1103;

	/**
	 * Minimal number of source code lines around the token.
	 *
	 * @var integer
	 */
	const SOURCE_LINES_AROUND = 5;

	/**
	 * The token that caused the expection to be thrown.
	 *
	 * @var array|null
	 */
	private $token;

	/**
	 * Boundaries of the token substream around the token.
	 *
	 * @var array
	 */
	private $scopeBoundaries = array();

	/**
	 * The reflection element that caused this exception to be raised.
	 *
	 * @var \TokenReflection\IReflection
	 */
	private $sender;

	/**
	 * Constructor.
	 *
	 * @param \TokenReflection\IReflection $sender Reflection element
	 * @param \TokenReflection\Stream\StreamBase $tokenStream Token stream
	 * @param string $message Exception message
	 * @param integer $code Exception code
	 */
	public function __construct(IReflection $sender, StreamBase $tokenStream, $message, $code)
	{
		parent::__construct($tokenStream, $message, $code);

		$this->sender = $sender;

		$token = current($tokenStream);
		$position = key($tokenStream);
		if (!empty($token) && !empty($position)) {
			$this->token = $token;

			$line = $this->token[2];

			$min = $position;
			while (isset($tokenStream[$min]) && $line - $tokenStream[$min][2] < self::SOURCE_LINES_AROUND) {
				$min--;
			}

			$max = $position;
			while (isset($tokenStream[$max]) && $tokenStream[$max][2] - $line < self::SOURCE_LINES_AROUND) {
				$max++;
			}

			$this->scopeBoundaries = array($min, $max);
		}
	}

	/**
	 * Returns the token where the problem was detected or NULL if the token stream was empty or an end was reached.
	 *
	 * @return array|null
	 */
	public function getToken()
	{
		return $this->token;
	}

	/**
	 * Returns the file line with the token or null.
	 *
	 * @return integer|null
	 */
	public function getTokenLine()
	{
		return null === $this->token ? null : $this->token[2];
	}

	/**
	 * Returns the source code part around the token.
	 *
	 * @return string|null
	 */
	public function getSourcePart()
	{
		if (!empty($this->scopeBoundaries)) {
			list($lo, $hi) = $this->scopeBoundaries;
			return $this->getStream()->getSourcePart($lo, $hi);
		}

		return null;
	}

	/**
	 * Returns the reflection element that caused the exception to be raised.
	 *
	 * @return \TokenReflection\IReflection
	 */
	public function getSender()
	{
		return $this->sender;
	}
}
