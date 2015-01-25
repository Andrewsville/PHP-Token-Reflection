<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Factory;

use ApiGen\TokenReflection\PhpParser\ClassReflection;
use ApiGen\TokenReflection\PhpParser\DocBlockParser;
use ApiGen\TokenReflection\Reflection\ReflectionNamespace;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;


class ClassReflectionFactory implements ClassReflectionFactoryInterface
{

	/**
	 * @var DocBlockParser
	 */
	private $docBlockParser;


	public function __construct(DocBlockParser $docBlockParser)
	{
		$this->docBlockParser = $docBlockParser;
	}

	/**
	 * @return ClassReflection
	 */
	public function createFromNode(Class_ $classNode, Stmt $parentNode = NULL, $file)
	{
		// 1. docblock
		$docComment = $classNode->hasAttribute('comments') ? $classNode->getAttribute('comments')[0] : NULL;

		// 2. get annotations
		$annotations = $this->docBlockParser->parseToAnnotations($docComment);

		return new ClassReflection(
			$classNode->name,
			$this->getNamespaceName($parentNode),
			$file,
			$this->getNamespaceAliases($parentNode),
			$annotations,
			$classNode,
			$this->docBlockParser
		);
	}


	/**
	 * @return string
	 */
	private function getNamespaceName(Stmt $parentNode)
	{
		if ($parentNode instanceof Stmt\Namespace_) {
			return $parentNode->name->parts[0];
		}
		return ReflectionNamespace::NO_NAMESPACE_NAME;
	}


	/**
	 * @return string
	 */
	private function getNamespaceAliases(Stmt $parentNode)
	{
		// todo
		return [];
	}

}
