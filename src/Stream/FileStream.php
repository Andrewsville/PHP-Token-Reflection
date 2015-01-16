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


/**
 * Token stream iterator created from a file.
 */
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
		$this->fileName = Broker::getRealPath($fileName);
		if (FALSE === $this->fileName) {
			throw new StreamException($this, 'File does not exist.', StreamException::DOES_NOT_EXIST);
		}
		$contents = @file_get_contents($this->fileName);
		if (FALSE === $contents) {
			throw new StreamException($this, 'File is not readable.', StreamException::NOT_READABLE);
		}
		$this->processSource($contents);
	}

}
