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
use ApiGen\TokenReflection\Parser\ElementParser;
use ApiGen\TokenReflection\Parser\ParameterParser;
use ApiGen\TokenReflection\ReflectionParameterInterface;
use ApiGen\TokenReflection\Resolver;
use ApiGen\TokenReflection\Stream\StreamBase;


class ReflectionParameter extends ReflectionElement implements ReflectionParameterInterface
{

	/**
	 * The parameter requires an array as its value.
	 *
	 * @var string
	 */
	const ARRAY_TYPE_HINT = 'array';

	/**
	 * The parameter requires a callback definition as its value.
	 *
	 * @var string
	 */
	const CALLABLE_TYPE_HINT = 'callable';

	/**
	 * Declaring class name.
	 *
	 * @var string
	 */
	private $declaringClassName;

	/**
	 * Declaring function name.
	 *
	 * @var string
	 */
	private $declaringFunctionName;

	/**
	 * Parameter default value.
	 *
	 * @var mixed
	 */
	private $defaultValue;

	/**
	 * Parameter default value definition (part of the source code).
	 *
	 * @var array|string
	 */
	private $defaultValueDefinition = [];

	/**
	 * Defines a type hint (class name or array) of parameter values.
	 *
	 * @var string
	 */
	private $typeHint;

	/**
	 * Defines a type hint (class name, array or callable) of parameter values as it was defined.
	 *
	 * @var string
	 */
	private $originalTypeHint;

	/**
	 * Position of the parameter in the function/method.
	 *
	 * @var int
	 */
	private $position;

	/**
	 * @var bool
	 */
	private $isOptional;

	/**
	 * @var bool
	 */
	private $isVariadic = FALSE;

	/**
	 * @var bool
	 */
	private $passedByReference = FALSE;


	public function __construct(StreamBase $tokenStream, Broker $broker, ReflectionFunctionBase $parent = NULL)
	{
		$this->broker = $broker;
		$this->parse($tokenStream, $parent);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDeclaringClass()
	{
		return $this->declaringClassName === NULL ? NULL : $this->getBroker()->getClass($this->declaringClassName);
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
	public function getDeclaringFunction()
	{
		if ($this->declaringClassName !== NULL) {
			// Method parameter
			$class = $this->getBroker()->getClass($this->declaringClassName);
			if ($class !== NULL) {
				return $class->getMethod($this->declaringFunctionName);
			}
		} else {
			// Function parameter
			return $this->getBroker()->getFunction($this->declaringFunctionName);
		}
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDeclaringFunctionName()
	{
		return $this->declaringFunctionName;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDefaultValue()
	{
		if ( ! $this->isOptional()) {
			throw new RuntimeException('Property is not optional.');
		}
		if ($this->defaultValue === NULL) {
			if (count($this->defaultValueDefinition) === 0) {
				throw new RuntimeException('Property has no default value.');
			}
			$this->defaultValue = Resolver::getValueDefinition($this->defaultValueDefinition, $this);
		}
		return $this->defaultValue;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDefaultValueDefinition()
	{
		return Resolver::getSourceCode($this->defaultValueDefinition);
	}


	/**
	 * {@inheritdoc}
	 */
	public function isDefaultValueConstant()
	{
		if ( ! $this->isDefaultValueAvailable() || empty($this->defaultValueDefinition)) {
			return FALSE;
		}
		static $expected = [T_STRING => TRUE, T_NS_SEPARATOR => TRUE, T_DOUBLE_COLON => TRUE];
		foreach ($this->defaultValueDefinition as $token) {
			if ( ! isset($expected[$token[0]])) {
				return FALSE;
			}
		}
		return TRUE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDefaultValueConstantName()
	{
		if ( ! $this->isOptional()) {
			throw new RuntimeException('Property is not optional.');
		}
		return $this->isDefaultValueConstant() ? $this->getDefaultValueDefinition() : NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isDefaultValueAvailable()
	{
		return $this->isOptional();
	}


	/**
	 * {@inheritdoc}
	 */
	public function getPosition()
	{
		return $this->position;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isArray()
	{
		return $this->typeHint === self::ARRAY_TYPE_HINT;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isCallable()
	{
		return $this->typeHint === self::CALLABLE_TYPE_HINT;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getOriginalTypeHint()
	{
		return !$this->isArray() && !$this->isCallable() ? ltrim($this->originalTypeHint, '\\') : NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getClass()
	{
		$name = $this->getClassName();
		if ($name === NULL) {
			return NULL;
		}
		return $this->getBroker()->getClass($name);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getClassName()
	{
		if ($this->isArray() || $this->isCallable()) {
			return NULL;
		}
		if ($this->typeHint === NULL && NULL !== $this->originalTypeHint) {
			if ($this->declaringClassName !== NULL) {
				$parent = $this->getDeclaringClass();
				if ($parent === NULL) {
					throw new RuntimeException('Could not load class reflection.');
				}

			} else {
				$parent = $this->getDeclaringFunction();
				if ($parent === NULL || ! $parent->isTokenized()) {
					throw new RuntimeException('Could not load function reflection.');
				}
			}
			$lTypeHint = strtolower($this->originalTypeHint);
			if ($lTypeHint === 'parent' || $lTypeHint === 'self') {
				if (NULL === $this->declaringClassName) {
					throw new RuntimeException('Parameter type hint cannot be "self" nor "parent" when not a method.');
				}
				if ($lTypeHint === 'parent') {
					if ($parent->isInterface() || $parent->getParentClassName() === NULL) {
						throw new RuntimeException('Class has no parent.');
					}
					$this->typeHint = $parent->getParentClassName();

				} else {
					$this->typeHint = $this->declaringClassName;
				}

			} else {
				$this->typeHint = ltrim(Resolver::resolveClassFQN($this->originalTypeHint, $parent->getNamespaceAliases(), $parent->getNamespaceName()), '\\');
			}
		}
		return $this->typeHint;
	}


	/**
	 * {@inheritdoc}
	 */
	public function allowsNull()
	{
		if ($this->isArray() || $this->isCallable()) {
			return strtolower($this->getDefaultValueDefinition()) === 'null';
		}
		return $this->originalTypeHint === NULL || ! empty($this->defaultValueDefinition);
	}


	/**
	 * {@inheritdoc}
	 */
	public function isOptional()
	{
		if ($this->isOptional === NULL) {
			$this->isOptional = !empty($this->defaultValueDefinition) && $this->haveSiblingsDefaultValues();
		}
		return $this->isOptional;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isVariadic()
	{
		return $this->isVariadic;
	}


	/**
	 * Returns if all following parameters have a default value definition.
	 *
	 * @return bool
	 */
	protected function haveSiblingsDefaultValues()
	{
		$function = $this->getDeclaringFunction();
		if ($function === NULL) {
			throw new RuntimeException('Could not get the declaring function reflection.');
		}

		/** @var ReflectionParameter $reflectionParameter */
		foreach (array_slice($function->getParameters(), $this->position + 1) as $reflectionParameter) {
			if ($reflectionParameter->getDefaultValueDefinition() === NULL) {
				return FALSE;
			}
		}
		return TRUE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isPassedByReference()
	{
		return $this->passedByReference;
	}


	/**
	 * {@inheritdoc}
	 */
	public function canBePassedByValue()
	{
		return ! $this->isPassedByReference();
	}


	/**
	 * {@inheritdoc}
	 */
	public function getPrettyName()
	{
		return str_replace('()', '($' . $this->name . ')', $this->getDeclaringFunction()->getPrettyName());
	}


	/**
	 * {@inheritdoc}
	 */
	public function getNamespaceAliases()
	{
		return $this->getDeclaringFunction()->getNamespaceAliases();
	}


	/**
	 * Creates a parameter alias for the given method.
	 *
	 * @return ReflectionParameter
	 */
	public function alias(ReflectionMethod $parent)
	{
		$parameter = clone $this;
		$parameter->declaringClassName = $parent->getDeclaringClassName();
		$parameter->declaringFunctionName = $parent->getName();
		return $parameter;
	}


	private function parse(StreamBase $tokenStream, ReflectionFunctionBase $parent)
	{
		$elementParser = new ElementParser($tokenStream, $this, $parent);
		$parameterParser = new ParameterParser($tokenStream, $this, $parent);

		$this->fileName = $tokenStream->getFileName();

		$this->declaringFunctionName = $parent->getName();
		$this->position = count($parent->getParameters());
		if ($parent instanceof ReflectionMethod) {
			$this->declaringClassName = $parent->getDeclaringClassName();
		}

		$this->startLine = $elementParser->parseLineNumber();
		$this->startPosition = $elementParser->parsePosition();

		list($this->docComment, $this->startPosition) = $elementParser->parseDocComment($this->startPosition);
		list($this->typeHint, $this->originalTypeHint) = $parameterParser->parseTypeHint();

		$this->passedByReference = $parameterParser->parsePassedByReference();

		$this->isVariadic = $parameterParser->parseIsVariadic();
		$this->name = $parameterParser->parseName();

		$this->defaultValueDefinition = $parameterParser->parseDefaultValue();

		$this->endLine = $elementParser->parseLineNumber();
		$this->endPosition = $elementParser->parsePosition();
	}

}
