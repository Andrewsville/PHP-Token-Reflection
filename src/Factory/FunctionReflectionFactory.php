<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Factory;

use ApiGen\TokenReflection\PhpParser\DocBlockParser;
use ApiGen\TokenReflection\PhpParser\FunctionReflection;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Function_;


class FunctionReflectionFactory
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
	 * @return FunctionReflection
	 */
	public function createFromNode(Function_ $functionNode, Stmt $parentNode = NULL, $file)
	{
		$docComment = $functionNode->hasAttribute('comments') ? $functionNode->getAttribute('comments')[0] : NULL;

		// 1. get namespace
		$namespace = '';
		if ($parentNode instanceof Stmt\Namespace_) {
			$namespace = $parentNode->name->parts[0];
		}

		// 2. get annotations
		$annotations = $this->docBlockParser->parseToAnnotations($docComment);

		return new FunctionReflection(
			$functionNode->name,
			$functionNode->byRef,
			$functionNode->params,
			$functionNode->getAttribute('startLine'),
			$functionNode->getAttribute('endLine'),
			$docComment,
			$namespace,
			$annotations,
			$file
		);
	}

}
