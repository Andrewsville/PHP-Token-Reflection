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
	 * Returns an array of all class reflections.
	 *
	 * @return array
	 */
	public function getClasses();

	/**
	 * Returns an array of all class names (FQN).
	 *
	 * @return array
	 */
	public function getClassNames();

	/**
	 * Returns an array of all class names (UQN).
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
	 * Returns all function reflections.
	 *
	 * @return array
	 */
	public function getFunctions();

	/**
	 * Returns all function names (FQN).
	 *
	 * @return array
	 */
	public function getFunctionNames();

	/**
	 * Returns all function names (UQN).
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
	 * Returns all constant reflections.
	 *
	 * @return array
	 */
	public function getConstants();

	/**
	 * Returns all constant names (FQN).
	 *
	 * @return array
	 */
	public function getConstantNames();

	/**
	 * Returns all constant names (UQN).
	 *
	 * @return array
	 */
	public function getConstantShortNames();
}
