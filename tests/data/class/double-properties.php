<?php

class TokenReflection_Test_ClassDoublePropertiesParent
{

	protected static $protectedOne = 1, $protectedTwo = 0;

}


class TokenReflection_Test_ClassDoubleProperties extends TokenReflection_Test_ClassDoublePropertiesParent
{

	public $publicOne = TRUE, $publicTwo = FALSE;

	private $privateOne = 'something', $privateTwo = '';

}
