<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\DI;

use Nette\DI\CompilerExtension;


class TokenReflectionExtension extends CompilerExtension
{

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('parser'))
			->setClass('ApiGen\TokenReflection\Parser');

		$builder->addDefinition($this->prefix('storage'))
			->setClass('ApiGen\TokenReflection\Storage\MemoryStorage');

		$builder->addDefinition($this->prefix('phpParser'))
			->setClass('PhpParser\Parser');

		$builder->addDefinition($this->prefix('emulativeLexer'))
			->setClass('PhpParser\Lexer\Emulative');

		$builder->addDefinition($this->prefix('resolver'))
			->setClass('ApiGen\TokenReflection\Resolver');

		$this->setupPhp();
		$this->setupReflections();
		$this->setupPhpParser();
	}



	private function setupPhp()
	{
	}


	private function setupReflections()
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('reflection.fileFactory'))
			->setClass('ApiGen\TokenReflection\Reflection\Factory\ReflectionFileFactory');

		$builder->addDefinition($this->prefix('reflection.namespaceFactory'))
			->setClass('ApiGen\TokenReflection\Reflection\Factory\ReflectionNamespaceFactory');

		$builder->addDefinition($this->prefix('reflection.fileNamespaceFactory'))
			->setClass('ApiGen\TokenReflection\Reflection\Factory\ReflectionFileNamespaceFactory');

		$builder->addDefinition($this->prefix('reflection.annotationFactory'))
			->setClass('ApiGen\TokenReflection\Reflection\Factory\ReflectionAnnotationFactory');
	}


	private function setupPhpParser()
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('docBlockParser'))
			->setClass('ApiGen\TokenReflection\PhpParser\DocBlockParser');

		$builder->addDefinition($this->prefix('namespaceReflectionFactory'))
			->setClass('ApiGen\TokenReflection\PhpParser\Factory\NamespaceReflectionFactory');

		$builder->addDefinition($this->prefix('classReflectionFactory'))
			->setClass('ApiGen\TokenReflection\Factory\ClassReflectionFactory');

		$builder->addDefinition($this->prefix('constantReflectionFactory'))
			->setClass('ApiGen\TokenReflection\Factory\ConstantReflectionFactory');

		$builder->addDefinition($this->prefix('functionReflectionFactory'))
			->setClass('ApiGen\TokenReflection\Factory\FunctionReflectionFactory');
	}

}
