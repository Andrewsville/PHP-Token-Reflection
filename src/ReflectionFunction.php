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
use ApiGen\TokenReflection\Stream\StreamBase;


class ReflectionFunction extends ReflectionFunctionBase implements IReflectionFunction
{

	/**
	 * Imported namespace/class aliases.
	 *
	 * @var array
	 */
	private $aliases = [];


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
	public function __toString()
	{
		$parameters = '';
		if ($this->getNumberOfParameters() > 0) {
			$buffer = '';
			foreach ($this->getParameters() as $parameter) {
				$buffer .= "\n    " . $parameter->__toString();
			}
			$parameters = sprintf(
				"\n\n  - Parameters [%d] {%s\n  }",
				$this->getNumberOfParameters(),
				$buffer
			);
		}
		return sprintf(
			"%sFunction [ <user> function %s%s ] {\n  @@ %s %d - %d%s\n}\n",
			$this->getDocComment() ? $this->getDocComment() . "\n" : '',
			$this->returnsReference() ? '&' : '',
			$this->getName(),
			$this->getFileName(),
			$this->getStartLine(),
			$this->getEndLine(),
			$parameters
		);
	}


	/**
	 * @param Broker $broker
	 * @param string $function Function name
	 * @param bool $return Return the export instead of outputting it
	 * @return string|NULL
	 * @throws RuntimeException If requested parameter doesn't exist.
	 */
	public static function export(Broker $broker, $function, $return = FALSE)
	{
		$functionName = $function;
		$function = $broker->getFunction($functionName);
		if (NULL === $function) {
			throw new RuntimeException(sprintf('Function %s() does not exist.', $functionName), RuntimeException::DOES_NOT_EXIST);
		}
		if ($return) {
			return $function->__toString();
		}
		echo $function->__toString();
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
		return parent::processParent($parent, $tokenStream);
	}


	/**
	 * Parses reflected element metadata from the token stream.
	 *
	 * @return ReflectionFunction
	 */
	protected function parse(StreamBase $tokenStream, IReflection $parent)
	{
		return $this->parseReturnsReference($tokenStream)
			->parseName($tokenStream);
	}

}
