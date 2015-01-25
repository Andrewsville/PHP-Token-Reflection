<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Reflection\Factory;

use ApiGen\TokenReflection\Reflection\ReflectionAnnotation;
use ApiGen\TokenReflection\Reflection\ReflectionBase;


class ReflectionAnnotationFactory
{

	/**
	 * @param ReflectionBase $reflection
	 * @param bool|string $docComment
	 * @return ReflectionAnnotation
	 */
	public function create(ReflectionBase $reflection, $docComment)
	{
		return new ReflectionAnnotation($reflection, $docComment);
	}

}
