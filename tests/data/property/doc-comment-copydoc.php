<?php

class TokenReflection_Test_PropertyDocCommentCopydoc
{
	/**
	 * This is a property.
	 */
	public $property;

	/**
	 * @copydoc property
	 */
	public $property2;

	/**
	 * @copydoc $property
	 */
	public $property3;

	/**
	 * @copydoc TokenReflection_Test_PropertyDocCommentCopydoc::$property
	 */
	public $property4;

	/**
	 * @copydoc nonexistentProperty
	 */
	public $property5;

	/**
	 * @copydoc $nonexistentProperty
	 */
	public $property6;

	/**
	 * @copydoc nonexistentClass::$nonexistentProperty
	 */
	public $property7;
}
