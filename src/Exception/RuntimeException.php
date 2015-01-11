<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Exception;

use ApiGen\TokenReflection\IReflection;


/**
 * Runtime exception raised when working with a reflection element.
 */
class RuntimeException extends BaseException
{

	/**
	 * The property/method is not accessible.
	 *
	 * @var integer
	 */
	const NOT_ACCESSBILE = 3002;

	/**
	 * The reflection element that caused this exception to be raised.
	 *
	 * @var ApiGen\TokenReflection\IReflection
	 */
	private $sender;


	/**
	 * Constructor.
	 *
	 * @param string $message Exception message
	 * @param integer $code Exception code
	 * @param ApiGen\TokenReflection\IReflection $sender Reflection element
	 */
	public function __construct($message, $code, IReflection $sender = NULL)
	{
		parent::__construct($message, $code);
		$this->sender = $sender;
	}


	/**
	 * Returns the reflection element that caused the exception to be raised.
	 *
	 * @return ApiGen\TokenReflection\IReflection
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
	public function getDetail()
	{
		return NULL === $this->sender ? '' : sprintf('Thrown when working with "%s".', $this->sender->getPrettyName());
	}

}
