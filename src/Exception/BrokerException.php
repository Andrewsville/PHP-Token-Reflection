<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Exception;

use ApiGen\TokenReflection\Broker\Broker;


class BrokerException extends BaseException
{

	/**
	 * @var Broker
	 */
	private $broker;


	/**
	 * @param Broker $broker
	 * @param string $message
	 * @param int $code
	 * @param StreamException $parent
	 */
	public function __construct(Broker $broker, $message, $code, StreamException $parent = NULL)
	{
		parent::__construct($message, $code, $parent);
		$this->broker = $broker;
	}


	/**
	 * @return Broker
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
