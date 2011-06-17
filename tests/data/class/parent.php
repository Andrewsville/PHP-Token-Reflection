<?php

class TokenReflection_Test_ClassGrandGrandParent extends ReflectionClass
{
}

class TokenReflection_Test_ClassGrandParent extends TokenReflection_Test_ClassGrandGrandParent
{
}

class TokenReflection_Test_ClassParent extends TokenReflection_Test_ClassGrandParent
{
}
