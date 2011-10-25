<?php
/**
 * PHP Token Reflection
 *
 * Version 1.0.0 RC 1
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this library in the file LICENSE.
 *
 * @author Ondřej Nešpor
 * @author Jaroslav Hanslík
 */

namespace TokenReflection;

require_once __DIR__ . '/../bootstrap.php';

/**
 * Constant test.
 *
 * @author Jaroslav Hanslík
 * @author Ondřej Nešpor
 */
class ReflectionConstantTest extends Test
{
	/**
	 * Element type.
	 *
	 * @var string
	 */
	protected $type = 'constant';

	/**
	 * Tests getting of start and end line.
	 */
	public function testLines()
	{
		$token = $this->getConstantTokenReflection('lines');

		$this->assertSame(5, $token->getStartLine());
		$this->assertSame(5, $token->getEndLine());
	}

	/**
	 * Tests getting of documentation comment.
	 */
	public function testComment()
	{
		$token = $this->getConstantTokenReflection('docComment');
		$this->assertSame("/**\n\t * This is a constant.\n\t */", $token->getDocComment());

		$token = $this->getConstantTokenReflection('noComment');
		$this->assertFalse($token->getDocComment());
	}

	/**
	 * Tests different types of constant value.
	 */
	public function testTypes()
	{
		$constants = array('string' => 'string', 'integer' => 1, 'integerNegative' => -1, 'float' => 1.1, 'floatNegative' => -1.1, 'boolean' => true, 'null' => null, 'constant' => E_NOTICE);
		foreach ($constants as $type => $value) {
			$test = 'type' . ucfirst($type);
			$token = $this->getConstantTokenReflection($test);
			$this->assertSame($this->getClassInternalReflection($test)->getConstant($this->getConstantName($test)), $token->getValue());
			$this->assertSame($value, $token->getValue());
		}
	}

	/**
	 * Tests if constant is defined in namespace or in class.
	 */
	public function testInNamespace()
	{
		$this->getBroker()->processFile($this->getFilePath('inNamespace'));
		$token = $this->getBroker()->getConstant('TokenReflection\Test\CONSTANT_IN_NAMESPACE');

		$this->assertInstanceOf('TokenReflection\ReflectionConstant', $token);
		$this->assertSame('constant-in-namespace', $token->getValue());

		$this->assertTrue($token->inNamespace());
		$this->assertSame('TokenReflection\\Test\\CONSTANT_IN_NAMESPACE', $token->getName());
		$this->assertSame('CONSTANT_IN_NAMESPACE', $token->getShortName());

		$this->assertNull($token->getDeclaringClassName());
		$this->assertNull($token->getDeclaringClass());

		$token = $this->getConstantTokenReflection('noNamespace');

		$this->assertFalse($token->inNamespace());
		$this->assertSame('NO_NAMESPACE', $token->getName());
		$this->assertSame('NO_NAMESPACE', $token->getShortName());

		$this->assertSame('TokenReflection_Test_ConstantNoNamespace', $token->getDeclaringClassName());
		$this->assertSame('TokenReflection_Test_ConstantNoNamespace', $token->getDeclaringClass()->getName());
		$this->assertInstanceOf('TokenReflection\ReflectionClass', $token->getDeclaringClass());
	}

	/**
	 * Tests export.
	 */
	public function testToString()
	{
		$tests = array(
			'noNamespace' => "Constant [ string NO_NAMESPACE ] { no-namespace }\n",
			'typeString' => "Constant [ string TYPE_STRING ] { string }\n",
			'typeInteger' => "Constant [ integer TYPE_INTEGER ] { 1 }\n",
			'typeIntegerNegative' => "Constant [ integer TYPE_INTEGER_NEGATIVE ] { -1 }\n",
			'typeFloat' => "Constant [ double TYPE_FLOAT ] { 1.1 }\n",
			'typeFloatNegative' => "Constant [ double TYPE_FLOAT_NEGATIVE ] { -1.1 }\n",
			'typeBoolean' => "Constant [ boolean TYPE_BOOLEAN ] { 1 }\n",
			'typeNull' => "Constant [ null TYPE_NULL ] {  }\n"
		);
		foreach ($tests as $test => $expected) {
			$this->assertSame($expected, $this->getConstantTokenReflection($test)->__toString());
			$this->assertSame($expected, ReflectionConstant::export($this->getBroker(), $this->getClassName($test), $this->getConstantName($test), true));

			// Test loading from a string
			$this->assertSame($expected, $this->getConstantTokenReflection($test, true)->__toString());
		}

		$this->assertSame("Constant [ integer E_NOTICE ] { 8 }\n", ReflectionConstant::export($this->getBroker(), null, 'E_NOTICE', true));
	}

	/**
	 * Tests magic constants.
	 */
	public function testMagicConstants()
	{
		$broker = new Broker(new Broker\Backend\Memory());
		$broker->process($this->getFilePath('magic'));

		require_once ($this->getFilePath('magic'));

		$internal_constants = get_defined_constants(true);
		$internal_constants = $internal_constants['user'];

		$token_constants = $broker->getConstants();
		$this->assertSame(14, count($token_constants));

		foreach ($token_constants as $name => $reflection) {
			$this->assertTrue(isset($internal_constants[$name]));
			$this->assertSame($internal_constants[$name], $reflection->getValue(), $name);
		}

		$token_functions = $broker->getFunctions();
		$this->assertSame(2, count($token_functions));

		foreach ($token_functions as $name => $token_function) {
			$this->assertTrue(function_exists($name));

			$function = new \ReflectionFunction($name);

			// Parameters
			$this->assertGreaterThan(0, $function->getNumberOfParameters(), sprintf('%s()', $name));
			$this->assertSame($function->getNumberOfParameters(), count($function->getParameters()), sprintf('%s()', $name));

			foreach ($function->getParameters() as $parameter) {
				$parameter_name = $parameter->getName();
				$token_parameter = $token_function->getParameter($parameter->getPosition());

				$this->assertTrue($parameter->isDefaultValueAvailable(), sprintf('%s(%s)', $name, $parameter_name));
				$this->assertSame($parameter->isDefaultValueAvailable(), $token_parameter->isDefaultValueAvailable(), sprintf('%s(%s)', $name, $parameter_name));

				$this->assertSame($parameter->getDefaultValue(), $token_parameter->getDefaultValue(), sprintf('%s(%s)', $name, $parameter_name));
			}

			// Static variables
			$internal_variables = $function->getStaticVariables();
			$this->assertGreaterThan(0, count($internal_variables), sprintf('%s()', $name));

			$token_variables = $token_function->getStaticVariables();
			$this->assertSame(count($internal_variables), count($token_variables), sprintf('%s()', $name));

			foreach ($internal_variables as $variable_name => $variable_value) {
				$this->assertTrue(isset($token_variables[$variable_name]), sprintf('%s()::%s', $name, $variable_name));
				$this->assertSame($variable_value, $token_variables[$variable_name], sprintf('%s()::%s', $name, $variable_name));
			}
		}

		$classes = array(
			'TokenReflection_Test_ConstantMagic',
			'ns\\TokenReflection_Test_ConstantMagic',
			'ns2\\TokenReflection_Test_ConstantMagic',
			'ns3\\TokenReflection_Test_ConstantMagic'
		);
		foreach ($classes as $class) {
			$this->assertTrue(class_exists($class, false), $class);

			$token = $broker->getClass($class);
			$internal = new \ReflectionClass($class);
			$instance = new $class();

			// Constants
			$this->assertSame(7, count($internal->getConstants()));
			$this->assertSame(count($internal->getConstants()), count($token->getConstantReflections()), $class);

			foreach ($internal->getConstants() as $name => $value) {
				$this->assertTrue($token->hasConstant($name), sprintf('%s::%s', $class, $name));
				$this->assertSame($value, $token->getConstantReflection($name)->getValue(), sprintf('%s::%s', $class, $name));
				$this->assertSame($value, $token->getConstant($name), sprintf('%s::%s', $class, $name));
			}

			// Properties
			$this->assertSame(14, count($internal->getProperties()));
			$this->assertSame(count($internal->getProperties()), count($token->getProperties()), $class);

			foreach ($internal->getProperties() as $reflection) {
				$name = $reflection->getName();

				$this->assertTrue($token->hasProperty($name), sprintf('%s::$%s', $class, $name));
				$this->assertSame($reflection->isStatic(), $token->getProperty($name)->isStatic());

				if ($reflection->isStatic()) {
					$this->assertSame($internal->getStaticPropertyValue($name), $token->getStaticPropertyValue($name), sprintf('%s::$%s', $class, $name));
				} else {
					$this->assertSame($reflection->getValue($instance), $token->getProperty($name)->getValue($instance), sprintf('%s::$%s', $class, $name));
					$this->assertSame($reflection->getValue($instance), $token->getProperty($name)->getDefaultValue(), sprintf('%s::$%s', $class, $name));
				}
			}

			// Methods
			$this->assertGreaterThanOrEqual(1, count($internal->getMethods()));
			$this->assertSame(count($internal->getMethods()), count($token->getMethods()), $class);

			foreach ($internal->getMethods() as $method) {
				$name = $method->getName();

				$this->assertTrue($token->hasMethod($name), sprintf('%s::%s()', $class, $name));

				$token_method = $token->getMethod($name);

				// Parameters
				$this->assertGreaterThan(0, $method->getNumberOfParameters(), sprintf('%s::%s()', $class, $name));
				$this->assertSame($method->getNumberOfParameters(), count($method->getParameters()), sprintf('%s::%s()', $class, $name));

				foreach ($method->getParameters() as $parameter) {
					$parameter_name = $parameter->getName();
					$token_parameter = $token_method->getParameter($parameter->getPosition());

					$this->assertTrue($parameter->isDefaultValueAvailable(), sprintf('%s::%s(%s)', $class, $name, $parameter_name));
					$this->assertSame($parameter->isDefaultValueAvailable(), $token_parameter->isDefaultValueAvailable(), sprintf('%s::%s(%s)', $class, $name, $parameter_name));

					$this->assertSame($parameter->getDefaultValue(), $token_parameter->getDefaultValue(), sprintf('%s::%s(%s)', $class, $name, $parameter_name));
				}

				// Static variables
				$internal_variables = $method->getStaticVariables();
				$this->assertGreaterThan(0, count($internal_variables), sprintf('%s::%s()', $class, $name));

				$token_variables = $token_method->getStaticVariables();
				$this->assertSame(count($internal_variables), count($token_variables), sprintf('%s::%s()', $class, $name));

				foreach ($internal_variables as $variable_name => $variable_value) {
					$this->assertTrue(isset($token_variables[$variable_name]), sprintf('%s::%s()::%s', $class, $name, $variable_name));
					$this->assertSame($variable_value, $token_variables[$variable_name], sprintf('%s::%s()::%s', $class, $name, $variable_name));
				}
			}
		}
	}

	/**
	 * Tests the __TRAIT__ magic constant.
	 *
	 * For PHP >= 5.4 only.
	 */
	public function testMagicConstants54()
	{
		if (PHP_VERSION_ID < 50400) {
			$this->markTestSkipped();
		}

		$broker = new Broker(new Broker\Backend\Memory());
		$broker->process($this->getFilePath('magic54'));

		require_once ($this->getFilePath('magic54'));

		$internal_constants = get_defined_constants(true);
		$internal_constants = $internal_constants['user'];

		$token_constants = $broker->getConstants();
		$this->assertSame(2, count($token_constants));

		foreach ($token_constants as $name => $reflection) {
			$this->assertTrue(isset($internal_constants[$name]));
			$this->assertSame($internal_constants[$name], $reflection->getValue(), $name);
		}

		$token_functions = $broker->getFunctions();
		$this->assertSame(2, count($token_functions));

		foreach ($token_functions as $name => $token_function) {
			$this->assertTrue(function_exists($name));

			$function = new \ReflectionFunction($name);

			// Parameters
			$this->assertGreaterThan(0, $function->getNumberOfParameters(), sprintf('%s()', $name));
			$this->assertSame($function->getNumberOfParameters(), count($function->getParameters()), sprintf('%s()', $name));

			foreach ($function->getParameters() as $parameter) {
				$parameter_name = $parameter->getName();
				$token_parameter = $token_function->getParameter($parameter->getPosition());

				$this->assertTrue($parameter->isDefaultValueAvailable(), sprintf('%s(%s)', $name, $parameter_name));
				$this->assertSame($parameter->isDefaultValueAvailable(), $token_parameter->isDefaultValueAvailable(), sprintf('%s(%s)', $name, $parameter_name));

				$this->assertSame($parameter->getDefaultValue(), $token_parameter->getDefaultValue(), sprintf('%s(%s)', $name, $parameter_name));
			}

			// Static variables
			$internal_variables = $function->getStaticVariables();
			$this->assertGreaterThan(0, count($internal_variables), sprintf('%s()', $name));

			$token_variables = $token_function->getStaticVariables();
			$this->assertSame(count($internal_variables), count($token_variables), sprintf('%s()', $name));

			foreach ($internal_variables as $variable_name => $variable_value) {
				$this->assertTrue(isset($token_variables[$variable_name]), sprintf('%s()::%s', $name, $variable_name));
				$this->assertSame($variable_value, $token_variables[$variable_name], sprintf('%s()::%s', $name, $variable_name));
			}
		}

		$classes = array(
			'TokenReflection_Test_ConstantMagic54Trait',
			'TokenReflection_Test_ConstantMagic54',
			'TokenReflection_Test_ConstantMagic54WithTrait',
			'ns\\TokenReflection_Test_ConstantMagic54Trait',
			'ns\\TokenReflection_Test_ConstantMagic54',
			'ns\\TokenReflection_Test_ConstantMagic54WithTrait',
			'ns2\\TokenReflection_Test_ConstantMagic54',
			'ns2\\TokenReflection_Test_ConstantMagic54WithTrait',
			'ns3\\TokenReflection_Test_ConstantMagic54',
			'ns3\\TokenReflection_Test_ConstantMagic54WithTrait'
		);
		foreach ($classes as $class) {
			$token = $broker->getClass($class);
			$internal = new \ReflectionClass($class);

			$this->assertSame($internal->isTrait(), $token->isTrait());

			if (!$internal->isTrait()) {
				$instance = new $class();
			}

			// Constants
			if ($internal->isTrait()) {
				$this->assertSame(0, count($internal->getConstants()));
			} else {
				$this->assertSame(1, count($internal->getConstants()));
			}

			$this->assertSame(count($internal->getConstants()), count($token->getConstantReflections()), $class);

			foreach ($internal->getConstants() as $name => $value) {
				$this->assertTrue($token->hasConstant($name), sprintf('%s::%s', $class, $name));
				$this->assertSame($value, $token->getConstantReflection($name)->getValue(), sprintf('%s::%s', $class, $name));
				$this->assertSame($value, $token->getConstant($name), sprintf('%s::%s', $class, $name));
			}

			// Properties
			$this->assertGreaterThan(0, count($internal->getProperties()));
			$this->assertSame(count($internal->getProperties()), count($token->getProperties()), $class);

			foreach ($internal->getProperties() as $reflection) {
				$name = $reflection->getName();

				$this->assertTrue($token->hasProperty($name), sprintf('%s::$%s', $class, $name));
				$this->assertSame($reflection->isStatic(), $token->getProperty($name)->isStatic());

				if ($reflection->isStatic()) {
					$this->assertSame($internal->getStaticPropertyValue($name), $token->getStaticPropertyValue($name), sprintf('%s::$%s', $class, $name));
				} elseif (!$internal->isTrait()) {
					$this->assertSame($reflection->getValue($instance), $token->getProperty($name)->getValue($instance), sprintf('%s::$%s', $class, $name));
					$this->assertSame($reflection->getValue($instance), $token->getProperty($name)->getDefaultValue(), sprintf('%s::$%s', $class, $name));
				}
			}

			// Methods
			$this->assertGreaterThanOrEqual(1, count($internal->getMethods()));
			$this->assertSame(count($internal->getMethods()), count($token->getMethods()), $class);

			foreach ($internal->getMethods() as $method) {
				$name = $method->getName();

				$this->assertTrue($token->hasMethod($name), sprintf('%s::%s()', $class, $name));

				$token_method = $token->getMethod($name);

				// Parameters
				$this->assertGreaterThan(0, $method->getNumberOfParameters(), sprintf('%s::%s()', $class, $name));
				$this->assertSame($method->getNumberOfParameters(), count($method->getParameters()), sprintf('%s::%s()', $class, $name));

				foreach ($method->getParameters() as $parameter) {
					$parameter_name = $parameter->getName();
					$token_parameter = $token_method->getParameter($parameter->getPosition());

					$this->assertTrue($parameter->isDefaultValueAvailable(), sprintf('%s::%s(%s)', $class, $name, $parameter_name));
					$this->assertSame($parameter->isDefaultValueAvailable(), $token_parameter->isDefaultValueAvailable(), sprintf('%s::%s(%s)', $class, $name, $parameter_name));

					$this->assertSame($parameter->getDefaultValue(), $token_parameter->getDefaultValue(), sprintf('%s::%s(%s)', $class, $name, $parameter_name));
				}

				// Static variables
				$internal_variables = $method->getStaticVariables();

				$token_variables = $token_method->getStaticVariables();
				$this->assertSame(count($internal_variables), count($token_variables), sprintf('%s::%s()', $class, $name));

				foreach ($internal_variables as $variable_name => $variable_value) {
					$this->assertTrue(isset($token_variables[$variable_name]), sprintf('%s::%s()::%s', $class, $name, $variable_name));
					$this->assertSame($variable_value, $token_variables[$variable_name], sprintf('%s::%s()::%s', $class, $name, $variable_name));
				}
			}
		}
	}
}
