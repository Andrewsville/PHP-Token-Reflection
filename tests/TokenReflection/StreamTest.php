<?php
/**
 * PHP Token Reflection
 *
 * Version 1.3.1
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this library in the file LICENSE.
 *
 * @author Ondřej Nešpor
 * @author Jaroslav Hanslík
 */

namespace TokenReflection;

require_once __DIR__ . '/../bootstrap.php';

/**
 * Stream test.
 */
class StreamTest extends Test
{
	/**
	 * Test type.
	 *
	 * @var string
	 */
	protected $type = 'parseerror';

	/**
	 * Tests the (im)possibility to unset a token from a token stream.
	 *
	 * @expectedException \TokenReflection\Exception\StreamException
	 */
	public function testUnsetException()
	{
		$stream = $this->getFileStream('invalid-stream');
		unset($stream[666]);
	}

	/**
	 * Tests the (im)possibility to set a token in a token stream.
	 *
	 * @expectedException \TokenReflection\Exception\StreamException
	 */
	public function testSetException()
	{
		$stream = $this->getFileStream('invalid-stream');
		$stream[0] = null;
	}

	/**
	 * Tests an exception thrown when calling findMatchingBracket and the current token is not a bracket.
	 *
	 * @expectedException \TokenReflection\Exception\StreamException
	 */
	public function testFindMatchingBracketException1()
	{
		$this->getFileStream('invalid-stream')->findMatchingBracket();
	}

	/**
	 * Tests an exception thrown when no matching bracket could be found.
	 *
	 * @expectedException \TokenReflection\Exception\StreamException
	 */
	public function testFindMatchingBracketException2()
	{
		$stream = $this->getFileStream('invalid-stream');
		$this->assertInstanceOf('\TokenReflection\Stream\FileStream', $stream->find('{'));

		$stream->findMatchingBracket();
	}

	/**
	 * Tests an exception thrown when calling findMatchingBracket and being beyond the end of the token.
	 *
	 * @expectedException \TokenReflection\Exception\StreamException
	 */
	public function testFindMatchingBracketException3()
	{
		$stream = $this->getFileStream('invalid-stream');
		$stream->seek(count($stream));

		$this->assertFalse($stream->valid());
		$stream->findMatchingBracket();
	}

	/**
	 * Tests an exception thrown when trying to load a non existent file.
	 *
	 * @expectedException \TokenReflection\Exception\StreamException
	 */
	public function testOpeningNonExistentFileException1()
	{
		$file = __DIR__ . DIRECTORY_SEPARATOR . '~#nonexistent#~';

		if (is_file($file)) {
			$this->markTestSkipped(sprintf('File %s exists.', $file));
		}
		$stream = new Stream\FileStream($file);
	}

	/**
	 * Tests an exception thrown when trying to load a non existent file.
	 *
	 * @expectedException \TokenReflection\Exception\StreamException
	 */
	public function testOpeningNonExistentFileException2()
	{
		$file = __DIR__ . DIRECTORY_SEPARATOR . '~#nonexistent#~' . DIRECTORY_SEPARATOR . '~#nonexistent#~';

		if (is_file($file)) {
			$this->markTestSkipped(sprintf('File %s exists.', $file));
		}
		$stream = new Stream\FileStream($file);
	}

	/**
	 * Returns a file token stream.
	 *
	 * @param string $name File name
	 * @return \TokenReflection\Stream\FileStream
	 */
	private function getFileStream($name)
	{
		return new Stream\FileStream($this->getFilePath($name));
	}
}
