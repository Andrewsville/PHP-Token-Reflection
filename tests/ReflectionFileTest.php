<?php

namespace ApiGen\TokenReflection\Tests;

use ApiGen\TokenReflection\ReflectionNamespace;
use ReflectionClass as InternalReflectionClass;


class ReflectionFileTest extends TestCase
{

	/**
	 * Element type.
	 *
	 * @var string
	 */
	protected $type = 'file';


	/**
	 * Tests file level docblocks.
	 */
	public function testDocComment()
	{
		$fileName = $this->getFilePath('docComment');
		$this->getBroker()->processFile($fileName);

		$this->assertTrue($this->getBroker()->hasFile($fileName));

		$fileReflection = $this->getBroker()->getFile($fileName);
		$this->assertInstanceOf('ApiGen\TokenReflection\ReflectionFile', $fileReflection);

		$this->assertSame($this->getFilePath('docComment'), $fileReflection->getPrettyName());

		$this->assertTrue($fileReflection->hasAnnotation('package'));
		$this->assertTrue($fileReflection->hasAnnotation('author'));
		$this->assertFalse($fileReflection->hasAnnotation('licence'));

		$this->assertSame(['package name'], $fileReflection->getAnnotation('package'));
		$this->assertSame(['author name'], $fileReflection->getAnnotation('author'));
	}


	/**
	 * Tests file level docblocks.
	 */
	public function testNoDocComment()
	{
		$fileName = $this->getFilePath('noDocComment');
		$this->getBroker()->processFile($fileName);

		$this->assertTrue($this->getBroker()->hasFile($fileName));

		$fileReflection = $this->getBroker()->getFile($fileName);
		$this->assertInstanceOf('ApiGen\TokenReflection\ReflectionFile', $fileReflection);

		$this->assertSame($this->getFilePath('noDocComment'), $fileReflection->getPrettyName());

		$this->assertFalse($fileReflection->hasAnnotation('package'));
		$this->assertFalse($fileReflection->hasAnnotation('author'));
		$this->assertFalse($fileReflection->getDocComment());
	}


	/**
	 * Tests returning file reflections.
	 */
	public function testReturningFileReflection()
	{
		$fileName = $this->getFilePath('docComment');
		$rfl = $this->getClassReflection('docComment');

		$this->assertTrue($this->getBroker()->hasFile($fileName));

		$this->assertSame($rfl->token->getFileName(), $rfl->token->getFileReflection()->getName());
		$this->assertSame($this->getBroker()->getFile($fileName), $rfl->token->getFileReflection());
	}


	public function testDeclareNoNamespace()
	{
		$fileName = $this->getFilePath('declareNoNamespace');
		$this->getBroker()->processFile($fileName);

		$this->assertTrue($this->getBroker()->hasFile($fileName));

		$fileReflection = $this->getBroker()->getFile($fileName);
		$this->assertInstanceOf('ApiGen\TokenReflection\ReflectionFile', $fileReflection);

		$this->assertSame($this->getFilePath('declareNoNamespace'), $fileReflection->getPrettyName());

		$namespaces = $fileReflection->getNamespaces();
		$this->assertCount(1, $namespaces);
		$this->assertEquals(ReflectionNamespace::NO_NAMESPACE_NAME, $namespaces[0]->getName());
	}


	public function testDeclareNamespace()
	{
		$fileName = $this->getFilePath('declareNamespace');
		$this->getBroker()->processFile($fileName);

		$this->assertTrue($this->getBroker()->hasFile($fileName));

		$fileReflection = $this->getBroker()->getFile($fileName);
		$this->assertInstanceOf('ApiGen\TokenReflection\ReflectionFile', $fileReflection);

		$this->assertSame($this->getFilePath('declareNamespace'), $fileReflection->getPrettyName());

		$namespaces = $fileReflection->getNamespaces();
		$this->assertCount(1, $namespaces);
		$this->assertEquals('TokenReflection\Test', $namespaces[0]->getName());
	}


	/**
	 * Tests throwing exceptions when requesting reflections of files that were not processed.
	 *
	 * @expectedException ApiGen\TokenReflection\Exception\BrokerException
	 */
	public function testExceptionReturningFileReflection()
	{
		$broker = $this->getBroker();

		$this->assertFalse($broker->hasFile('#non~Existent#'));
		$broker->getFile('#non~Existent#');
	}
}
