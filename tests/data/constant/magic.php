<?php

namespace
{
	const CONST_NAMESPACE = __NAMESPACE__;
	const CONST_CLASS = __CLASS__;
	const CONST_FILE = __FILE__;
	const CONST_DIR = __DIR__;
	const CONST_LINE = __LINE__;
	const CONST_FUNCTION = __FUNCTION__;
	const CONST_METHOD = __METHOD__;

	function constantMagic($namespace = __NAMESPACE__, $class = __CLASS__, $file = __FILE__, $dir = __DIR__, $line = __LINE__, $function = __FUNCTION__, $method = __METHOD__, $all = array(__NAMESPACE__, __CLASS__, __FILE__, __DIR__, __LINE__, __FUNCTION__, __METHOD__))
	{
		static $namespace = __NAMESPACE__;
		static $class = __CLASS__;
		static $file = __FILE__;
		static $dir = __DIR__;
		static $line = __LINE__;
		static $function = __FUNCTION__;
		static $method = __METHOD__;
		static $all = array(__NAMESPACE__, __CLASS__, __FILE__, __DIR__, __LINE__, __FUNCTION__, __METHOD__, '__NAMESPACE__, __CLASS__, __FILE__, __DIR__, __LINE__, __FUNCTION__, __METHOD__');
	}

	class TokenReflection_Test_ConstantMagic
	{
		const CONST_NAMESPACE = __NAMESPACE__;
		const CONST_CLASS = __CLASS__;
		const CONST_FILE = __FILE__;
		const CONST_DIR = __DIR__;
		const CONST_LINE = __LINE__;
		const CONST_FUNCTION = __FUNCTION__;
		const CONST_METHOD = __METHOD__;

		public $namespace = __NAMESPACE__;
		public $class = __CLASS__;
		public $file = __FILE__;
		public $dir = __DIR__;
		public $line = __LINE__;
		public $function = __FUNCTION__;
		public $method = __METHOD__;

		public static $s_namespace = __NAMESPACE__;
		public static $s_class = __CLASS__;
		public static $s_file = __FILE__;
		public static $s_dir = __DIR__;
		public static $s_line = __LINE__;
		public static $s_function = __FUNCTION__;
		public static $s_method = __METHOD__;

		public function foo($namespace = __NAMESPACE__, $class = __CLASS__, $file = __FILE__, $dir = __DIR__, $line = __LINE__, $function = __FUNCTION__, $method = __METHOD__, $all = array(__NAMESPACE__, __CLASS__, __FILE__, __DIR__, __LINE__, __FUNCTION__, __METHOD__))
		{
			static $namespace = __NAMESPACE__;
			static $class = __CLASS__;
			static $file = __FILE__;
			static $dir = __DIR__;
			static $line = __LINE__;
			static $function = __FUNCTION__;
			static $method = __METHOD__;
			static $all = array(__NAMESPACE__, __CLASS__, __FILE__, __DIR__, __LINE__, __FUNCTION__, __METHOD__);
		}
	}
}

namespace ns
{
	const CONST_NAMESPACE = __NAMESPACE__;
	const CONST_CLASS = __CLASS__;
	const CONST_FILE = __FILE__;
	const CONST_DIR = __DIR__;
	const CONST_LINE = __LINE__;
	const CONST_FUNCTION = __FUNCTION__;
	const CONST_METHOD = __METHOD__;

	function constantMagic($namespace = __NAMESPACE__, $class = __CLASS__, $file = __FILE__, $dir = __DIR__, $line = __LINE__, $function = __FUNCTION__, $method = __METHOD__, $all = array(__NAMESPACE__, __CLASS__, __FILE__, __DIR__, __LINE__, __FUNCTION__, __METHOD__))
	{
		static $namespace = __NAMESPACE__;
		static $class = __CLASS__;
		static $file = __FILE__;
		static $dir = __DIR__;
		static $line = __LINE__;
		static $function = __FUNCTION__;
		static $method = __METHOD__;
		static $all = array(__NAMESPACE__, __CLASS__, __FILE__, __DIR__, __LINE__, __FUNCTION__, __METHOD__);
	}

	class TokenReflection_Test_ConstantMagic
	{
		const CONST_NAMESPACE = __NAMESPACE__;
		const CONST_CLASS = __CLASS__;
		const CONST_FILE = __FILE__;
		const CONST_DIR = __DIR__;
		const CONST_LINE = __LINE__;
		const CONST_FUNCTION = __FUNCTION__;
		const CONST_METHOD = __METHOD__;

		public $namespace = __NAMESPACE__;
		public $class = __CLASS__;
		public $file = __FILE__;
		public $dir = __DIR__;
		public $line = __LINE__;
		public $function = __FUNCTION__;
		public $method = __METHOD__;

		public static $s_namespace = __NAMESPACE__;
		public static $s_class = __CLASS__;
		public static $s_file = __FILE__;
		public static $s_dir = __DIR__;
		public static $s_line = __LINE__;
		public static $s_function = __FUNCTION__;
		public static $s_method = __METHOD__;

		public function foo(
			$namespace = __NAMESPACE__,
			$class = __CLASS__,
			$file = __FILE__,
			$dir = __DIR__,
			$line = __LINE__,
			$function = __FUNCTION__,
			$method = __METHOD__,
			$all = array(
				__NAMESPACE__,
				__CLASS__,
				__FILE__,
				__DIR__,
				__LINE__,
				__FUNCTION__,
				__METHOD__
			)
		)
		{
			static $namespace = __NAMESPACE__;
			static $class = __CLASS__;
			static $file = __FILE__;
			static $dir = __DIR__;
			static $line = __LINE__;
			static $function = __FUNCTION__;
			static $method = __METHOD__;
			static $all = array(__NAMESPACE__, __CLASS__, __FILE__, __DIR__, __LINE__, __FUNCTION__, __METHOD__);
		}
	}
}

namespace ns2
{
	class TokenReflection_Test_ConstantMagic extends \TokenReflection_Test_ConstantMagic
	{
		public function bar($namespace = __NAMESPACE__, $class = __CLASS__, $file = __FILE__, $dir = __DIR__, $line = __LINE__, $function = __FUNCTION__, $method = __METHOD__, $all = array(__NAMESPACE__, __CLASS__, __FILE__, __DIR__, __LINE__, __FUNCTION__, __METHOD__))
		{
			static $namespace = __NAMESPACE__;
			static $class = __CLASS__;
			static $file = __FILE__;
			static $dir = __DIR__;
			static $line = __LINE__;
			static $function = __FUNCTION__;
			static $method = __METHOD__;
			static $all = array(__NAMESPACE__, __CLASS__, __FILE__, __DIR__, __LINE__, __FUNCTION__, __METHOD__);
		}
	}
}

namespace ns3
{
	class TokenReflection_Test_ConstantMagic extends \ns\TokenReflection_Test_ConstantMagic
	{
		public function bar($namespace = __NAMESPACE__, $class = __CLASS__, $file = __FILE__, $dir = __DIR__, $line = __LINE__, $function = __FUNCTION__, $method = __METHOD__, $all = array(__NAMESPACE__, __CLASS__, __FILE__, __DIR__, __LINE__, __FUNCTION__, __METHOD__))
		{
			static $namespace = __NAMESPACE__;
			static $class = __CLASS__;
			static $file = __FILE__;
			static $dir = __DIR__;
			static $line = __LINE__;
			static $function = __FUNCTION__;
			static $method = __METHOD__;
			static $all = array(__NAMESPACE__, __CLASS__, __FILE__, __DIR__, __LINE__, __FUNCTION__, __METHOD__);
		}
	}
}
