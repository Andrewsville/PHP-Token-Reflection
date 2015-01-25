<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Reflection;

use ApiGen\TokenReflection\Exception\ParseException;
use ApiGen\TokenReflection\Exception\RuntimeException;
use ApiGen\TokenReflection\ReflectionFunctionBaseInterface;
use ApiGen\TokenReflection\ReflectionParameterInterface;
use ApiGen\TokenReflection\Resolver;
use ApiGen\TokenReflection\Stream\StreamBase;


abstract class ReflectionFunctionBase extends ReflectionElement implements ReflectionFunctionBaseInterface
{

	/**
	 * @var string
	 */
	protected $namespaceName;

	/**
	 * @var bool
	 */
	protected $returnsReference = FALSE;

	/**
	 * @var ReflectionParameterInterface[]
	 */
	protected $parameters = [];

	/**
	 * @var array
	 */
	protected $staticVariables = [];

	/**
	 * @var array
	 */
	protected $staticVariablesDefinition = [];

	/**
	 * @var bool|NULL
	 */
	private $isVariadic;


	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		if ($this->namespaceName !== NULL && $this->namespaceName !== ReflectionNamespace::NO_NAMESPACE_NAME) {
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
		return $this->getNamespaceName() !== '';
	}


	/**
	 * @return bool
	 */
	public function returnsReference()
	{
		return $this->returnsReference;
	}


	/**
	 * @param int|string $parameter Parameter name or position
	 * @return ReflectionParameter
	 */
	public function getParameter($parameter)
	{
		if (is_numeric($parameter)) {
			if ( ! isset($this->parameters[$parameter])) {
				throw new RuntimeException(sprintf('There is no parameter at position "%d".', $parameter));
			}
			return $this->parameters[$parameter];
		} else {
			foreach ($this->parameters as $reflection) {
				if ($reflection->getName() === $parameter) {
					return $reflection;
				}
			}
			throw new RuntimeException(sprintf('There is no parameter "%s".', $parameter));
		}
	}


	/**
	 * @return ReflectionParameterInterface[]
	 */
	public function getParameters()
	{
		return $this->parameters;
	}


	/**
	 * @return int
	 */
	public function getNumberOfParameters()
	{
		return count($this->parameters);
	}


	/**
	 * @return int
	 */
	public function getNumberOfRequiredParameters()
	{
		$count = 0;
		array_walk($this->parameters, function (ReflectionParameter $parameter) use (&$count) {
			if ( ! $parameter->isOptional()) {
				$count++;
			}
		});
		return $count;
	}


	/**
	 * @return array
	 */
	public function getStaticVariables()
	{
		if (empty($this->staticVariables) && !empty($this->staticVariablesDefinition)) {
			foreach ($this->staticVariablesDefinition as $variableName => $variableDefinition) {
				$this->staticVariables[$variableName] = Resolver::getValueDefinition($variableDefinition, $this);
			}
		}
		return $this->staticVariables;
	}


	/**
	 * @return bool
	 */
	public function isVariadic()
	{
		if ( ! isset($this->isVariadic)) {
			/** @var ReflectionParameterInterface $lastParameter */
			$lastParameter = end($this->parameters);
			$this->isVariadic = $lastParameter && $lastParameter->isVariadic();
		};
		return $this->isVariadic;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getPrettyName()
	{
		return $this->name . '()';
	}


	/**
	 * Creates aliases to parameters.
	 *
	 * @throws RuntimeException When called on a ReflectionFunction instance.
	 */
	protected function aliasParameters()
	{
		if ( ! $this instanceof ReflectionMethod) {
			throw new RuntimeException('Only method parameters can be aliased.', RuntimeException::UNSUPPORTED, $this);
		}
		foreach ($this->parameters as $index => $parameter) {
			$this->parameters[$index] = $parameter->alias($this);
		}
	}


	/**
	 * Parses function/method parameters.
	 *
	 * @return ReflectionFunctionBase
	 * @throws ParseException If parameters could not be parsed.
	 */
	protected function parseParameters(StreamBase $tokenStream)
	{
		if ( ! $tokenStream->is('(')) {
			throw new ParseException('Could find the start token.', ParseException::UNEXPECTED_TOKEN);
		}
		static $accepted = [T_NS_SEPARATOR => TRUE, T_STRING => TRUE, T_ARRAY => TRUE, T_CALLABLE => TRUE, T_VARIABLE => TRUE, '&' => TRUE];
		if (PHP_VERSION_ID >= 50600 && ! isset($accepted[T_ELLIPSIS])) {
			$accepted += [T_ELLIPSIS => TRUE];
		}

		$tokenStream->skipWhitespaces(TRUE);
		while (NULL !== ($type = $tokenStream->getType()) && ')' !== $type) {
			if (isset($accepted[$type])) {
				$parameter = new ReflectionParameter($tokenStream, $this->storage, $this);
				$this->parameters[] = $parameter;
			}
			if ($tokenStream->is(')')) {
				break;
			}
			$tokenStream->skipWhitespaces(TRUE);
		}
		$tokenStream->skipWhitespaces();
		return $this;
	}


	/**
	 * @return ReflectionFunctionBase
	 * @throws ParseException If static variables could not be parsed.
	 */
	protected function parseStaticVariables(StreamBase $tokenStream)
	{
		$type = $tokenStream->getType();
		if ('{' === $type) {
			$tokenStream->skipWhitespaces(TRUE);
			while ('}' !== ($type = $tokenStream->getType())) {
				switch ($type) {
					case T_STATIC:
						$type = $tokenStream->skipWhitespaces(TRUE)->getType();
						if (T_VARIABLE !== $type) {
							// Late static binding
							break;
						}
						while (T_VARIABLE === $type) {
							$variableName = $tokenStream->getTokenValue();
							$variableDefinition = [];
							$type = $tokenStream->skipWhitespaces(TRUE)->getType();
							if ('=' === $type) {
								$type = $tokenStream->skipWhitespaces(TRUE)->getType();
								$level = 0;
								while ($tokenStream->valid()) {
									switch ($type) {
										case '(':
										case '[':
										case '{':
										case T_CURLY_OPEN:
										case T_DOLLAR_OPEN_CURLY_BRACES:
											$level++;
											break;
										case ')':
										case ']':
										case '}':
											$level--;
											break;
										case ';':
										case ',':
											if (0 === $level) {
												break 2;
											}
										default:
											break;
									}
									$variableDefinition[] = $tokenStream->current();
									$type = $tokenStream->skipWhitespaces(TRUE)->getType();
								}
								if ( ! $tokenStream->valid()) {
									throw new ParseException('Invalid end of token stream.', ParseException::READ_BEYOND_EOS);
								}
							}
							$this->staticVariablesDefinition[substr($variableName, 1)] = $variableDefinition;
							if (',' === $type) {
								$type = $tokenStream->skipWhitespaces(TRUE)->getType();
							} else {
								break;
							}
						}
						break;
					case T_FUNCTION:
						// Anonymous function -> skip to its end
						if ( ! $tokenStream->find('{')) {
							throw new ParseException('Could not find beginning of the anonymous function.', ParseException::UNEXPECTED_TOKEN);
						}
					// Break missing intentionally
					case '{':
					case '[':
					case '(':
					case T_CURLY_OPEN:
					case T_DOLLAR_OPEN_CURLY_BRACES:
						$tokenStream->findMatchingBracket()->skipWhitespaces(TRUE);
						break;
					default:
						$tokenStream->skipWhitespaces();
						break;
				}
			}

		} elseif (';' !== $type) {
			throw new ParseException('Unexpected token found.', ParseException::UNEXPECTED_TOKEN);
		}
		return $this;
	}

}
