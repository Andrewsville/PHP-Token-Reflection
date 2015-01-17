<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Invalid;

use ApiGen\TokenReflection\Broker\Broker;
use ApiGen\TokenReflection\Exception;
use ApiGen\TokenReflection\Exception\RuntimeException;
use ApiGen\TokenReflection\ReflectionFunctionInterface;


class ReflectionFunction extends ReflectionElement implements ReflectionFunctionInterface
{

	/**
	 * @param string $name
	 * @param string $fileName
	 * @param Broker $broker
	 */
	public function __construct($name, $fileName, Broker $broker)
	{
		$this->name = ltrim($name, '\\');
		$this->broker = $broker;
		$this->fileName = $fileName;
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
	public function returnsReference()
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getParameter($parameter)
	{
		throw new RuntimeException(
			sprintf('There is no parameter with name or position "%s".', $parameter),
			RuntimeException::DOES_NOT_EXIST, $this
		);
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
	 * {@inheritdoc}
	 */
	public function isDisabled()
	{
		return FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getNamespaceAliases()
	{
		return [];
	}


	/**
	 * {@inheritdoc}
	 */
	public function isVariadic()
	{
		return FALSE;
	}

}
