<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Invalid;

use ApiGen\TokenReflection;
use ApiGen\TokenReflection\IReflectionFunction;
use ApiGen\TokenReflection\Exception;
use ApiGen\TokenReflection\Broker;
use ApiGen\TokenReflection\ReflectionBase;


/**
 * Invalid function reflection.
 *
 * The reflected function is not unique.
 */
class ReflectionFunction extends ReflectionElement implements IReflectionFunction
{

	/**
	 * Class name (FQN).
	 *
	 * @var string
	 */
	private $name;

	/**
	 * Original definition file name.
	 *
	 * @var string
	 */
	private $fileName;

	/**
	 * Reflection broker.
	 *
	 * @var ApiGen\TokenReflection\Broker
	 */
	private $broker;


	/**
	 * Constructor.
	 *
	 * @param string $name Function name
	 * @param string $fileName Original definiton file name
	 * @param ApiGen\TokenReflection\Broker $broker Reflection broker
	 */
	public function __construct($name, $fileName, Broker $broker)
	{
		$this->name = ltrim($name, '\\');
		$this->broker = $broker;
		$this->fileName = $fileName;
	}


	/**
	 * Returns the name (FQN).
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * Returns the unqualified name (UQN).
	 *
	 * @return string
	 */
	public function getShortName()
	{
		$pos = strrpos($this->name, '\\');
		return FALSE === $pos ? $this->name : substr($this->name, $pos + 1);
	}


	/**
	 * Returns the namespace name.
	 *
	 * @return string
	 */
	public function getNamespaceName()
	{
		$pos = strrpos($this->name, '\\');
		return FALSE === $pos ? '' : substr($this->name, 0, $pos);
	}


	/**
	 * Returns if the class is defined within a namespace.
	 *
	 * @return bool
	 */
	public function inNamespace()
	{
		return FALSE !== strrpos($this->name, '\\');
	}


	/**
	 * Returns if the reflection object is internal.
	 *
	 * @return bool
	 */
	public function isInternal()
	{
		return FALSE;
	}


	/**
	 * Returns if the reflection object is user defined.
	 *
	 * @return bool
	 */
	public function isUserDefined()
	{
		return TRUE;
	}


	/**
	 * Returns if the current reflection comes from a tokenized source.
	 *
	 * @return bool
	 */
	public function isTokenized()
	{
		return TRUE;
	}


	/**
	 * Returns the reflection broker used by this reflection object.
	 *
	 * @return ApiGen\TokenReflection\Broker
	 */
	public function getBroker()
	{
		return $this->broker;
	}


	/**
	 * Returns an element pretty (docblock compatible) name.
	 *
	 * @return string
	 */
	public function getPrettyName()
	{
		return $this->name . '()';
	}


	/**
	 * Returns the PHP extension reflection.
	 *
	 * @return ApiGen\TokenReflection\IReflectionExtension|null
	 */
	public function getExtension()
	{
		return NULL;
	}


	/**
	 * Returns the PHP extension name.
	 *
	 * @return false
	 */
	public function getExtensionName()
	{
		return FALSE;
	}


	/**
	 * Returns the file name the reflection object is defined in.
	 *
	 * @return null
	 */
	public function getFileName()
	{
		return $this->fileName;
	}


	/**
	 * Returns a file reflection.
	 *
	 * @return ApiGen\TokenReflection\ReflectionFile
	 * @throws ApiGen\TokenReflection\Exception\RuntimeException If the file is not stored inside the broker
	 */
	public function getFileReflection()
	{
		throw new Exception\BrokerException($this->getBroker(), sprintf('Function was not parsed from a file', $this->getPrettyName()), Exception\BrokerException::UNSUPPORTED);
	}


	/**
	 * Returns the appropriate source code part.
	 *
	 * @return string
	 */
	public function getSource()
	{
		return '';
	}


	/**
	 * Returns the start position in the file token stream.
	 *
	 * @return int
	 */
	public function getStartPosition()
	{
		return -1;
	}


	/**
	 * Returns the end position in the file token stream.
	 *
	 * @return int
	 */
	public function getEndPosition()
	{
		return -1;
	}


	/**
	 * Returns the definition start line number in the file.
	 *
	 * @return int
	 */
	public function getStartLine()
	{
		return NULL;
	}


	/**
	 * Returns the definition end line number in the file.
	 *
	 * @return int
	 */
	public function getEndLine()
	{
		return NULL;
	}


	/**
	 * Returns the appropriate docblock definition.
	 *
	 * @return bool
	 */
	public function getDocComment()
	{
		return FALSE;
	}


	/**
	 * Checks if there is a particular annotation.
	 *
	 * @param string $name Annotation name
	 * @return bool
	 */
	public function hasAnnotation($name)
	{
		return FALSE;
	}


	/**
	 * Returns a particular annotation value.
	 *
	 * @param string $name Annotation name
	 * @return string|array|null
	 */
	public function getAnnotation($name)
	{
		return NULL;
	}


	/**
	 * Returns all annotations.
	 *
	 * @return array
	 */
	public function getAnnotations()
	{
		return [];
	}


	/**
	 * Returns if the function/method is a closure.
	 *
	 * @return bool
	 */
	public function isClosure()
	{
		return FALSE;
	}


	/**
	 * Returns if the function/method is deprecated.
	 *
	 * @return bool
	 */
	public function isDeprecated()
	{
		return FALSE;
	}


	/**
	 * Returns if the function/method returns its value as reference.
	 *
	 * @return bool
	 */
	public function returnsReference()
	{
		return FALSE;
	}


	/**
	 * Returns a function/method parameter.
	 *
	 * @param int|string $parameter Parameter name or position
	 */
	public function getParameter($parameter)
	{
		if (is_numeric($parameter)) {
			throw new Exception\RuntimeException(sprintf('There is no parameter at position "%d".', $parameter), Exception\RuntimeException::DOES_NOT_EXIST, $this);
		} else {
			throw new Exception\RuntimeException(sprintf('There is no parameter "%s".', $parameter), Exception\RuntimeException::DOES_NOT_EXIST, $this);
		}
	}


	/**
	 * Returns function/method parameters.
	 *
	 * @return array
	 */
	public function getParameters()
	{
		return [];
	}


	/**
	 * Returns the number of parameters.
	 *
	 * @return int
	 */
	public function getNumberOfParameters()
	{
		return 0;
	}


	/**
	 * Returns the number of required parameters.
	 *
	 * @return int
	 */
	public function getNumberOfRequiredParameters()
	{
		return 0;
	}


	/**
	 * Returns static variables.
	 *
	 * @return array
	 */
	public function getStaticVariables()
	{
		return [];
	}


	/**
	 * Returns if the method is is disabled via the disable_functions directive.
	 *
	 * @return bool
	 */
	public function isDisabled()
	{
		return FALSE;
	}


	/**
	 * Calls the function.
	 *
	 * @return mixed
	 */
	public function invoke()
	{
		return $this->invokeArgs([]);
	}


	/**
	 * Calls the function.
	 *
	 * @param array $args Function parameter values
	 * @return mixed
	 */
	public function invokeArgs(array $args)
	{
		throw new Exception\RuntimeException('Cannot invoke invalid functions', Exception\RuntimeException::UNSUPPORTED, $this);
	}


	/**
	 * Returns imported namespaces and aliases from the declaring namespace.
	 *
	 * @return array
	 */
	public function getNamespaceAliases()
	{
		return [];
	}


	/**
	 * Returns the function/method as closure.
	 *
	 * @return \Closure
	 */
	public function getClosure()
	{
		throw new Exception\RuntimeException('Cannot invoke invalid functions', Exception\RuntimeException::UNSUPPORTED, $this);
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
	 * Returns this pointer bound to closure.
	 *
	 * @return null
	 */
	public function getClosureThis()
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
		return FALSE;
	}


	/**
	 * Returns the string representation of the reflection object.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return sprintf(
			"%sFunction [ <user> function %s%s ] {\n  @@ %s %d - %d\n}\n",
			$this->getDocComment() ? $this->getDocComment() . "\n" : '',
			$this->returnsReference() ? '&' : '',
			$this->getName(),
			$this->getFileName(),
			$this->getStartLine(),
			$this->getEndLine()
		);
	}


	/**
	 * Magic __get method.
	 *
	 * @param string $key Variable name
	 * @return mixed
	 */
	final public function __get($key)
	{
		return ReflectionBase::get($this, $key);
	}


	/**
	 * Magic __isset method.
	 *
	 * @param string $key Variable name
	 * @return bool
	 */
	final public function __isset($key)
	{
		return ReflectionBase::exists($this, $key);
	}

}
