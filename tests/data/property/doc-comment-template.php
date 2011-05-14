<?php

class TokenReflection_Test_PropertyDocCommentTemplate
{
	/**#@+
	 * Short description.
	 *
	 * Long description.
	 *
	 * @var string
	 */
	public $public1 = 'public1';

	public $public2 = 'public2';

	/**#@+
	 * Another short description.
	 *
	 * Another long description.
	 *
	 * @var array
	 */
	public $public3 = 'public3';

	/**
	 * Own short description.
	 */
	public $public4 = 'public4';

	/**#@-*/

	/**
	 * Another own short description.
	 *
	 * Own long description.
	 * @var integer
	 */
	public $public5 = 'public5';

	/**#@-*/
	public $public6 = 'public6';

	/**
	 * Outside of template.
	 *
	 * @var boolean
	 */
	public $public7 = 'public7';

}