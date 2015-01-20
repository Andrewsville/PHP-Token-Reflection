<?php

namespace ApiGen\TokenReflection\Tests\PhpParser;

use ApiGen\TokenReflection\Broker\Broker;
use ApiGen\TokenReflection\Factory\FunctionReflectionFactory;
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


	public function __construct()
	{
		$this->container = (new ContainerFactory)->create();
	}


	public function testParsing()
	{
//		/** @var Broker $broker */
//		$broker = $this->container->getByType('ApiGen\TokenReflection\Broker\Broker');
//		$broker->processFile(__DIR__ . '/doubleClass.php');
//
//		$classes = $broker->getClasses();
//		$this->assertCount(1, $classes);
//		$this->assertInstanceOf('ApiGen\TokenReflection\Reflection\ReflectionClass', $classes['SomeClass']);

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
	 */
	private function iterateNodes($nodes, Node $parent = NULL, $file)
	{
		$classReflectionFactory = $this->container->getByType('ApiGen\TokenReflection\Factory\ClassReflectionFactory');

		/** @var FunctionReflectionFactory $functionReflectionFactory */
		$functionReflectionFactory = $this->container->getByType('ApiGen\TokenReflection\Factory\FunctionReflectionFactory');

		foreach ($nodes as $node) {
			if ($node instanceof Class_) {
//				$classReflection = $classReflectionFactory->createFromNode($node);

			} elseif ($node instanceof Function_) {
				$functionReflection = $functionReflectionFactory->createFromNode($node, $parent, $file);
				$this->assertSame('SomeNamespace', $functionReflection->getNamespaceName());
				$this->assertTrue($functionReflection->inNamespace());
				$this->assertFalse($functionReflection->returnsReference());
				$this->assertTrue($functionReflection->isDeprecated());
				$this->assertFalse($functionReflection->isInternal());
				$this->assertTrue($functionReflection->isUserDefined());
				$this->assertSame([], $functionReflection->getNamespaceAliases());
				$this->assertSame('getSome()', $functionReflection->getPrettyName());

			} elseif ($node instanceof Namespace_) {
				$this->iterateNodes($node->stmts, $node, $file);

			} elseif ($node instanceof Const_) {
				/** @var FunctionReflectionFactory $functionReflectionFactory */
				$constantReflectionFactory = $this->container->getByType('ApiGen\TokenReflection\Factory\ConstantReflectionFactory');
				$constantReflection = $constantReflectionFactory->createFromNode($node, $parent, $file);
				$this->assertInstanceOf('ApiGen\TokenReflection\PhpParser\ConstantReflection',  $constantReflection);
			}
		}
	}

}
