<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\PhpParser\Factory;

use ApiGen\TokenReflection\PhpParser\NamespaceReflection;
use ApiGen\TokenReflection\ReflectionNamespaceInterface;
use PhpParser\Node\Stmt\Namespace_;


class NamespaceReflectionFactory
{

	/**
	 * @return ReflectionNamespaceInterface
	 *
	 */
	public function createFromNode(Namespace_ $node)
	{
		$name = $node->name->parts[0];
		return new NamespaceReflection($name);
	}

}
