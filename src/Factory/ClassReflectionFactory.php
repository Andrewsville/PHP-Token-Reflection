<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Factory;

use ApiGen\TokenReflection\PhpParser\ClassReflection;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;


class ClassReflectionFactory implements ClassReflectionFactoryInterface
{

	/**
	 * @return ClassReflection
	 */
	public function createFromNode(Class_ $classNode, Stmt $parentNode = NULL, $file)
	{
		return new ClassReflection(
			$classNode->name
		);
	}

}
