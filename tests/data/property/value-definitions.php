<?php

class TokenReflection_Test_PropertyValueDefinitions
{

	private $property1 = TRUE;

	private $property2 = TRUE;

	private $property3 = TRUE/** foo */
	;

	private $property4 = /** foo */
		TRUE;

	private $property5 /** foo */ = TRUE;

	private $property6 /** foo */ = TRUE/** foo */
	;

	private $property7 /** foo */ = /** foo */
		TRUE/** foo */
	;

	private /** foo */
		$property8 = TRUE;

}
