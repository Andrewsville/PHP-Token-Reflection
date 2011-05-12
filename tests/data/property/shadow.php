<?php

class TokenReflection_Test_PropertyShadowGrandParent
{
	private $shadow = false;
}

class TokenReflection_Test_PropertyShadowParent extends TokenReflection_Test_PropertyShadowGrandParent
{
	private $shadow = false;
}

class TokenReflection_Test_PropertyShadow extends TokenReflection_Test_PropertyShadowParent
{
	private $shadow = false;
}