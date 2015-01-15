<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection;

use ApiGen\TokenReflection\Exception\ParseException;
use ApiGen\TokenReflection\Stream\StreamBase;


class ReflectionConstant extends ReflectionElement implements IReflectionConstant
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
	 * {@inheritdoc}
	 */
	public function getShortName()
	{
		$name = $this->getName();
		if (NULL !== $this->namespaceName && $this->namespaceName !== ReflectionNamespace::NO_NAMESPACE_NAME) {
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
		return NULL === $this->namespaceName || $this->namespaceName === ReflectionNamespace::NO_NAMESPACE_NAME ? '' : $this->namespaceName;
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
		return NULL === $this->declaringClassName ? parent::getPrettyName() : sprintf('%s::%s', $this->declaringClassName, $this->name);
	}


	/**
	 * Returns if the constant definition is valid.
	 *
	 * @return bool
	 */
	public function isValid()
	{
		return TRUE;
	}


	/**
	 * Processes the parent reflection object.
	 *
	 * @return ReflectionElement
	 * @throws ParseException If an invalid parent reflection object was provided.
	 */
	protected function processParent(IReflection $parent, StreamBase $tokenStream)
	{
		if ($parent instanceof ReflectionFileNamespace) {
			$this->namespaceName = $parent->getName();
			$this->aliases = $parent->getNamespaceAliases();
		} elseif ($parent instanceof ReflectionClass) {
			$this->declaringClassName = $parent->getName();
		} else {
			throw new ParseException($this, $tokenStream, sprintf('Invalid parent reflection provided: "%s".', get_class($parent)), ParseException::INVALID_PARENT);
		}
		return parent::processParent($parent, $tokenStream);
	}


	/**
	 * Find the appropriate docblock.
	 *
	 * @return ReflectionConstant
	 */
	protected function parseDocComment(StreamBase $tokenStream, IReflection $parent)
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
	 *
	 * @return ReflectionConstant
	 */
	protected function parse(StreamBase $tokenStream, IReflection $parent)
	{
		if ($tokenStream->is(T_CONST)) {
			$tokenStream->skipWhitespaces(TRUE);
		}
		if (FALSE === $this->docComment->getDocComment()) {
			parent::parseDocComment($tokenStream, $parent);
		}
		return $this->parseName($tokenStream)
			->parseValue($tokenStream, $parent);
	}


	/**
	 * Parses the constant name.
	 *
	 * @return ReflectionConstant
	 * @throws ParseException If the constant name could not be determined.
	 */
	protected function parseName(StreamBase $tokenStream)
	{
		if ( ! $tokenStream->is(T_STRING)) {
			throw new ParseException($this, $tokenStream, 'The constant name could not be determined.', ParseException::LOGICAL_ERROR);
		}
		if (NULL === $this->namespaceName || $this->namespaceName === ReflectionNamespace::NO_NAMESPACE_NAME) {
			$this->name = $tokenStream->getTokenValue();
		} else {
			$this->name = $this->namespaceName . '\\' . $tokenStream->getTokenValue();
		}
		$tokenStream->skipWhitespaces(TRUE);
		return $this;
	}


	/**
	 * Parses the constant value.
	 *
	 * @return ReflectionConstant
	 * @throws ParseException If the constant value could not be determined.
	 */
	private function parseValue(StreamBase $tokenStream, IReflection $parent)
	{
		if ( ! $tokenStream->is('=')) {
			throw new ParseException($this, $tokenStream, 'Could not find the definition start.', ParseException::UNEXPECTED_TOKEN);
		}
		$tokenStream->skipWhitespaces(TRUE);
		static $acceptedTokens = [
			'-' => TRUE,
			'+' => TRUE,
			T_STRING => TRUE,
			T_NS_SEPARATOR => TRUE,
			T_CONSTANT_ENCAPSED_STRING => TRUE,
			T_DNUMBER => TRUE,
			T_LNUMBER => TRUE,
			T_DOUBLE_COLON => TRUE,
			T_CLASS_C => TRUE,
			T_DIR => TRUE,
			T_FILE => TRUE,
			T_FUNC_C => TRUE,
			T_LINE => TRUE,
			T_METHOD_C => TRUE,
			T_NS_C => TRUE,
			T_TRAIT_C => TRUE
		];
		while (NULL !== ($type = $tokenStream->getType())) {
			if (T_START_HEREDOC === $type) {
				$this->valueDefinition[] = $tokenStream->current();
				while (NULL !== $type && T_END_HEREDOC !== $type) {
					$tokenStream->next();
					$this->valueDefinition[] = $tokenStream->current();
					$type = $tokenStream->getType();
				};
				$tokenStream->next();
			} elseif (isset($acceptedTokens[$type])) {
				$this->valueDefinition[] = $tokenStream->current();
				$tokenStream->next();
			} elseif ($tokenStream->isWhitespace(TRUE)) {
				$tokenStream->skipWhitespaces(TRUE);
			} else {
				break;
			}
		}
		if (empty($this->valueDefinition)) {
			throw new ParseException($this, $tokenStream, 'Value definition is empty.', ParseException::LOGICAL_ERROR);
		}
		$value = $tokenStream->getTokenValue();
		if (NULL === $type || (',' !== $value && ';' !== $value)) {
			throw new ParseException($this, $tokenStream, 'Invalid value definition.', ParseException::LOGICAL_ERROR);
		}
		return $this;
	}

}
