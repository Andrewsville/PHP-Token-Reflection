<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection;

use ApiGen\TokenReflection\Broker\Broker;
use ApiGen\TokenReflection\Exception;
use ApiGen\TokenReflection\Exception\ParseException;
use ApiGen\TokenReflection\Exception\RuntimeException;
use ApiGen\TokenReflection\Parser\FunctionParser;
use ApiGen\TokenReflection\Stream\StreamBase;


class ReflectionFunction extends ReflectionFunctionBase implements IReflectionFunction
{

	/**
	 * Imported namespace/class aliases.
	 *
	 * @var array
	 */
	private $aliases = [];


	public function __construct(StreamBase $tokenStream, Broker $broker, IReflection $parent = NULL)
	{
		$this->functionParser = new FunctionParser($tokenStream, $this, $parent);
		parent::__construct($tokenStream, $broker, $parent);
	}

	/**
	 * {@inheritdoc}
	 */
	public function isDisabled()
	{
		return $this->hasAnnotation('disabled');
	}


	/**
	 * {@inheritdoc}
	 */
	public function invoke()
	{
		return $this->invokeArgs(func_get_args());
	}


	/**
	 * {@inheritdoc}
	 */
	public function invokeArgs(array $args = [])
	{
		if ( ! function_exists($this->getName())) {
			throw new RuntimeException('Could not invoke function; function is not defined.', RuntimeException::DOES_NOT_EXIST, $this);
		}
		return call_user_func_array($this->getName(), $args);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getNamespaceAliases()
	{
		return $this->aliases;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getClosure()
	{
		if ( ! function_exists($this->getName())) {
			throw new RuntimeException('Could not invoke function; function is not defined.', RuntimeException::DOES_NOT_EXIST, $this);
		}
		$that = $this;
		return function () use ($that) {
			return $that->invokeArgs(func_get_args());
		};
	}


	/**
	 * Returns the closure scope class.
	 *
	 * @return null
	 */
	public function getClosureScopeClass()
	{
		return NULL;
	}


	/**
	 * Returns if the function definition is valid.
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
		if ( ! $parent instanceof ReflectionFileNamespace) {
			throw new ParseException($this, $tokenStream, 'The parent object has to be an instance of TokenReflection\ReflectionFileNamespace.', ParseException::INVALID_PARENT);
		}
		$this->namespaceName = $parent->getName();
		$this->aliases = $parent->getNamespaceAliases();
	}


	protected function parse(StreamBase $tokenStream, IReflection $parent)
	{
		$this->returnsReference = $this->functionParser->parseReturnReference();
		$this->name = $this->functionParser->parseName();
	}

}
