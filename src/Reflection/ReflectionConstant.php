<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Reflection;

use ApiGen\TokenReflection\Broker\Broker;
use ApiGen\TokenReflection\Exception\ParseException;
use ApiGen\TokenReflection\ReflectionInterface;
use ApiGen\TokenReflection\ReflectionConstantInterface;
use ApiGen\TokenReflection\Parser\ConstantParser;
use ApiGen\TokenReflection\Reflection\ReflectionClass;
use ApiGen\TokenReflection\Reflection\ReflectionElement;
use ApiGen\TokenReflection\Reflection\ReflectionFileNamespace;
use ApiGen\TokenReflection\Reflection\ReflectionNamespace;
use ApiGen\TokenReflection\Resolver;
use ApiGen\TokenReflection\Stream\StreamBase;


class ReflectionConstant extends ReflectionElement implements ReflectionConstantInterface
{

	/**
	 * Name of the declaring class.
	 *
	 * @var string
	 */
	private $declaringClassName;

	/**
	 * Constant namespace name.
	 *
	 * @var string
	 */
	private $namespaceName;

	/**
	 * Constant value.
	 *
	 * @var mixed
	 */
	private $value;

	/**
	 * Constant value definition in tokens.
	 *
	 * @var array|string
	 */
	private $valueDefinition = [];

	/**
	 * Imported namespace/class aliases.
	 *
	 * @var array
	 */
	private $aliases = [];

	/**
	 * @var ConstantParser
	 */
	private $constantParser;


	public function __construct(StreamBase $tokenStream, Broker $broker, ReflectionInterface $parent = NULL)
	{
		$this->constantParser = new ConstantParser($tokenStream, $this, $parent);
		parent::__construct($tokenStream, $broker, $parent);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getShortName()
	{
		$name = $this->getName();
		if ($this->namespaceName !== NULL && $this->namespaceName !== ReflectionNamespace::NO_NAMESPACE_NAME) {
			$name = substr($name, strlen($this->namespaceName) + 1);
		}
		return $name;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDeclaringClassName()
	{
		return $this->declaringClassName;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDeclaringClass()
	{
		if (NULL === $this->declaringClassName) {
			return NULL;
		}
		return $this->getBroker()->getClass($this->declaringClassName);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getNamespaceName()
	{
		return $this->namespaceName === NULL || $this->namespaceName === ReflectionNamespace::NO_NAMESPACE_NAME ? '' : $this->namespaceName;
	}


	/**
	 * {@inheritdoc}
	 */
	public function inNamespace()
	{
		return '' !== $this->getNamespaceName();
	}


	/**
	 * Returns the constant value.
	 *
	 * @return mixed
	 */
	public function getValue()
	{
		if (is_array($this->valueDefinition)) {
			$this->value = Resolver::getValueDefinition($this->valueDefinition, $this);
			$this->valueDefinition = Resolver::getSourceCode($this->valueDefinition);
		}
		return $this->value;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getValueDefinition()
	{
		return is_array($this->valueDefinition) ? Resolver::getSourceCode($this->valueDefinition) : $this->valueDefinition;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getOriginalValueDefinition()
	{
		return $this->valueDefinition;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getNamespaceAliases()
	{
		return NULL === $this->declaringClassName ? $this->aliases : $this->getDeclaringClass()->getNamespaceAliases();
	}


	/**
	 * {@inheritdoc}
	 */
	public function getPrettyName()
	{
		return $this->declaringClassName === NULL ? parent::getPrettyName() : sprintf('%s::%s', $this->declaringClassName, $this->name);
	}


	/**
	 * {@inheritdoc}
	 */
	public function isValid()
	{
		return TRUE;
	}


	/**
	 * @return ReflectionElement
	 * @throws ParseException If an invalid parent reflection object was provided.
	 */
	protected function processParent(ReflectionInterface $parent, StreamBase $tokenStream)
	{
		if ($parent instanceof ReflectionFileNamespace) {
			$this->namespaceName = $parent->getName();
			$this->aliases = $parent->getNamespaceAliases();
		} elseif ($parent instanceof ReflectionClass) {
			$this->declaringClassName = $parent->getName();
		} else {
			throw new ParseException($this, $tokenStream, sprintf('Invalid parent reflection provided: "%s".', get_class($parent)), ParseException::INVALID_PARENT);
		}
	}


	/**
	 * Find the appropriate docblock.
	 *
	 * @return ReflectionConstant
	 */
	protected function parseDocComment(StreamBase $tokenStream, ReflectionInterface $parent)
	{
		$position = $tokenStream->key() - 1;
		while ($position > 0 && !$tokenStream->is(T_CONST, $position)) {
			$position--;
		}
		$actual = $tokenStream->key();
		parent::parseDocComment($tokenStream->seek($position), $parent);
		$tokenStream->seek($actual);
		return $this;
	}


	/**
	 * Parses reflected element metadata from the token stream.
	 */
	protected function parse(StreamBase $tokenStream, ReflectionInterface $parent)
	{
		if ($tokenStream->is(T_CONST)) {
			$tokenStream->skipWhitespaces(TRUE);
		}

		if (FALSE === $this->docComment->getDocComment()) {
			parent::parseDocComment($tokenStream, $parent);
		}

		$this->name = $this->constantParser->parseName($this->namespaceName);
		$this->valueDefinition = $this->constantParser->parseValue();
	}

}
