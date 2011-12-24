<?php
/**
 * PHP Token Reflection
 *
 * Version 1.0.0
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this library in the file LICENSE.
 *
 * @author Ondřej Nešpor
 * @author Jaroslav Hanslík
 */

namespace TokenReflection\Stream;

use TokenReflection\Broker as Broker;

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
	 * @throws \TokenReflection\Exception\Parse If file does not exist or is not readable.
	 */
	public function __construct($fileName)
	{
		parent::__construct();

		$this->fileName = Broker::getRealPath($fileName);

		if (false === $this->fileName) {
			throw new Exception\Parse('File does not exist.', Exception\Parse::FILE_DOES_NOT_EXIST);
		}

		$contents = file_get_contents($this->fileName);
		if (false === $contents) {
			throw new Exception\Parse('File is not readable.', Exception\Parse::FILE_NOT_READABLE);
		}

		$this->processSource($contents);
	}
}