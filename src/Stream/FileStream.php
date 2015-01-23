<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Stream;

use ApiGen\TokenReflection\Broker\Broker;
use ApiGen\TokenReflection\Exception\StreamException;


class FileStream extends StreamBase
{

	/**
	 * Creates a token substream from a file.
	 *
	 * @param string $fileName
	 * @throws StreamException If the file does not exist or is not readable.
	 */
	public function __construct($fileName)
	{
		$this->fileName = realpath($fileName);
		if ($this->fileName === FALSE) {
			throw new StreamException('File does not exist.');
		}
		$contents = @file_get_contents($this->fileName);
		if ($contents === FALSE) {
			throw new StreamException('File is not readable.');
		}
		$this->processSource($contents);
	}

}
