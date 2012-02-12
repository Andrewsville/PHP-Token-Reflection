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
	 * The name of the token that caused the exception to be thrown.
	 *
	 * @var string|null
	 */
	private $tokenName;

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

		$token = $tokenStream->current();
		$position = $tokenStream->key();

		if (!empty($token) && !empty($position)) {
			$this->token = $token;
			$this->tokenName = $tokenStream->getTokenName();

			$line = $this->token[2];

			$min = $position;
			while (isset($tokenStream[$min - 1]) && $line - $tokenStream[$min][2] < self::SOURCE_LINES_AROUND) {
				$min--;
			}

			$max = $position;
			while (isset($tokenStream[$max + 1]) && $tokenStream[$max][2] - $line < self::SOURCE_LINES_AROUND) {
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
	 * @param boolean $lineNumbers Returns the source code part with line numbers
	 * @return string|null
	 */
	public function getSourcePart($lineNumbers = false)
	{
		if (empty($this->scopeBoundaries)) {
			return null;
		}

		list($lo, $hi) = $this->scopeBoundaries;
		$stream = $this->getStream();

		$code = $stream->getSourcePart($lo, $hi);

		if ($lineNumbers) {
			$lines = explode("\n", $code);

			$startLine = $stream[$lo][2];
			$width = strlen($startLine + count($lines) - 1);
			$errorLine = $this->token[2];
			$actualLine = $startLine;

			$code = implode(
				"\n",
				array_map(function($line) use(&$actualLine, $width, $errorLine) {
					return ($actualLine === $errorLine ? '*' : ' ') . str_pad($actualLine++, $width, ' ', STR_PAD_LEFT) . ': ' . $line;
				}, $lines)
			);
		}

		return $code;
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

	/**
	 * Returns an exception description detail.
	 *
	 * @return string
	 */
	protected function getDetail()
	{
		if (0 === $this->getStream()->count()) {
			return parent::getDetail() . 'The token stream was empty.';
		} elseif (empty($this->token)) {
			return parent::getDetail() . 'The token stream was read out of its bounds.';
		} else {
			return parent::getDetail() .
				sprintf(
					"\nThe cause of the exception was the %s token (line %s) in following part of %s source code:\n\n%s",
					$this->tokenName,
					$this->token[2],
					$this->sender && $this->sender->getName() ? $this->sender->getPrettyName() : 'the',
					$this->getSourcePart(true)
				);
		}
	}
}
