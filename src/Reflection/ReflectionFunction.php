<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Reflection;

use ApiGen\TokenReflection\Broker\Broker;
use ApiGen\TokenReflection\Exception\RuntimeException;
use ApiGen\TokenReflection\ReflectionInterface;
use ApiGen\TokenReflection\ReflectionFunctionInterface;
use ApiGen\TokenReflection\Parser\FunctionParser;
use ApiGen\TokenReflection\Stream\StreamBase;


class ReflectionFunction extends ReflectionFunctionBase implements ReflectionFunctionInterface
{

	/**
	 * Imported namespace/class aliases.
	 *
	 * @var array
	 */
	private $aliases = [];


	public function __construct(StreamBase $tokenStream, Broker $broker, ReflectionInterface $parent = NULL)
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
	 * Returns if the function definition is valid.
	 *
	 * @return bool
	 */
	public function isValid()
	{
		return TRUE;
	}


	protected function parse(StreamBase $tokenStream, ReflectionFileNamespace $parent)
	{
		$this->namespaceName = $parent->getName();
		$this->aliases = $parent->getNamespaceAliases();

		$this->returnsReference = $this->functionParser->parseReturnReference();
		$this->name = $this->functionParser->parseName();

		$this->parseParameters($tokenStream);
		$this->parseStaticVariables($tokenStream);
	}

}
