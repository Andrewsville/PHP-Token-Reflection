<?php
/**
 * PHP Token Reflection
 *
 * Development version
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this library in the file license.txt.
 *
 * @author Ondřej Nešpor <andrew@andrewsville.cz>
 * @author Jaroslav Hanslík <kukulich@kukulich.cz>
 */

namespace TokenReflection;

use RuntimeException;

/**
 * Base abstract class for tokenized function and method.
 */
abstract class ReflectionFunctionBase extends ReflectionBase implements IReflectionFunctionBase
{
	/**
	 * Function/method modifiers.
	 *
	 * @var integer
	 */
	protected $modifiers = 0;

	/**
	 * Static variables defined within the function/method.
	 *
	 * @var array
	 */
	private $staticVariables = array();

	/**
	 * Function/method namespace name.
	 *
	 * @var string
	 */
	protected $namespaceName;

	/**
	 * Determines if the function returns its value as reference.
	 *
	 * @var boolean
	 */
	private $returnsReference = false;

	/**
	 * Parameters array.
	 *
	 * @var array
	 */
	private $parameters = array();

	/**
	 * Returns function/method modifiers.
	 *
	 * @return integer
	 */
	public function getModifiers()
	{
		return $this->modifiers;
	}

	/**
	 * Returns if the function/method is defined within a namespace.
	 *
	 * @return boolean
	 */
	public function inNamespace()
	{
		return null !== $this->getNamespaceName();
	}

	/**
	 * Returns the namespace name.
	 *
	 * @return string
	 */
	public function getNamespaceName()
	{
		return $this->namespaceName === ReflectionNamespace::NO_NAMESPACE_NAME ? null : $this->namespaceName;
	}

	/**
	 * Returns the number of parameters.
	 *
	 * @return integer
	 */
	public function getNumberOfParameters()
	{
		return count($this->parameters);
	}

	/**
	 * Returns the number of required parameters.
	 *
	 * @return integer
	 */
	public function getNumberOfRequiredParameters()
	{
		$count = 0;
		array_walk($this->parameters, function(ReflectionParameter $parameter) use(&$count) {
			if (!$parameter->isOptional()) {
				$count++;
			}
		});
		return $count;
	}

	/**
	 * Returns an array of parameters.
	 *
	 * @return array
	 */
	public function getParameters()
	{
		return $this->parameters;
	}

	/**
	 * Returns a particular function/method parameter.
	 *
	 * @param integer|string $parameter Parameter name or position
	 * @return \TokenReflection\ReflectionParameter
	 */
	public function getParameter($parameter)
	{
		if (is_numeric($parameter)) {
			if (isset($this->parameters[$parameter])) {
				return $this->parameters[$parameter];
			} else {
				throw new Exception(sprintf('There is no parameter at position %d', $parameter), Exception::DOES_NOT_EXIST);
			}
		} else {
			foreach ($this->parameters as $reflection) {
				if ($reflection->getName() === $parameter) {
					return $reflection;
				}
			}

			throw new Exception(sprintf('There is no parameter %s', $parameter), Exception::DOES_NOT_EXIST);
		}
	}

	/**
	 * Returns static variables.
	 *
	 * @return array
	 */
	public function getStaticVariables()
	{
		return $this->staticVariables;
	}

	/**
	 * Returns if the method is a closure.
	 *
	 * @return boolean
	 */
	public function isClosure()
	{
		return false;
	}

	/**
	 * Returns if the method returns its value as reference.
	 *
	 * @return boolean
	 */
	public function returnsReference()
	{
		return $this->returnsReference;
	}

	/**
	 * Returns the method/function FQN.
	 *
	 * @return string
	 */
	public function getName()
	{
		if (null !== $this->namespaceName && ReflectionNamespace::NO_NAMESPACE_NAME !== $this->namespaceName) {
			return $this->namespaceName . '\\' . $this->name;
		}

		return $this->name;
	}

	/**
	 * Returns the method/function UQN.
	 *
	 * @return string
	 */
	public function getShortName()
	{
		return $this->name;
	}

	/**
	 * Parses the function/method name.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @return \TokenReflection\ReflectionMethod
	 */
	protected function parseName(Stream $tokenStream)
	{
		if (!$tokenStream->is(T_STRING)) {
			throw new RuntimeException('Could not determine the method/function name');
		}

		$this->name = $tokenStream->getTokenValue();

		$tokenStream->skipWhitespaces();

		return $this;
	}

	/**
	 * Parses child reflection objects from the token stream.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @return \TokenReflection\ReflectionBase
	 */
	final protected function parseChildren(Stream $tokenStream)
	{
		return $this
			->parseParameters($tokenStream)
			->parseStaticVariables($tokenStream);
	}

	/**
	 * Parses function/method parameters.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @return \TokenReflection\ReflectionFunctionBase
	 */
	final protected function parseParameters(Stream $tokenStream)
	{
		if (!$tokenStream->is('(')) {
			throw new RuntimeException('Could not determine parameters start');
		}

		$tokenStream->skipWhitespaces();

		while (null !== ($type = $tokenStream->getType()) && ')' !== $type) {
			if (T_VARIABLE === $type) {
				$parameter = new ReflectionParameter($tokenStream->getParameterStream(), $this->getBroker(), $this);
				$this->parameters[] = $parameter;
			}

			if ($tokenStream->is(')')) {
				break;
			} else {
				$tokenStream->skipWhitespaces();
			}
		}

		return $this;
	}

	/**
	 * Parses static variables.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @return \TokenReflection\ReflectionFunctionBase
	 */
	final protected function parseStaticVariables(Stream $tokenStream)
	{
		// @todo
		return $this;
	}

	/**
	 * Parses if the function/method returns its value as reference.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @return \TokenReflection\ReflectionFunctionBase
	 */
	final protected function parseReturnsReference(Stream $tokenStream)
	{
		if (!$tokenStream->is(T_FUNCTION)) {
			throw new RuntimeException('Could not find the function keyword.');
		}

		$tokenStream->skipWhitespaces();

		$type = $tokenStream->getType();

		if ('&' === $type) {
			$this->returnsReference = true;
			$tokenStream->skipWhitespaces();
		} elseif (!T_STRING === $type) {
			throw new RuntimeException(sprintf('Unexpected token type: %s', $tokenStream->getTokenName()));
		}

		return $this;
	}
}