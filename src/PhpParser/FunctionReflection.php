<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\PhpParser;

use ApiGen\TokenReflection\Broker\Broker;
use ApiGen\TokenReflection\Exception\RuntimeException;
use ApiGen\TokenReflection\ReflectionExtensionInterface;
use ApiGen\TokenReflection\ReflectionFunctionInterface;
use ApiGen\TokenReflection\ReflectionParameterInterface;
use PhpParser\Comment\Doc;
use PhpParser\Node\Param;


class FunctionReflection implements ReflectionFunctionInterface
{

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var bool
	 */
	private $returnsReference;

	/**
	 * @var Param[]|array
	 */
	private $parameters;

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
	public function __construct($name, $byRef, array $parameters, $startLine, $endLine, $docComment, $namespace, array $annotations, $file)
	{
		$this->name = $name;
		$this->returnsReference = $byRef;
		$this->parameters = $parameters;
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
	public function isVariadic()
	{
		foreach ($this->parameters as $parameter) {
			if ($parameter->variadic) {
				$this->isVariadic = TRUE;
			}
		}
		return $this->isVariadic;
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
	public function returnsReference()
	{
		return $this->returnsReference;
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
	public function getParameter($parameter)
	{
		if (is_numeric($parameter)) {
			if ( ! isset($this->parameters[$parameter])) {
				throw new RuntimeException(sprintf('There is no parameter at position "%d".', $parameter), RuntimeException::DOES_NOT_EXIST);
			}
			return $this->parameters[$parameter];

		} else {
			foreach ($this->parameters as $reflection) {
				if ($reflection->getName() === $parameter) {
					return $reflection;
				}
			}
			throw new RuntimeException(sprintf('There is no parameter "%s".', $parameter), RuntimeException::DOES_NOT_EXIST);
		}
	}


	/**
	 * {@inheritdoc}
	 */
	public function getParameters()
	{
		return $this->parameters;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getNumberOfParameters()
	{
		return count($this->parameters);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getNumberOfRequiredParameters()
	{
		$numberRequired = 0;
		foreach ($this->parameters as $parameter) {
			if ($parameter->isRequired()) {
				$numberRequired++;
			}
		}
		return $numberRequired;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getPrettyName()
	{
		return $this->name . '()';
	}


	/**
	 * Returns static variables.
	 *
	 * @return array
	 */
	function getStaticVariables()
	{
		// TODO: Implement getStaticVariables() method.
	}


	/**
	 * {@inheritdoc}
	 * @todo figure out later
	 */
	public function getNamespaceAliases()
	{
		return [];
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

}
