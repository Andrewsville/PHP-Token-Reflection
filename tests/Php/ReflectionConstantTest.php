<?php

namespace ApiGen\TokenReflection\Tests\Php;

use ApiGen;
use ApiGen\TokenReflection\Php\ReflectionConstant;
use ApiGen\TokenReflection\Tests\TestCase;


class ReflectionConstantTest extends TestCase
{

	/**
	 * @var string
	 */
	protected $type = 'constant';

	/**
	 * @var ReflectionConstant
	 */
	private $internalReflectionConstant;


	protected function setUp()
	{
		$this->internalReflectionConstant = $this->getStorage()->getConstant('DIRECTORY_SEPARATOR');
	}


	public function testName()
	{
		$this->assertSame('DIRECTORY_SEPARATOR', $this->internalReflectionConstant->getName());
		$this->assertSame('DIRECTORY_SEPARATOR', $this->internalReflectionConstant->getShortName());
		$this->assertNull($this->internalReflectionConstant->getDeclaringClass());
		$this->assertNull($this->internalReflectionConstant->getDeclaringClassName());
	}

	public function testBasicMethods()
	{
		$this->assertFalse($this->internalReflectionConstant->hasAnnotation('...'));
		$this->assertNull($this->internalReflectionConstant->getAnnotation('...'));
		$this->assertSame([], $this->internalReflectionConstant->getAnnotations());

		$this->assertFalse($this->internalReflectionConstant->isTokenized());
		$this->assertFalse($this->internalReflectionConstant->isDeprecated());
		$this->assertFalse($this->internalReflectionConstant->isUserDefined());

		$this->assertSame('DIRECTORY_SEPARATOR', $this->internalReflectionConstant->getPrettyName());
	}


	public function testNamespaces()
	{
		$this->assertSame('', $this->internalReflectionConstant->getNamespaceName());
		$this->assertFalse($this->internalReflectionConstant->inNamespace());
		$this->assertSame([], $this->internalReflectionConstant->getNamespaceAliases());
	}


	public function testExtension()
	{
		$this->assertNull($this->internalReflectionConstant->getExtension());
		$this->assertFalse($this->internalReflectionConstant->getExtensionName());
	}


	public function testFile()
	{
		$this->assertNull($this->internalReflectionConstant->getFileName());
		$this->assertNull($this->internalReflectionConstant->getStartLine());
		$this->assertNull($this->internalReflectionConstant->getEndLine());
	}


	public function testGetDocComment()
	{
		$this->assertFalse($this->internalReflectionConstant->getDocComment());
	}


	public function testValue()
	{
		$this->assertSame('/', $this->internalReflectionConstant->getValue());
		$this->assertSame("'/'", $this->internalReflectionConstant->getValueDefinition());
	}


	public function testGetStorage()
	{
		$this->assertInstanceOf('ApiGen\TokenReflection\Broker\StorageInterface', $this->internalReflectionConstant->getStorage());
	}


	public function testCreate()
	{
		$this->assertNull(ReflectionConstant::create(new \ReflectionFunction('strlen'), $this->getStorage()));
	}

}
