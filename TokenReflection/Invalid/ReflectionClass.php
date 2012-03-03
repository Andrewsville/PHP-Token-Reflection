<?php
/**
 * PHP Token Reflection
 *
 * Version 1.1
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this library in the file LICENSE.
 *
 * @author Ondřej Nešpor
 * @author Jaroslav Hanslík
 */

namespace TokenReflection\Invalid;

use TokenReflection\Dummy;

/**
 * Invalid class reflection.
 *
 * The reflected class is not unique.
 */
class ReflectionClass extends Dummy\ReflectionClass
{
	/**
	 * Returns if the class definition is complete.
	 *
	 * Invalid classes are always complete.
	 *
	 * @return boolean
	 */
	public function isComplete()
	{
		return true;
	}

	/**
	 * Returns if the class definition is valid.
	 *
	 * @return boolean
	 */
	public function isValid()
	{
		return false;
	}
}
