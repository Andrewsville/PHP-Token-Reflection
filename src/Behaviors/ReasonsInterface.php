<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Behaviors;

use ApiGen\TokenReflection\Exception\BaseException;


interface ReasonsInterface
{

	function addReason(BaseException $reason);


	/**
	 * @return array
	 */
	function getReasons();


	/**
	 * @return bool
	 */
	public function hasReasons();

}
