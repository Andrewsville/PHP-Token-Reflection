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
	}

}
