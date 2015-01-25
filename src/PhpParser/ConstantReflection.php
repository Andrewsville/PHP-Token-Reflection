<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\PhpParser;

use ApiGen\TokenReflection\ReflectionClassInterface;
use ApiGen\TokenReflection\ReflectionConstantInterface;
use PhpParser\Comment\Doc;


class ConstantReflection implements ReflectionConstantInterface
{

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var int
	 */
	private $startLine;

	/**
	 * @var int
	 */
	private $endLine;

	/**
	 * @var Doc|NULL
	 */
	private $docComment;

	/**
	 * @var bool
	 */
	private $isVariadic = FALSE;

	/**
	 * @var array
	 */
	private $annotations;

	/**
	 * @var
	 */
	private $file;


	/**
	 * @param string $name
	 * @param bool $byRef
	 * @param array $parameters
	 * @param int $startLine
	 * @param int $endLine
	 * @param string $docComment
	 * @param string $namespace
	 * @param array $annotations
	 */
	public function __construct($name, $startLine, $endLine, $docComment, $namespace, array $annotations, $file)
	{
		$this->name = $name;
		$this->startLine = $startLine;
		$this->endLine = $endLine;
		$this->docComment = $docComment;
		$this->namespace = $namespace;
		$this->annotations = $annotations;
		$this->file = $file;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getStartLine()
	{
		return $this->startLine;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getEndLine()
	{
		return $this->endLine;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDocComment()
	{
		if ($this->docComment instanceof Doc) {
			return $this->docComment->getText();

		} else {
			return '';
		}
	}


	/**
	 * {@inheritdoc}
	 */
	public function getNamespaceName()
	{
		return $this->namespace;
	}


	/**
	 * {@inheritdoc}
	 */
	public function inNamespace()
	{
		return (bool) $this->namespace;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getFileName()
	{
		return $this->file;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isDeprecated()
	{
		if (isset($this->annotations['deprecated'])) {
			return TRUE;
		}
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getPrettyName()
	{
		return $this->name . '()';
	}


	/**
	 * {@inheritdoc}
	 */
	public function isInternal()
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isUserDefined()
	{
		return TRUE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isTokenized()
	{
		return TRUE;
	}


	/**
	 * Returns the unqualified name (UQN).
	 *
	 * @return string
	 */
	function getShortName()
	{
		// TODO: Implement getShortName() method.
	}


	/**
	 * Returns the declaring class reflection.
	 *
	 * @return ReflectionClassInterface
	 */
	function getDeclaringClass()
	{
		// TODO: Implement getDeclaringClass() method.
	}


	/**
	 * Returns the declaring class name.
	 *
	 * @return string
	 */
	function getDeclaringClassName()
	{
		// TODO: Implement getDeclaringClassName() method.
	}


	/**
	 * Returns imported namespaces and aliases from the declaring namespace.
	 *
	 * @return array
	 */
	function getNamespaceAliases()
	{
		// TODO: Implement getNamespaceAliases() method.
	}


	/**
	 * Returns the constant value.
	 *
	 * @return mixed
	 */
	function getValue()
	{
		// TODO: Implement getValue() method.
	}


	/**
	 * Returns the part of the source code defining the constant value.
	 *
	 * @return string
	 */
	function getValueDefinition()
	{
		// TODO: Implement getValueDefinition() method.
	}
}
