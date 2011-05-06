<?php

class TokenReflection_Test_MethodShadowGrandParent
{
	private function shadow()
	{
	}
}

class TokenReflection_Test_MethodShadowParent extends TokenReflection_Test_MethodShadowGrandParent
{
	private function shadow()
	{
	}
}

class TokenReflection_Test_MethodShadow extends TokenReflection_Test_MethodShadowParent
{
	private function shadow()
	{
	}
}