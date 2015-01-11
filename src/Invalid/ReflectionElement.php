<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Invalid;

use ApiGen\TokenReflection\Exception\BaseException;


abstract class ReflectionElement
{

	/**
	 * Reasons why this element's reflection is invalid.
	 *
	 * @var BaseException[]
	 */
	private $reasons = [];


	/**
	 * @return $this
	 */
	public function addReason(BaseException $reason)
	{
		$this->reasons[] = $reason;
		return $this;
	}


	/**
	 * @return array
	 */
	public function getReasons()
	{
		return $this->reasons;
	}


	/**
	 * @return bool
	 */
	public function hasReasons()
	{
		return ! empty($this->reasons);
	}

}
