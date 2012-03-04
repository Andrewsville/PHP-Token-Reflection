<?php
/**
 * PHP Token Reflection
 *
 * Version 1.1
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this library in the file LICENSE.
 *
 * @author Ondřej Nešpor
 * @author Jaroslav Hanslík
 */

namespace TokenReflection\Exception;

use TokenReflection\ReflectionFile;

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
	private $reasons = array();

	/**
	 * Constructor.
	 *
	 * @param array $reasons Resons why the file could not be processed
	 * @param \TokenReflection\ReflectionFile $sender Reflection file
	 */
	public function __construct(array $reasons, ReflectionFile $sender = null)
	{
		parent::__construct('There was an error processing the file.', 0, $sender);

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
		$detail = parent::getDetail();

		$reasons = array_map(function(BaseException $reason) {
			return get_class($reason) . ' ' . $reason->getDetail();
		}, $this->reasons);

		if (!empty($reasons)) {
			if (!empty($detail)) {
				$detail .= "\n";
			}

			$detail .= "There were following reasons for this exception:\n" . implode("\n", $reasons);
		}

		return $detail;
	}
}
