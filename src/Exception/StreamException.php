<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Exception;

use ApiGen\TokenReflection\Stream\StreamBase;


class StreamException extends BaseException
{

	/**
	 * The property/element does not exist.
	 *
	 * @var int
	 */
	const NOT_READABLE = 1001;

	/**
	 * A required PHP extension is missing.
	 *
	 * @var int
	 */
	const READ_BEYOND_EOS = 1002;

	/**
	 * There was an error when (de)serializing the token stream.
	 *
	 * @var int
	 */
	const SERIALIZATION_ERROR = 1003;

	/**
	 * The token stream that caused this exception to be raised.
	 *
	 * @var ApiGen\TokenReflection\Stream\StreamBase
	 */
	private $stream;


	/**
	 * @param StreamBase $stream Reflection element
	 * @param string $message Exception message
	 * @param int $code Exception code
	 */
	public function __construct(StreamBase $stream, $message, $code)
	{
		parent::__construct($message, $code);
		$this->stream = $stream;
	}


	/**
	 * Returns the reflection element that caused the exception to be raised.
	 *
	 * @return ApiGen\TokenReflection\Stream\StreamBase
	 */
	public function getStream()
	{
		return $this->stream;
	}


	/**
	 * Returns the processed file name.
	 *
	 * @return string
	 */
	public function getFileName()
	{
		return $this->stream->getFileName();
	}


	/**
	 * Returns an exception description detail.
	 *
	 * @return string
	 */
	public function getDetail()
	{
		return sprintf('Thrown when working with file "%s" token stream.', $this->getFileName());
	}

}
