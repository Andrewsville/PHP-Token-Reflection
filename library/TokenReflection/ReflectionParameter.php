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
 * Tokenized function/method parameter reflection.
 */
class ReflectionParameter extends ReflectionBase implements IReflectionParameter
{
	/**
	 * The parameter requires an array as its value.
	 *
	 * @var string
	 */
	CONST ARRAY_CONSTRAINT = 'array';

	/**
	 * Defines if the default value definitions should be parsed (eval-ed).
	 *
	 * @var boolean
	 */
	private static $parseValueDefinitions = false;

	/**
	 * Defines a constraint (class name or array) of parameter values.
	 *
	 * @var string
	 */
	private $valueConstraint;

	/**
	 * Defines a constraint (class name or array) of parameter values as it was defined.
	 *
	 * @var string
	 */
	private $originalValueConstraint;

	/**
	 * Parameter default value definition (part of the source code).
	 *
	 * @var string
	 */
	private $defaultValueDefinition;

	/**
	 * Parameter default value.
	 *
	 * @var mixed
	 */
	private $defaultValue;

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
	 * Determines if the value is passed by reference.
	 *
	 * @var boolean
	 */
	private $passedByReference = false;

	/**
	 * Determines if the parameter is optional.
	 *
	 * @var boolean
	 */
	private $isOptional;

	/**
	 * Position of the parameter in the function/method.
	 *
	 * @var integer
	 */
	private $position;

	/**
	 * Returns if the the parameter allows NULL.
	 *
	 * @return boolean
	 */
	public function allowsNull()
	{
		return true;
	}

	/**
	 * Returns reflection of the required class of the value.
	 *
	 * @return \TokenReflection\ReflectionClass|\TokenReflection\ReflectionPhpClass|null
	 */
	public function getClass()
	{
		$name = $this->getClassName();
		if (null === $name) {
			return null;
		}

		return $this->getBroker()->getClass($name);
	}

	/**
	 * Returns the required class name of the value.
	 *
	 * @return string|null
	 */
	public function getClassName()
	{
		if ($this->isArray()) {
			return null;
		}

		if (null === $this->valueConstraint && null !== $this->originalValueConstraint) {
			if (null !== $this->declaringClassName) {
				$parent = $this->getDeclaringClass();
				if (null === $parent || !$parent->isTokenized()) {
					throw new Exception(sprintf('Could not load class %s reflection', $this->declaringClassName));
				}
			} else {
				$parent = $this->getDeclaringFunction();
				if (null === $parent || !$parent->isTokenized()) {
					throw new Exception(sprintf('Could not load function %s reflection', $this->declaringFunctionName));
				}
			}

			$this->valueConstraint = self::resolveClassFQN($this->originalValueConstraint, $parent->getNamespaceAliases(), $parent->getNamespaceName());
		}

		return $this->valueConstraint;
	}

	/**
	 * Returns the required class name of the value as it was defined in the source code.
	 *
	 * @return string
	 */
	public function getOriginalClassName()
	{
		return !$this->isArray() ? ltrim($this->originalValueConstraint, '\\') : null;
	}

	/**
	 * Returns the declaring class.
	 *
	 * @return \TokenReflection\ReflectionClass|null
	 */
	public function getDeclaringClass()
	{
		return null === $this->declaringClassName ? null : $this->getBroker()->getClass($this->declaringClassName);
	}

	/**
	 * Returns the declaring class name.
	 *
	 * @return string|null
	 */
	public function getDeclaringClassName()
	{
		return $this->declaringClassName;
	}

	/**
	 * Returns the declaring function.
	 *
	 * @return \TokenReflection\ReflectionFunctionBase
	 */
	public function getDeclaringFunction()
	{
		if (null !== $this->declaringClassName) {
			// method parameter
			$class = $this->getBroker()->getClass($this->declaringClassName);
			if (null !== $class) {
				return $class->getMethod($this->declaringFunctionName);
			}
		} else {
			// function parameter
			return $this->getBroker()->getFunction($this->declaringFunctionName);
		}
	}

	/**
	 * Returns the declaring function name.
	 *
	 * @return string|null
	 */
	public function getDeclaringFunctionName()
	{
		return $this->declaringFunctionName;
	}

	/**
	 * Returns the default value.
	 *
	 * @return mixed
	 */
	public function getDefaultValue()
	{
		if (null === $this->defaultValueDefinition) {
			throw new Exception('Property has no default value', Exception::DOES_NOT_EXIST);
		}

		return $this->defaultValue;
	}

	/**
	 * Returns the part of the source code defining the parameter default value.
	 *
	 * @return string
	 */
	public function getDefaultValueDefinition()
	{
		return $this->defaultValueDefinition;
	}

	/**
	 * Returns the position within all parameters.
	 *
	 * @return integer
	 */
	public function getPosition()
	{
		return $this->position;
	}

	/**
	 * Returns if the parameter expects an array.
	 *
	 * @return boolean
	 */
	public function isArray()
	{
		return $this->valueConstraint === self::ARRAY_CONSTRAINT;
	}

	/**
	 * Retutns if a default value for the parameter is available.
	 *
	 * @return boolean
	 */
	public function isDefaultValueAvailable()
	{
		return null !== $this->defaultValueDefinition;
	}

	/**
	 * Returns the docblock definition of the parameter.
	 *
	 * @return string|false
	 */
	public function getInheritedDocComment()
	{
		return $this->getDocComment();
	}

	/**
	 * Returns if the parameter is optional.
	 *
	 * @return boolean
	 */
	public function isOptional()
	{
		if (null === $this->isOptional) {
			$function = $this->getDeclaringFunction();
			if (null === $function) {
				throw new RuntimeException(sprintf('Could not get the declaring function %s reflection.', $this->declaringFunctionName));
			}

			$this->isOptional = true;
			foreach (array_slice($function->getParameters(), $this->position) as $reflectionParameter) {
				if (!$reflectionParameter->isDefaultValueAvailable()) {
					$this->isOptional = false;
					break;
				}
			}
		}

		return $this->isOptional;
	}

	/**
	 * Returns if the parameter value is passed by reference.
	 *
	 * @return boolean
	 */
	public function isPassedByReference()
	{
		return $this->passedByReference;
	}

	/**
	 * Processes the parent reflection object.
	 *
	 * @param \TokenReflection\IReflection $parent Parent reflection object
	 * @return \TokenReflection\ReflectionBase
	 */
	protected function processParent(IReflection $parent)
	{
		if (!$parent instanceof ReflectionFunctionBase) {
			throw new RuntimeException(sprintf('The parent object has to be an instance of TokenReflection\ReflectionFunctionBase descendant class, %s given.', get_class($parent)));
		}

		// Declaring function name
		$this->declaringFunctionName = $parent->getName();

		// Position
		$this->position = count($parent->getParameters());

		// Declaring class name
		if ($parent instanceof ReflectionMethod) {
			$this->declaringClassName = $parent->getDeclaringClassName();
		}

		return parent::processParent($parent);
	}

	/**
	 * Parses reflected element metadata from the token stream.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @param \TokenReflection\IReflection $parent Parent reflection object
	 * @return \TokenReflection\ReflectionParameter
	 */
	protected function parse(Stream $tokenStream, IReflection $parent)
	{
		return $this
			->parseConstraint($tokenStream)
			->parsePassedByReference($tokenStream)
			->parseName($tokenStream)
			->parseDefaultValue($tokenStream);
	}

	/**
	 * Parses the constant name.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @return \TokenReflection\ReflectionParameter
	 */
	protected function parseName(Stream $tokenStream)
	{
		if (!$tokenStream->is(T_VARIABLE)) {
			throw new RuntimeException('Could not find the parameter variable definition');
		}

		$token = $tokenStream->current();
		$this->name = substr($token[1], 1);

		$tokenStream->skipWhitespaces();

		return $this;
	}

	/**
	 * Parses the value type constraint.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @return \TokenReflection\ReflectionParameter
	 */
	private function parseConstraint(Stream $tokenStream)
	{
		$type = $tokenStream->getType();

		if (T_ARRAY === $type) {
			$this->valueConstraint = self::ARRAY_CONSTRAINT;
			$this->originalValueConstraint = self::ARRAY_CONSTRAINT;
			$tokenStream->skipWhitespaces();
		} elseif (T_STRING === $type || T_NS_SEPARATOR === $type) {
			$className = '';
			do {
				$token = $tokenStream->current();
				$className .= $token[1];

				$tokenStream->skipWhitespaces();
				$type = $tokenStream->getType();
			} while (T_STRING === $type || T_NS_SEPARATOR === $type);

			if ('' === ltrim($className, '\\')) {
				throw new RuntimeException('Invalid value constraint definition');
			}

			$this->originalValueConstraint = $className;
		}

		return $this;
	}

	/**
	 * Parses if parameter value is passed by reference.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @return \TokenReflection\ReflectionParameter
	 */
	private function parsePassedByReference(Stream $tokenStream)
	{
		if ($tokenStream->is('&')) {
			$this->passedByReference = true;
			$tokenStream->skipWhitespaces();
		}

		return $this;
	}

	/**
	 * Parses the parameter default value.
	 *
	 * @param \TokenReflection\Stream $tokenStream Token substream
	 * @return \TokenReflection\ReflectionParameter
	 */
	private function parseDefaultValue(Stream $tokenStream)
	{
		if ($tokenStream->is('=')) {
			$tokenStream->skipWhitespaces();

			$level = 0;
			while (null !== ($type = $tokenStream->getType())) {
				switch ($type) {
					case ')':
						if (0 === $level) {
							break 2;
						}
					case '}':
					case ']':
						$level--;
						break;
					case '(':
					case '{':
					case '[':
						$level++;
						break;
					case ',':
						if (0 === $level) {
							break 2;
						}
				}

				$this->defaultValueDefinition .= $tokenStream->getTokenValue();
				$tokenStream->next();
			}

			if (',' === $type) {
				$tokenStream->next();
			} elseif (')' !== $type) {
				throw new RuntimeException('Parameter default value definition is not terminated properly');
			}

			if (self::$parseValueDefinitions) {
				// Následuje husťárna (a fucking awesomness follows)
				$this->defaultValue = @eval('return ' . $this->defaultValueDefinition . ';');
			}
		}

		return $this;
	}

	/**
	 * Sets if the default value definitions should be parsed.
	 *
	 * @param boolean $parse Should be definitions parsed
	 */
	public static function setParseValueDefinitions($parse)
	{
		self::$parseValueDefinitions = (bool) $parse;
	}

	/**
	 * Returns if the default value definitions should be parsed.
	 *
	 * @return boolean
	 */
	public static function getParseValueDefinitions()
	{
		return self::$parseValueDefinitions;
	}
}
