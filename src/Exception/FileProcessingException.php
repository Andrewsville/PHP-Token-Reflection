<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Exception;

use ApiGen\TokenReflection\Reflection\ReflectionFile;


class FileProcessingException extends RuntimeException
{

	/**
	 * Reasons while file could not be processed.
	 *
	 * @var array
	 */
	private $reasons = [];


	public function __construct(array $reasons)
	{
		parent::__construct('There was an error processing the file.');
		$this->reasons = $reasons;
	}


	/**
	 * @return array
	 */
	public function getReasons()
	{
		return $this->reasons;
	}

}
