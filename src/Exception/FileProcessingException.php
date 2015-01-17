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
	 * @var array
	 */
	private $reasons = [];


	/**
	 * @param array $reasons
	 * @param \ApiGen\TokenReflection\Reflection\ReflectionFile $sender
	 */
	public function __construct(array $reasons, ReflectionFile $sender = NULL)
	{
		parent::__construct(
			$sender === NULL ? 'There was an error processing the file.' : sprintf('There was an error processing the file %s.', $sender->getName()),
			0,
			$sender
		);
		$this->reasons = $reasons;
	}


	/**
	 * Returns a list of reasons why the file could not be processed.
	 *
	 * @return array
	 */
	public function getReasons()
	{
		return $this->reasons;
	}


	/**
	 * Returns an exception description detail.
	 *
	 * @return string
	 */
	public function getDetail()
	{
		if ( ! empty($this->reasons)) {
			$reasons = array_map(function (BaseException $reason) {
				if ($reason instanceof ParseException) {
					return $reason->getDetail();
				} else {
					return $reason->getMessage();
				}
			}, $this->reasons);
			return "There were following reasons for this exception:\n" . implode("\n", $reasons);
		}
		return '';
	}

}
