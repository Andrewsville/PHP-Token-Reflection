<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Exception;

use ApiGen\TokenReflection\Broker;

/**
 * Exception raised when working with the Broker.
 */
class BrokerException extends BaseException
{
	/**
	 * Processed file name.
	 *
	 * @var ApiGen\TokenReflection\Broker
	 */
	private $broker;

	/**
	 * Constructor.
	 *
	 * @param ApiGen\TokenReflection\Broker $broker Processed file name
	 * @param string $message Exception message
	 * @param integer $code Exception code
	 * @param ApiGen\TokenReflection\Exception\StreamException $parent Parent exception
	 */
	public function __construct(Broker $broker, $message, $code, StreamException $parent = null)
	{
		parent::__construct($message, $code, $parent);

		$this->broker = $broker;
	}

	/**
	 * Returns the current Broker.
	 *
	 * @return ApiGen\TokenReflection\Broker
	 */
	public function getBroker()
	{
		return $this->broker;
	}

	/**
	 * Returns an exception description detail.
	 *
	 * @return string
	 */
	public function getDetail()
	{
		return '';
	}
}
