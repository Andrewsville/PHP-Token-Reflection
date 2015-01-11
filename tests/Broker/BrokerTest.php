<?php

namespace ApiGen\TokenReflection\Tests\Broker;

use ApiGen;
use ApiGen\TokenReflection\Broker\Broker;
use ApiGen\TokenReflection\Broker\MemoryBackend;
use ApiGen\TokenReflection\Tests\TestCase;


class BrokerTest extends TestCase
{

	public function testEmptyFileProcessing()
	{
		$this->getFileTokenReflection('empty');
	}


	public function testFindFiles()
	{
		$broker = new Broker(new MemoryBackend);
		$files = $broker->processDirectory(realpath(__DIR__ . '/../data/class'), TRUE);
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

}
