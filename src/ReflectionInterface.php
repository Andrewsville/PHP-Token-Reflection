<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\TokenReflection;

use ApiGen\TokenReflection\Broker\Broker;


interface ReflectionInterface
{

	/**
	 * Returns the name (FQN).
	 *
	 * @return string
	 */
	function getName();


	/**
	 * Returns an element pretty (docblock compatible) name.
	 *
	 * @return string
	 */
	function getPrettyName();


	/**
	 * Returns if the reflection object is internal.
	 *
	 * @return bool
	 */
	function isInternal();


	/**
	 * Returns if the reflection object is user defined.
	 *
	 * @return bool
	 */
	function isUserDefined();


	/**
	 * Returns if the current reflection comes from a tokenized source.
	 *
	 * @return bool
	 */
	function isTokenized();


	/**
	 * Returns the reflection broker used by this reflection object.
	 *
	 * @return Broker
	 */
	function getBroker();


	/**
	 * Magic __get method.
	 *
	 * @param string $key Variable name
	 * @return mixed
	 */
	function __get($key);


	/**
	 * Magic __isset method.
	 *
	 * @param string $key Variable name
	 * @return bool
	 */
	function __isset($key);

}
