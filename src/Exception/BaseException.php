<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Exception;

use RuntimeException;


abstract class BaseException extends RuntimeException
{

	/**
	 * The property/element does not exist.
	 *
	 * @var int
	 */
	const DOES_NOT_EXIST = 1;

	/**
	 * An invalid argument was provided.
	 *
	 * @var int
	 */
	const INVALID_ARGUMENT = 2;

	/**
	 * A required PHP extension is missing.
	 *
	 * @var int
	 */
	const PHP_EXT_MISSING = 3;

	/**
	 * The requested feature is not supported.
	 *
	 * @var int
	 */
	const UNSUPPORTED = 4;

	/**
	 * The reflected element already exists.
	 *
	 * @var int
	 */
	const ALREADY_EXISTS = 5;


	/**
	 * Returns an exception description detail.
	 *
	 * @return string
	 */
	public abstract function getDetail();


	/**
	 * Returns an exception description as string.
	 *
	 * @return string
	 */
	public function getOutput()
	{
		$detail = $this->getDetail();
		return sprintf(
			"exception '%s'%s in %s on line %d\n%s\nStack trace:\n%s",
			get_class($this),
			$this->getMessage() ? " with message '" . $this->getMessage() . "'" : '',
			$this->getFile(),
			$this->getLine(),
			empty($detail) ? '' : $detail . "\n",
			$this->getTraceAsString()
		);
	}


	/**
	 * Returns the exception details as string.
	 *
	 * @return string
	 */
	public function __toString()
	{
		$output = '';
		if ($ex = $this->getPrevious()) {
			$output .= (string) $ex . "\n\nNext ";
		}
		return $output . $this->getOutput() . "\n";
	}

}
