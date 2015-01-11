<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Exception;

use ApiGen\TokenReflection\ReflectionFile;


/**
 * Processing exception thrown by the library if a file could not be processed.
 */
final class FileProcessingException extends RuntimeException
{

	/**
	 * Resons why the file could not be processed.
	 *
	 * @var array
	 */
	private $reasons = [];


	/**
	 * Constructor.
	 *
	 * @param array $reasons Resons why the file could not be processed
	 * @param ApiGen\TokenReflection\ReflectionFile $sender Reflection file
	 */
	public function __construct(array $reasons, ReflectionFile $sender = NULL)
	{
		parent::__construct(
			NULL === $sender ? 'There was an error processing the file.' : sprintf('There was an error processing the file %s.', $sender->getName()),
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
		if (!empty($this->reasons)) {
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
