<?php

namespace ApiGen\TokenReflection\Tests;

use ApiGen;
use ApiGen\TokenReflection\Stream\FileStream;


/**
 * Stream test.
 */
class StreamTest extends TestCase
{

	/**
	 * TestCase type.
	 *
	 * @var string
	 */
	protected $type = 'parseerror';


	/**
	 * Tests the (im)possibility to unset a token from a token stream.
	 *
	 * @expectedException ApiGen\TokenReflection\Exception\StreamException
	 */
	public function testUnsetException()
	{
		$stream = $this->getFileStream('invalid-stream');
		unset($stream[666]);
	}


	/**
	 * Tests the (im)possibility to set a token in a token stream.
	 *
	 * @expectedException ApiGen\TokenReflection\Exception\StreamException
	 */
	public function testSetException()
	{
		$stream = $this->getFileStream('invalid-stream');
		$stream[0] = NULL;
	}


	/**
	 * Tests an exception thrown when calling findMatchingBracket and the current token is not a bracket.
	 *
	 * @expectedException ApiGen\TokenReflection\Exception\StreamException
	 */
	public function testFindMatchingBracketException1()
	{
		$this->getFileStream('invalid-stream')->findMatchingBracket();
	}


	/**
	 * Tests an exception thrown when no matching bracket could be found.
	 *
	 * @expectedException ApiGen\TokenReflection\Exception\StreamException
	 */
	public function testFindMatchingBracketException2()
	{
		$stream = $this->getFileStream('invalid-stream');
		$this->assertInstanceOf('ApiGen\TokenReflection\Stream\FileStream', $stream->find('{'));

		$stream->findMatchingBracket();
	}


	/**
	 * Tests an exception thrown when calling findMatchingBracket and being beyond the end of the token.
	 *
	 * @expectedException ApiGen\TokenReflection\Exception\StreamException
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
	 * @expectedException ApiGen\TokenReflection\Exception\StreamException
	 */
	public function testOpeningNonExistentFileException1()
	{
		$file = __DIR__ . DIRECTORY_SEPARATOR . '~#nonexistent#~';

		if (is_file($file)) {
			$this->markTestSkipped(sprintf('File %s exists.', $file));
		}
		new FileStream($file);
	}


	/**
	 * Tests an exception thrown when trying to load a non existent file.
	 *
	 * @expectedException ApiGen\TokenReflection\Exception\StreamException
	 */
	public function testOpeningNonExistentFileException2()
	{
		$file = __DIR__ . DIRECTORY_SEPARATOR . '~#nonexistent#~' . DIRECTORY_SEPARATOR . '~#nonexistent#~';

		if (is_file($file)) {
			$this->markTestSkipped(sprintf('File %s exists.', $file));
		}
		new FileStream($file);
	}


	/**
	 * @param string $name File name
	 * @return FileStream
	 */
	private function getFileStream($name)
	{
		return new FileStream($this->getFilePath($name));
	}
}
