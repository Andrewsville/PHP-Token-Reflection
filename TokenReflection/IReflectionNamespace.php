<?php
/**
 * PHP Token Reflection
 *
 * Version 1.0 beta 2
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

/**
 * Common reflection namespace interface.
 */
interface IReflectionNamespace extends IReflection
{
	/**
	 * Return a class reflection.
	 *
	 * @param string $className Class name
	 * @return \TokenReflection\IReflectionClass
	 */
	public function getClass($className);

	/**
	 * Returns if the namespace contains a class of the given name.
	 *
	 * @param string $className Class name
	 * @return boolean
	 */
	public function hasClass($className);

	/**
	 * Returns class reflections.
	 *
	 * @return array
	 */
	public function getClasses();

	/**
	 * Returns class names (FQN).
	 *
	 * @return array
	 */
	public function getClassNames();

	/**
	 * Returns class unqualified names (UQN).
	 *
	 * @return array
	 */
	public function getClassShortNames();

	/**
	 * Returns a function reflection.
	 *
	 * @param string $functionName Function name
	 * @return \TokenReflection\IReflectionFunction
	 */
	public function getFunction($functionName);

	/**
	 * Returns function reflections.
	 *
	 * @return array
	 */
	public function getFunctions();

	/**
	 * Returns if the namespace contains a function of the given name.
	 *
	 * @param string $functionName Function name
	 * @return boolean
	 */
	public function hasFunction($functionName);

	/**
	 * Returns function names (FQN).
	 *
	 * @return array
	 */
	public function getFunctionNames();

	/**
	 * Returns function unqualified names (UQN).
	 *
	 * @return array
	 */
	public function getFunctionShortNames();

	/**
	 * Returns a constant reflection.
	 *
	 * @param string $constantName Constant name
	 * @return \TokenReflection\IReflectionConstant
	 */
	public function getConstant($constantName);

	/**
	 * Returns constant reflections.
	 *
	 * @return array
	 */
	public function getConstants();

	/**
	 * Returns if the namespace contains a constant of the given name.
	 *
	 * @param string $constantName Constant name
	 * @return boolean
	 */
	public function hasConstant($constantName);

	/**
	 * Returns constant names (FQN).
	 *
	 * @return array
	 */
	public function getConstantNames();

	/**
	 * Returns constant unqualified names (UQN).
	 *
	 * @return array
	 */
	public function getConstantShortNames();
}
