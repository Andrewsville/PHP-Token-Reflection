<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */
namespace ApiGen\TokenReflection\Stream;

use ApiGen\TokenReflection\Broker as Broker;
use ApiGen\TokenReflection\Exception;


/**
 * Token stream iterator created from a file.
 */
class FileStream extends StreamBase
{

	/**
	 * Constructor.
	 *
	 * Creates a token substream from a file.
	 *
	 * @param string $fileName File name
	 * @throws ApiGen\TokenReflection\Exception\StreamException If the file does not exist or is not readable.
	 */
	public function __construct($fileName)
	{
		parent::__construct();
		$this->fileName = Broker::getRealPath($fileName);
		if (FALSE === $this->fileName) {
			throw new Exception\StreamException($this, 'File does not exist.', Exception\StreamException::DOES_NOT_EXIST);
		}
		$contents = @file_get_contents($this->fileName);
		if (FALSE === $contents) {
			throw new Exception\StreamException($this, 'File is not readable.', Exception\StreamException::NOT_READABLE);
		}
		$this->processSource($contents);
	}
}
