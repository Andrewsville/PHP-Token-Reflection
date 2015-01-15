<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Invalid;

use ApiGen\TokenReflection\Behaviors\Annotations;
use ApiGen\TokenReflection\Broker\Broker;
use ApiGen\TokenReflection\Exception;
use ApiGen\TokenReflection\Exception\RuntimeException;
use ApiGen\TokenReflection\IReflectionFunction;
use ApiGen\TokenReflection\ReflectionBase;
use ApiGen\TokenReflection\ReflectionFile;


class ReflectionFunction extends ReflectionElement implements IReflectionFunction, Annotations
{

	/**
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
	 * @var Broker
	 */
	private $broker;


	/**
	 * @param string $name
	 * @param string $fileName Original definition file name
	 * @param Broker $broker
	 */
	public function __construct($name, $fileName, Broker $broker)
	{
		$this->name = ltrim($name, '\\');
		$this->broker = $broker;
		$this->fileName = $fileName;
	}


	/**
	 * {@inheritdoc}
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
	 * {@inheritdoc}
	 */
	public function getNamespaceName()
	{
		$pos = strrpos($this->name, '\\');
		return FALSE === $pos ? '' : substr($this->name, 0, $pos);
	}


	/**
	 * {@inheritdoc}
	 */
	public function inNamespace()
	{
		return FALSE !== strrpos($this->name, '\\');
	}


	/**
	 * {@inheritdoc}
	 */
	public function isInternal()
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isUserDefined()
	{
		return TRUE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isTokenized()
	{
		return TRUE;
	}


	/**
	 * {@inheritdoc}
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
	 * {@inheritdoc}
	 */
	public function getExtension()
	{
		return NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getExtensionName()
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getFileName()
	{
		return $this->fileName;
	}


	/**
	 * Returns a file reflection.
	 *
	 * @return ReflectionFile
	 * @throws RuntimeException If the file is not stored inside the broker
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
	 * {@inheritdoc}
	 */
	public function getStartLine()
	{
		return NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getEndLine()
	{
		return NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDocComment()
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function hasAnnotation($name)
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getAnnotation($name)
	{
		return NULL;
	}


	/**
	 * {@inheritdoc}
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
	 * {@inheritdoc}
	 */
	public function isDeprecated()
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function returnsReference()
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getParameter($parameter)
	{
		if (is_numeric($parameter)) {
			throw new RuntimeException(sprintf('There is no parameter at position "%d".', $parameter), RuntimeException::DOES_NOT_EXIST, $this);

		} else {
			throw new RuntimeException(sprintf('There is no parameter "%s".', $parameter), RuntimeException::DOES_NOT_EXIST, $this);
		}
	}


	/**
	 * {@inheritdoc}
	 */
	public function getParameters()
	{
		return [];
	}


	/**
	 * {@inheritdoc}
	 */
	public function getNumberOfParameters()
	{
		return 0;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getNumberOfRequiredParameters()
	{
		return 0;
	}


	/**
	 * {@inheritdoc}
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
		throw new RuntimeException('Cannot invoke invalid functions', RuntimeException::UNSUPPORTED, $this);
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
		throw new RuntimeException('Cannot invoke invalid functions', RuntimeException::UNSUPPORTED, $this);
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
	 * {@inheritdoc}
	 */
	public function isValid()
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isVariadic()
	{
		return FALSE;
	}


	/**
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
	 * {@inheritdoc}
	 */
	public function __get($key)
	{
		return ReflectionBase::get($this, $key);
	}


	/**
	 * {@inheritdoc}
	 */
	public function __isset($key)
	{
		return ReflectionBase::exists($this, $key);
	}

}
