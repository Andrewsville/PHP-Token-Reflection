<?php

namespace ApiGen\TokenReflection\Tests\Broker;

use ApiGen;
use ApiGen\TokenReflection\Tests\ContainerFactory;
use ApiGen\TokenReflection\Tests\TestCase;
use Nette\DI\Container;


class BrokerTest extends TestCase
{

	/**
	 * @var Container
	 */
	private $container;


	public function __construct()
	{
		$this->container = (new ContainerFactory)->create();
	}


	public function testEmptyFileProcessing()
	{
		$this->getFileTokenReflection('empty');
	}


	public function testFindFiles()
	{
		$broker = $this->container->getByType('ApiGen\TokenReflection\Broker\Broker');
		$files = $broker->processDirectory(realpath(__DIR__ . '/../data/class'), TRUE);
		$this->assertCount(37, $files);
	}


	/**
	 * @expectedException ApiGen\TokenReflection\Exception\BrokerException
	 */
	public function testFileProcessingError()
	{
		$file = __DIR__ . DIRECTORY_SEPARATOR . '~#nonexistent#~';
		$this->getBroker()->processFile($file);
	}


	/**
	 * @expectedException ApiGen\TokenReflection\Exception\BrokerException
	 */
	public function testDirectoryProcessingError()
	{
		$file = __DIR__ . DIRECTORY_SEPARATOR . '~#nonexistent#~' . DIRECTORY_SEPARATOR . '~#nonexistent#~';
		$this->getBroker()->processDirectory($file);
	}


	/**
	 * @expectedException ApiGen\TokenReflection\Exception\BrokerException
	 */
	public function testProcessingError()
	{
		$file = __DIR__ . DIRECTORY_SEPARATOR . '~#nonexistent#~';
		$this->getBroker()->process($file);
	}

}
