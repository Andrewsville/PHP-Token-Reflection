<?php
/**
 * PHP Token Reflection
 *
 * Version 1.0 beta 3
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this library in the file LICENSE.
 *
 * @author Ondřej Nešpor <andrew@andrewsville.cz>
 * @author Jaroslav Hanslík <kukulich@kukulich.cz>
 */

namespace TokenReflection;

use TokenReflection\Exception;

/**
 * Base abstract class for tokenized function and method.
 */
abstract class ReflectionFunctionBase extends ReflectionBase implements IReflectionFunctionBase
{
	/**
	 * Function/method namespace name.
	 *
	 * @var string
	 */
	protected $namespaceName;

	/**
	 * Function/method modifiers.
	 *
	 * @var integer
	 */
	protected $modifiers = 0;

	/**
	 * Determines if the function/method returns its value as reference.
	 *
	 * @var boolean
	 */
	private $returnsReference = false;

	/**
	 * Parameters.
	 *
	 * @var array
	 */
	private $parameters = array();

	/**
	 * Static variables defined within the function/method.
	 *
	 * @var array
	 */
	private $staticVariables = array();

	/**
	 * Returns the name (FQN).
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
	 * Returns the unqualified name (UQN).
	 *
	 * @return string
	 */
	public function getShortName()
	{
		return $this->name;
	}

	/**
	 * Returns the namespace name.
	 *
	 * @return string
	 */
	public function getNamespaceName()
	{
		return null === $this->namespaceName || $this->namespaceName === ReflectionNamespace::NO_NAMESPACE_NAME ? '' : $this->namespaceName;
	}

	/**
	 * Returns if the function/method is defined within a namespace.
	 *
	 * @return boolean
	 */
	public function inNamespace()
	{
		return '' !== $this->getNamespaceName();
	}

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
	 * Returns if the function/method is a closure.
	 *
	 * @return boolean
	 */
	public function isClosure()
	{
		return false;
	}

	/**
	 * Returns if the function/method returns its value as reference.
	 *
	 * @return boolean
	 */
	public function returnsReference()
	{
		return $this->returnsReference;
	}

	/**
	 * Returns a particular function/method parameter.
	 *
	 * @param integer|string $parameter Parameter name or position
	 * @return \TokenReflection\ReflectionParameter
	 * @throws \TokenReflection\Exception\Runtime If there is no parameter of the given name
	 * @throws \TokenReflection\Exception\Runtime If there is no parameter at the given position
	 */
	public function getParameter($parameter)
	{
		if (is_numeric($parameter)) {
			if (!isset($this->parameters[$parameter])) {
				throw new Exception\Runtime(sprintf('There is no parameter at position "%d" in function/method "%s".', $parameter, $this->getName()), Exception\Runtime::DOES_NOT_EXIST);
			}
			return $this->parameters[$parameter];
		} else {
			foreach ($this->parameters as $reflection) {
				if ($reflection->getName() === $parameter) {
					return $reflection;
				}
			}

			throw new Exception\Runtime(sprintf('There is no parameter "%s" in function/method "%s".', $parameter, $this->getName()), Exception\Runtime::DOES_NOT_EXIST);
		}
	}

	/**
	 * Returns parameters.
	 *
	 * @return array
	 */
	public function getParameters()
	{
		return $this->parameters;
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
		array_walk($this->parameters, function(ReflectionParameter $parameter) use (&$count) {
			if (!$parameter->isOptional()) {
				$count++;
			}
		});
		return $count;
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
	 * Parses if the function/method returns its value as reference.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @return \TokenReflection\ReflectionFunctionBase
	 * @throws \TokenReflection\Exception\Parse If could not be determined if the function\method returns its value by reference
	 */
	final protected function parseReturnsReference(Stream $tokenStream)
	{
		try {
			if (!$tokenStream->is(T_FUNCTION)) {
				throw new Exception\Parse('Could not find the function keyword.', Exception\Parse::PARSE_ELEMENT_ERROR);
			}

			$tokenStream->skipWhitespaces();

			$type = $tokenStream->getType();

			if ('&' === $type) {
				$this->returnsReference = true;
				$tokenStream->skipWhitespaces();
			} elseif (T_STRING !== $type) {
				throw new Exception\Parse(sprintf('Invalid token found: "%s".', $tokenStream->getTokenName()), Exception\Parse::PARSE_ELEMENT_ERROR);
			}

			return $this;
		} catch (Exception\Parse $e) {
			throw new Exception\Parse('Could not determine if the function\method returns its value by reference.', Exception\Parse::PARSE_ELEMENT_ERROR, $e);
		}
	}

	/**
	 * Parses the function/method name.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @return \TokenReflection\ReflectionMethod
	 * @throws \TokenReflection\Exception\Parse If the class name could not be determined
	 */
	protected function parseName(Stream $tokenStream)
	{
		try {
			if (!$tokenStream->is(T_STRING)) {
				throw new Exception\Parse(sprintf('Invalid token found: "%s".', $tokenStream->getTokenName()), Exception\Parse::PARSE_ELEMENT_ERROR);
			}

			$this->name = $tokenStream->getTokenValue();

			$tokenStream->skipWhitespaces();

			return $this;
		} catch (Exception $e) {
			throw new Exception\Parse('Could not parse function/method name.', Exception\Parse::PARSE_ELEMENT_ERROR, $e);
		}
	}

	/**
	 * Parses child reflection objects from the token stream.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @param \TokenReflection\IReflection $parent Parent reflection object
	 * @return \TokenReflection\ReflectionBase
	 */
	final protected function parseChildren(Stream $tokenStream, IReflection $parent)
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
	 * @throws \TokenReflection\Exception\Parse If parameters could not be parsed
	 *
	 */
	final protected function parseParameters(Stream $tokenStream)
	{
		try {
			if (!$tokenStream->is('(')) {
				throw new Exception\Parse('Could find the start token.', Exception\Parse::PARSE_CHILDREN_ERROR);
			}

			static $accepted = array(T_NS_SEPARATOR => true, T_STRING => true, T_ARRAY => true, T_VARIABLE => true, '&' => true);

			$tokenStream->skipWhitespaces();

			while (null !== ($type = $tokenStream->getType()) && ')' !== $type) {
				if (isset($accepted[$type])) {
					$parameter = new ReflectionParameter($tokenStream, $this->getBroker(), $this);
					$this->parameters[] = $parameter;
				}

				if ($tokenStream->is(')')) {
					break;
				}

				$tokenStream->skipWhitespaces();
			}

			$tokenStream->skipWhitespaces();

			return $this;
		} catch (Exception $e) {
			throw new Exception\Parse(sprintf('Could not parse function/method "%s" parameters.', $this->name), Exception\Parse::PARSE_CHILDREN_ERROR, $e);
		}
	}

	/**
	 * Parses static variables.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @return \TokenReflection\ReflectionFunctionBase
	 * @throws \TokenReflection\Exception\Parse If static variables could not be parsed
	 */
	final protected function parseStaticVariables(Stream $tokenStream)
	{
		try {
			$type = $tokenStream->getType();
			if ('{' === $type) {
				// @todo finding static variables
				$tokenStream->findMatchingBracket();
			} elseif (';' !== $type) {
				throw new Exception\Parse(sprintf('Invalid token found: "%s".', $tokenStream->getTokenName()), Exception\Parse::PARSE_CHILDREN_ERROR);
			}

			return $this;
		} catch (Exception $e) {
			throw new Exception\Parse(sprintf('Could not parse function/method "%s" static variables.', $this->name), Exception\Parse::PARSE_CHILDREN_ERROR, $e);
		}
	}
}
