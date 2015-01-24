<?php

namespace ApiGen\TokenReflection\Tests\Reflection;

use ApiGen;
use ApiGen\TokenReflection\Reflection\ReflectionNamespace;
use ApiGen\TokenReflection\Tests\TestCase;


class ReflectionFileTest extends TestCase
{

	/**
	 * Element type.
	 *
	 * @var string
	 */
	protected $type = 'file';


	public function testDocComment()
	{
		$fileName = $this->getFilePath('docComment');
		$this->parser->processFile($fileName);

		$this->assertTrue($this->parser->getStorage()->hasFile($fileName));

		$fileReflection = $this->parser->getStorage()->getFile($fileName);
		$this->assertInstanceOf('ApiGen\TokenReflection\Reflection\ReflectionFile', $fileReflection);

		$this->assertSame($this->getFilePath('docComment'), $fileReflection->getPrettyName());

		$this->assertTrue($fileReflection->hasAnnotation('package'));
		$this->assertTrue($fileReflection->hasAnnotation('author'));
		$this->assertFalse($fileReflection->hasAnnotation('licence'));

		$this->assertSame(['package name'], $fileReflection->getAnnotation('package'));
		$this->assertSame(['author name'], $fileReflection->getAnnotation('author'));
	}


	public function testNoDocComment()
	{
		$fileName = $this->getFilePath('noDocComment');
		$this->parser->processFile($fileName);

		$this->assertTrue($this->parser->getStorage()->hasFile($fileName));

		$fileReflection = $this->parser->getStorage()->getFile($fileName);
		$this->assertInstanceOf('ApiGen\TokenReflection\Reflection\ReflectionFile', $fileReflection);

		$this->assertSame($this->getFilePath('noDocComment'), $fileReflection->getPrettyName());

		$this->assertFalse($fileReflection->hasAnnotation('package'));
		$this->assertFalse($fileReflection->hasAnnotation('author'));
		$this->assertFalse($fileReflection->getDocComment());
	}


	public function testReturningFileReflection()
	{
		$fileName = $this->getFilePath('docComment');
		$rfl = $this->getClassReflection('docComment');

		$this->assertTrue($this->parser->getStorage()->hasFile($fileName));

		$this->assertSame($rfl->token->getFileName(), $rfl->token->getFileReflection()->getName());
		$this->assertSame($this->parser->getStorage()->getFile($fileName), $rfl->token->getFileReflection());
	}


	public function testDeclareNoNamespace()
	{
		$fileName = $this->getFilePath('declareNoNamespace');
		$this->parser->processFile($fileName);

		$this->assertTrue($this->parser->getStorage()->hasFile($fileName));

		$fileReflection = $this->parser->getStorage()->getFile($fileName);
		$this->assertInstanceOf('ApiGen\TokenReflection\Reflection\ReflectionFile', $fileReflection);

		$this->assertSame($this->getFilePath('declareNoNamespace'), $fileReflection->getPrettyName());

		$namespaces = $fileReflection->getNamespaces();
		$this->assertCount(1, $namespaces);
		$this->assertEquals(ReflectionNamespace::NO_NAMESPACE_NAME, $namespaces[0]->getName());
	}


	public function testDeclareNamespace()
	{
		$fileName = $this->getFilePath('declareNamespace');
		$this->parser->processFile($fileName);

		$this->assertTrue($this->parser->getStorage()->hasFile($fileName));

		$fileReflection = $this->parser->getStorage()->getFile($fileName);
		$this->assertInstanceOf('ApiGen\TokenReflection\Reflection\ReflectionFile', $fileReflection);

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
		$this->assertFalse($this->parser->getStorage()->hasFile('#non~Existent#'));
		$this->parser->getStorage()->getFile('#non~Existent#');
	}

}
