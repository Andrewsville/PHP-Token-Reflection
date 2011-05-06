<?php

class TokenReflection_Test_PropertyDeclaringClassParent
{
	public $parent = true;
	protected $parentOverlay = true;
}

class TokenReflection_Test_PropertyDeclaringClass extends TokenReflection_Test_PropertyDeclaringClassParent
{
	protected $parentOverlay = false;
	public $child = true;
}