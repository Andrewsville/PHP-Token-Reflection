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

		$builder->addDefinition($this->prefix('broker'))
			->setClass('ApiGen\TokenReflection\Broker\Broker');

		$builder->addDefinition($this->prefix('backend'))
			->setClass('ApiGen\TokenReflection\Broker\MemoryBackend');

		$builder->addDefinition($this->prefix('phpParser'))
			->setClass('PhpParser\Parser');

		$builder->addDefinition($this->prefix('emulativeLexer'))
			->setClass('PhpParser\Lexer\Emulative');

		$builder->addDefinition($this->prefix('resolver'))
			->setClass('ApiGen\TokenReflection\Resolver');

		$builder->addDefinition($this->prefix('docBlockParser'))
			->setClass('ApiGen\TokenReflection\PhpParser\DocBlockParser');

		$this->setupFactories();
	}


	private function setupFactories()
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('classReflectionFactory'))
			->setClass('ApiGen\TokenReflection\Factory\ClassReflectionFactory');

		$builder->addDefinition($this->prefix('constantReflectionFactory'))
			->setClass('ApiGen\TokenReflection\Factory\ConstantReflectionFactory');

		$builder->addDefinition($this->prefix('functionReflectionFactory'))
			->setClass('ApiGen\TokenReflection\Factory\FunctionReflectionFactory');

		// method

		// parameter

		// property

		// ... that's it?
	}

}
