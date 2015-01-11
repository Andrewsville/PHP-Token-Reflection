<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Stream;

/**
 * Token stream iterator created from a string.
 */
class StringStream extends StreamBase
{

	/**
	 * Creates a token substream from a string.
	 *
	 * @param string $source PHP source code
	 * @param string $fileName File name
	 */
	public function __construct($source, $fileName)
	{
		parent::__construct();
		$this->fileName = $fileName;
		$this->processSource($source);
	}

}
