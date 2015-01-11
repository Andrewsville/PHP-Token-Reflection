<?php

namespace ApiGen\TokenReflection\Tests;

use ApiGen;
use ApiGen\TokenReflection\Broker;


class BrokerTest extends TestCase
{

	/**
	 * Tests processing of an empty file.
	 */
	public function testEmptyFileProcessing()
	{
		$this->getFileTokenReflection('empty');
	}

	public function testFindFiles()
	{
		$broker = new Broker(new Broker\Backend\Memory);
		$files = $broker->processDirectory(realpath(__DIR__ . '/data/class'), true);
		$this->assertCount(39, $files);
	}


	/**
	 * Tests an exception thrown when a file could not be processed.
	 *
	 * @expectedException ApiGen\TokenReflection\Exception\BrokerException
	 */
	public function testFileProcessingError()
	{
		$file = __DIR__ . DIRECTORY_SEPARATOR . '~#nonexistent#~';

		if (is_file($file)) {
			$this->markTestSkipped(sprintf('File %s exists.', $file));
		}

		$this->getBroker()->processFile($file);
	}

	/**
	 * Tests an exception thrown when a file could not be processed.
	 *
	 * @expectedException ApiGen\TokenReflection\Exception\BrokerException
	 */
	public function testDirectoryProcessingError()
	{
		$file = __DIR__ . DIRECTORY_SEPARATOR . '~#nonexistent#~' . DIRECTORY_SEPARATOR . '~#nonexistent#~';

		if (is_dir($file)) {
			$this->markTestSkipped(sprintf('Directory %s exists.', $file));
		}

		$this->getBroker()->processDirectory($file);
	}


	/**
	 * Tests an exception thrown when a file could not be processed.
	 *
	 * @expectedException ApiGen\TokenReflection\Exception\BrokerException
	 */
	public function testProcessingError()
	{
		$file = __DIR__ . DIRECTORY_SEPARATOR . '~#nonexistent#~';

		if (is_file($file)) {
			$this->markTestSkipped(sprintf('File %s exists.', $file));
		}

		$this->getBroker()->process($file);
	}

	/**
	 * PhpUnit does not seem to let one compare two arrays without having to
	 * have elements in the same order (which is not important at all here).
	 *
	 * @param array $expected
	 * @param array $actual
	 */
	private function compareFileLists(array $expected, array $actual)
	{
		$this->assertSame(count($expected), count($actual));
		foreach ($expected as $fileName) {
			$this->assertTrue(in_array($fileName, $actual));
		}
	}

}
