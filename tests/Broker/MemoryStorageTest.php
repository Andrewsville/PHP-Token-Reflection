<?php

namespace ApiGen\TokenReflection\Tests\Broker;

use ApiGen;
use ApiGen\TokenReflection\Parser;
use ApiGen\TokenReflection\Storage\StorageInterface;
use ApiGen\TokenReflection\Tests\ContainerFactory;
use Nette\DI\Container;
use PHPUnit_Framework_TestCase;


class MemoryStorageTest extends PHPUnit_Framework_TestCase
{

	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @var StorageInterface
	 */
	private $storage;


	public function __construct()
	{
		$this->container = (new ContainerFactory)->create();
	}


	protected function setUp()
	{
		/** @var Parser $broker */
		$broker = $this->container->getByType('ApiGen\TokenReflection\Parser');
		$broker->parseDirectory(realpath(__DIR__ . '/../data/class'));
		$this->storage = $this->container->getByType('ApiGen\TokenReflection\Storage\StorageInterface');
	}


	public function testFiles()
	{
		$file = realpath(__DIR__ . '/../data/class/no-parent.php');

		$this->assertTrue($this->storage->hasFile($file));
		$this->assertFalse($this->storage->hasFile($file . '...'));

		$fileReflection = $this->storage->getFile($file);
		$this->assertInstanceOf('ApiGen\TokenReflection\Reflection\ReflectionFile', $fileReflection);

		$this->assertCount(37, $this->storage->getFiles());
	}


	/**
	 * @expectedException ApiGen\TokenReflection\Exception\ParserException
	 */
	public function testGetNonExistingFile()
	{
		$this->storage->getFile('...');
	}


	public function testNamespaces()
	{
		$this->assertTrue($this->storage->hasNamespace('ns'));
		$this->assertFalse($this->storage->hasNamespace('...'));

		$this->assertInstanceOf(
			'ApiGen\TokenReflection\Reflection\ReflectionNamespace',
			$this->storage->getNamespace('ns')
		);

		$this->assertCount(5, $this->storage->getNamespaces());
	}


	/**
	 * @expectedException ApiGen\TokenReflection\Exception\ParserException
	 */
	public function getNonExistingNamespace()
	{
		$this->storage->getNamespace('...');
	}


	public function testClasses()
	{
		$this->assertTrue($this->storage->hasClass('TokenReflection_Test_ClassNoFinal'));
		$this->assertFalse($this->storage->hasClass('...'));
		$this->assertFalse($this->storage->hasClass('ns\\...'));
		$this->assertFalse($this->storage->hasClass('nonExistingNamespace\\...'));

		$this->assertInstanceOf(
			'ApiGen\TokenReflection\Reflection\ReflectionClass',
			$this->storage->getClass('TokenReflection_Test_ClassNoFinal')
		);
		$this->storage->getClass('someNonExistingClass');

		$this->assertCount(68, $this->storage->getClasses());
	}


	public function testConstants()
	{
		$this->assertFalse($this->storage->hasConstant('...'));
		$this->assertFalse($this->storage->hasConstant('SomeClass::someConstant'));
		$this->assertFalse($this->storage->hasConstant('\\someConstant'));
		$this->assertCount(0, $this->storage->getConstants());
	}


	/**
	 * @expectedException ApiGen\TokenReflection\Exception\ParserException
	 */
	public function testGetNonExistingConstant()
	{
		$this->storage->getConstant('...');
	}


	public function testFunctions()
	{
		$this->assertFalse($this->storage->hasFunction('...'));
		$this->assertCount(0, $this->storage->getFunctions());
	}


	/**
	 * @expectedException ApiGen\TokenReflection\Exception\ParserException
	 */
	public function testGetNonExistingFunction()
	{
		$this->storage->getFunction('...');
	}

}
