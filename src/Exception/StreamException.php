<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Exception;


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

}
