<?php

namespace ApiGen\TokenReflection\Tests;

use ApiGen;
use ApiGen\TokenReflection\Parser;
use ApiGen\TokenReflection\Tests\ContainerFactory;
use Nette\DI\Container;
use PHPUnit_Framework_TestCase;


class ParserTest extends PHPUnit_Framework_TestCase
{

	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @var Parser
	 */
	private $parser;


	public function __construct()
	{
		$this->container = (new ContainerFactory)->create();
	}


	protected function setUp()
	{
		$this->parser = $this->container->getByType('ApiGen\TokenReflection\Parser');
	}


	public function testFindFiles()
	{
		$files = $this->parser->parseDirectory(realpath(__DIR__ . '/data/class'));
		$this->assertCount(37, $files);
	}


	/**
	 * @expectedException ApiGen\TokenReflection\Exception\StreamException
	 */
	public function testFileProcessingError()
	{
		$file = __DIR__ . DIRECTORY_SEPARATOR . '~#nonexistent#~';
		$this->parser->parseFile($file);
	}


	/**
	 * @expectedException ApiGen\TokenReflection\Exception\ParserException
	 */
	public function testDirectoryProcessingError()
	{
		$file = __DIR__ . DIRECTORY_SEPARATOR . '~#nonexistent#~' . DIRECTORY_SEPARATOR . '~#nonexistent#~';
		$this->parser->parseDirectory($file);
	}

}
