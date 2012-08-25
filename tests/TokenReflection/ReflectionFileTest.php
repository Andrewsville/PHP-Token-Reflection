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

use ReflectionClass as InternalReflectionClass;

require_once __DIR__ . '/../bootstrap.php';

/**
 * Class test.
 */
class ReflectionFileTest extends Test
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
		$this->assertInstanceOf('\TokenReflection\ReflectionFile', $fileReflection);

		$this->assertSame($this->getFilePath('docComment'), $fileReflection->getPrettyName());

		$this->assertTrue($fileReflection->hasAnnotation('package'));
		$this->assertTrue($fileReflection->hasAnnotation('author'));
		$this->assertFalse($fileReflection->hasAnnotation('licence'));

		$this->assertSame(array('package name'), $fileReflection->getAnnotation('package'));
		$this->assertSame(array('author name'), $fileReflection->getAnnotation('author'));
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
		$this->assertInstanceOf('\TokenReflection\ReflectionFile', $fileReflection);

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

	/**
	 * Tests throwing exceptions when requesting reflections of files that were not processed.
	 *
	 * @expectedException \TokenReflection\Exception\BrokerException
	 */
	public function testExceptionReturningFileReflection()
	{
		$broker = $this->getBroker();

		$this->assertFalse($broker->hasFile('#non~Existent#'));
		$broker->getFile('#non~Existent#');
	}
}
