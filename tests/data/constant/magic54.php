<?php

namespace
{
	const CONST_TRAIT = __TRAIT__;

	class TokenReflection_Test_ConstantMagic54
	{
		const CONST_TRAIT = __TRAIT__;

		public $trait = __TRAIT__;

		public function foo($trait = __TRAIT__)
		{
			static $trait = __TRAIT__;
		}
	}
}

namespace ns
{
	const CONST_TRAIT = __TRAIT__;

	class TokenReflection_Test_ConstantMagic54
	{
		const CONST_TRAIT = __TRAIT__;

		public $trait = __TRAIT__;

		public function foo($trait = __TRAIT__)
		{
			static $trait = __TRAIT__;
		}
	}
}

namespace ns2
{
	class TokenReflection_Test_ConstantMagic54 extends \TokenReflection_Test_ConstantMagic54
	{

	}
}

namespace ns3
{
	class TokenReflection_Test_ConstantMagic54 extends \ns\TokenReflection_Test_ConstantMagic54
	{

	}
}
