<?php

class TokenReflection_Test_PropertyDeclaringClassParent
{

	public $parent = TRUE;

	protected $parentOverlay = TRUE;

}


class TokenReflection_Test_PropertyDeclaringClass extends TokenReflection_Test_PropertyDeclaringClassParent
{

	protected $parentOverlay = FALSE;

	public $child = TRUE;

}
