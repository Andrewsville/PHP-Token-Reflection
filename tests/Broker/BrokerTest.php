<?php

namespace ApiGen\TokenReflection\Tests\Broker;

use ApiGen;
use ApiGen\TokenReflection\Parser;
use ApiGen\TokenReflection\Tests\ContainerFactory;
use Nette\DI\Container;
use PHPUnit_Framework_TestCase;


class BrokerTest extends PHPUnit_Framework_TestCase
{

	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @var Parser
	 */
	private $broker;


	public function __construct()
	{
		$this->container = (new ContainerFactory)->create();
	}


	protected function setUp()
	{
		$this->broker = $this->container->getByType('ApiGen\TokenReflection\Parser');
	}


	public function testFindFiles()
	{
		$files = $this->broker->parseDirectory(realpath(__DIR__ . '/../data/class'), TRUE);
		$this->assertCount(37, $files);
	}


	/**
	 * @expectedException ApiGen\TokenReflection\Exception\StreamException
	 */
	public function testFileProcessingError()
	{
		$file = __DIR__ . DIRECTORY_SEPARATOR . '~#nonexistent#~';
		$this->broker->parseFile($file);
	}


	/**
	 * @expectedException ApiGen\TokenReflection\Exception\ParserException
	 */
	public function testDirectoryProcessingError()
	{
		$file = __DIR__ . DIRECTORY_SEPARATOR . '~#nonexistent#~' . DIRECTORY_SEPARATOR . '~#nonexistent#~';
		$this->broker->parseDirectory($file);
	}

}
