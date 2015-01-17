<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection\Invalid;

use ApiGen\TokenReflection\ReflectionConstantInterface;
use ApiGen\TokenReflection\Broker\Broker;


class ReflectionConstant extends ReflectionElement implements ReflectionConstantInterface
{

	/**
	 * @param string $name
	 * @param string $fileName
	 * @param Broker $broker
	 */
	public function __construct($name, $fileName, Broker $broker)
	{
		$this->name = $name;
		$this->broker = $broker;
		$this->fileName = $fileName;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDeclaringClass()
	{
		return NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDeclaringClassName()
	{
		return NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getValue()
	{
		return NULL;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getValueDefinition()
	{
		return NULL;
	}


	/**
	 * Returns an element pretty (docblock compatible) name.
	 *
	 * @return string
	 */
	public function getPrettyName()
	{
		return $this->name;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getNamespaceAliases()
	{
		return [];
	}

}
