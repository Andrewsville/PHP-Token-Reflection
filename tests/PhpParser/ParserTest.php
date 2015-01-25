<?php

namespace ApiGen\TokenReflection\Tests\PhpParser;

use ApiGen\TokenReflection\Parser;
use ApiGen\TokenReflection\Factory\ClassReflectionFactory;
use ApiGen\TokenReflection\Factory\ConstantReflectionFactory;
use ApiGen\TokenReflection\Factory\FunctionReflectionFactory;
use ApiGen\TokenReflection\PhpParser\Factory\NamespaceReflectionFactory;
use ApiGen\TokenReflection\Storage\StorageInterface;
use ApiGen\TokenReflection\Tests\ContainerFactory;
use Nette\DI\Container;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Const_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Namespace_;
use PHPUnit_Framework_TestCase;


class ParserTest extends PHPUnit_Framework_TestCase
{

	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @var ClassReflectionFactory
	 */
	private $classReflectionFactory;

	/**
	 * @var ConstantReflectionFactory
	 */
	private $constantReflectionFactory;

	/**
	 * @var FunctionReflectionFactory
	 */
	private $functionReflectionFactory;

	/**
	 * @var StorageInterface
	 */
	private $storage;

	/**
	 * @var NamespaceReflectionFactory
	 */
	private $namespaceReflectionFactory;


	public function __construct()
	{
		$this->container = (new ContainerFactory)->create();
	}


	protected function setUp()
	{
		$this->classReflectionFactory = $this->container->getByType('ApiGen\TokenReflection\Factory\ClassReflectionFactory');
		$this->constantReflectionFactory = $this->container->getByType('ApiGen\TokenReflection\Factory\ConstantReflectionFactory');
		$this->functionReflectionFactory = $this->container->getByType('ApiGen\TokenReflection\Factory\FunctionReflectionFactory');
		$this->namespaceReflectionFactory = $this->container->getByType('ApiGen\TokenReflection\PhpParser\Factory\NamespaceReflectionFactory');
		$this->storage = $this->container->getByType('ApiGen\TokenReflection\Storage\StorageInterface');
	}


	public function testParsing()
	{
		/** @var \PhpParser\Parser $parser */
		$file = __DIR__ . '/doubleClass.php';
		$parser = $this->container->getByType('PhpParser\Parser');
		$parsed = $parser->parse(file_get_contents($file));
		$this->assertCount(1, $parsed);

		// use some factory here!
		$this->iterateNodes($parsed, NULL, $file);
	}


	/**
	 * @param Stmt[] $nodes
	 * @param Node $parent
	 * @param string $file
	 */
	private function iterateNodes($nodes, Node $parent = NULL, $file)
	{
		foreach ($nodes as $node) {
			if ($node instanceof Class_) {
				$classReflection = $this->classReflectionFactory->createFromNode($node, $parent, $file);
				$this->assertSame('SomeClass', $classReflection->getName());
				$this->assertSame('SomeNamespace', $classReflection->getNamespaceName());
				$this->assertSame(__DIR__ . '/doubleClass.php', $classReflection->getFileName());
				$this->assertSame(23, $classReflection->getStartLine());
				$this->assertSame(26, $classReflection->getEndLine());
				$this->assertSame([], $classReflection->getNamespaceAliases());

				$docComment = <<<DOC
/**
 * I got some cool annotation as well
 */
DOC;
				$this->assertSame($docComment, $classReflection->getDocComment());

				$this->assertFalse($classReflection->isAbstract());
				$this->assertFalse($classReflection->isFinal());

				$this->storage->addClass($classReflection->getName(), $classReflection);

			} elseif ($node instanceof Function_) {
				$functionReflection = $this->functionReflectionFactory->createFromNode($node, $parent, $file);
				$this->assertSame('SomeNamespace', $functionReflection->getNamespaceName());
				$this->assertTrue($functionReflection->inNamespace());
				$this->assertFalse($functionReflection->returnsReference());
				$this->assertTrue($functionReflection->isDeprecated());
				$this->assertFalse($functionReflection->isInternal());
				$this->assertTrue($functionReflection->isUserDefined());
				$this->assertSame([], $functionReflection->getNamespaceAliases());
				$this->assertSame('getSome()', $functionReflection->getPrettyName());
				$this->assertSame(__DIR__ . '/doubleClass.php', $functionReflection->getFileName());
				$this->storage->addFunction($functionReflection->getName(), $functionReflection);

			} elseif ($node instanceof Const_) {
				$constantReflection = $this->constantReflectionFactory->createFromNode($node, $parent, $file);
				$this->assertInstanceOf('ApiGen\TokenReflection\PhpParser\ConstantReflection',  $constantReflection);
				$this->assertInternalType('string', $constantReflection->getDocComment());
				$this->assertSame(__DIR__ . '/doubleClass.php', $constantReflection->getFileName());
				$this->storage->addConstant($constantReflection->getName(), $constantReflection);

			} elseif ($node instanceof Namespace_) {
				// create namespace reflection?
				$namespaceReflection = $this->namespaceReflectionFactory->createFromNode($node);
				$this->iterateNodes($node->stmts, $node, $file);
			}
		}
	}

}
