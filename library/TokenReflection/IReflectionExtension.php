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
 * Common reflection extension interface.
 */
interface IReflectionExtension extends IReflection
{
	/**
	 * Returns classes defined by this extension.
	 *
	 * @param string $name Class name
	 * @return \TokenReflection\IReflectionClass|null
	 */
	public function getClass($name);

	/**
	 * Returns classes defined by this extension.
	 *
	 * @return array
	 */
	public function getClasses();

	/**
	 * Returns class names defined by this extension.
	 *
	 * @return array
	 */
	public function getClassNames();

	/**
	 * Returns a constant reflection.
	 *
	 * @param string $name Constant name
	 * @return \TokenReflection\IReflectionConstant
	 */
	public function getConstantReflection($name);

	/**
	 * Returns reflections of defined constants.
	 *
	 * This method has this name just for consistence with the rest of reflection.
	 *
	 * @return array
	 * @see \TokenReflection\IReflectionExtension::getConstantReflections()
	 */
	public function getConstantReflections();

	/**
	 * Returns a constant value.
	 *
	 * @param string $name Constant name
	 * @return mixed|false
	 */
	public function getConstant($name);

	/**
	 * Returns values of defined constants.
	 *
	 * This method exists just for consistence with the rest of reflection.
	 *
	 * @return array
	 */
	public function getConstants();

	/**
	 * Returns a function reflection.
	 *
	 * @param string $name Function name
	 * @return \TokenReflection\IReflectionFunction
	 */
	public function getFunction($name);

	/**
	 * Returns functions defined by this extension.
	 *
	 * @return array
	 */
	public function getFunctions();

	/**
	 * Returns function names defined by this extension.
	 *
	 * @return array
	 */
	public function getFunctionNames();
}
