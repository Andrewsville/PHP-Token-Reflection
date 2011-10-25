<?php

namespace
{
	const CONST_TRAIT = __TRAIT__;

	function constantMagic54($trait = __TRAIT__)
	{
		static $trait = __TRAIT__;
	}

	trait TokenReflection_Test_ConstantMagic54Trait
	{

		public $t_trait = __TRAIT__;

		public static $t_strait = __TRAIT__;

		public function t_foo($trait = __TRAIT__)
		{
			static $trait = __TRAIT__;
		}

	}

	class TokenReflection_Test_ConstantMagic54
	{
		const CONST_TRAIT = __TRAIT__;

		public $trait = __TRAIT__;

		public static $strait = __TRAIT__;

		public function foo($trait = __TRAIT__)
		{
			static $trait = __TRAIT__;
		}
	}

	class TokenReflection_Test_ConstantMagic54WithTrait extends TokenReflection_Test_ConstantMagic54
	{
		use TokenReflection_Test_ConstantMagic54Trait;

		public $trait2 = __TRAIT__;

		public static $strait2 = __TRAIT__;

		public function bar($trait = __TRAIT__)
		{
			static $trait = __TRAIT__;
		}

	}
}

namespace ns
{
	const CONST_TRAIT = __TRAIT__;

	function constantMagic54($trait = __TRAIT__)
	{
		static $trait = __TRAIT__;
	}

	trait TokenReflection_Test_ConstantMagic54Trait
	{

		public $t_trait = __TRAIT__;

		public static $t_strait = __TRAIT__;

		public function t_foo($trait = __TRAIT__)
		{
			static $trait = __TRAIT__;
		}

	}

	class TokenReflection_Test_ConstantMagic54
	{
		const CONST_TRAIT = __TRAIT__;

		public $trait = __TRAIT__;

		public static $strait = __TRAIT__;

		public function foo($trait = __TRAIT__)
		{
			static $trait = __TRAIT__;
		}
	}

	class TokenReflection_Test_ConstantMagic54WithTrait extends TokenReflection_Test_ConstantMagic54
	{
		use TokenReflection_Test_ConstantMagic54Trait;

		public $trait2 = __TRAIT__;

		public static $strait2 = __TRAIT__;

		public function bar($trait = __TRAIT__)
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

	class TokenReflection_Test_ConstantMagic54WithTrait extends \TokenReflection_Test_ConstantMagic54WithTrait
	{

	}
}

namespace ns3
{
	class TokenReflection_Test_ConstantMagic54 extends \ns\TokenReflection_Test_ConstantMagic54
	{

	}

	class TokenReflection_Test_ConstantMagic54WithTrait extends \ns\TokenReflection_Test_ConstantMagic54WithTrait
	{

	}
}
