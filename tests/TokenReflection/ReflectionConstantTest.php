<?php
/**
 * PHP Token Reflection
 *
 * Version 1.3.1
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
		$rfl = $this->getClassReflection('docComment');
		foreach (array_keys($rfl->internal->getConstants()) as $constant) {
			$this->assertTrue($rfl->token->hasConstant($constant), $constant);
			$this->assertFalse(false === $rfl->token->getConstantReflection($constant)->getDocComment(), $constant);
		}

		$token = $this->getConstantTokenReflection('noComment');
		$this->assertFalse($token->getDocComment());
	}

	/**
	 * Tests heredoc defined value.
	 */
	public function testHeredoc()
	{
		$rfl = $this->getClassReflection('heredoc');

		$this->assertSame($rfl->internal->getConstant('HEREDOC'), $rfl->token->getConstant('HEREDOC'));
		$this->assertSame('constant value', $rfl->token->getConstant('HEREDOC'));

		$this->assertSame($rfl->internal->getConstant('NOWDOC'), $rfl->token->getConstant('NOWDOC'));
		$this->assertSame('constant value', $rfl->token->getConstant('NOWDOC'));
	}

	/**
	 * Tests getting of copydoc documentation comment.
	 */
	public function testCommentCopydoc()
	{
		static $constants = array('DOC_COMMENT', 'DOC_COMMENT_COPY', 'DOC_COMMENT_COPY2');

		$broker = $this->getBroker();
		$broker->processFile($this->getFilePath('docCommentCopydoc'));

		$this->assertTrue($broker->hasClass('TokenReflection_Test_ConstantDocCommentCopydoc'));
		$reflection = $broker->getClass('TokenReflection_Test_ConstantDocCommentCopydoc');
		foreach ($constants as $constant) {
			$this->assertSame('This is a constant.', $reflection->getConstantReflection($constant)->getAnnotation(ReflectionAnnotation::SHORT_DESCRIPTION), $constant);
		}

		$this->assertSame('This is another constant.', $reflection->getConstantReflection('DOC_COMMENT_COPY_CLASS')->getAnnotation(ReflectionAnnotation::SHORT_DESCRIPTION));
		$this->assertSame(null, $reflection->getConstantReflection('DOC_COMMENT_COPY_NO')->getAnnotation(ReflectionAnnotation::SHORT_DESCRIPTION));

		static $topLevelConstants = array(
			'CONSTANT_DOC_COMMENT_COPYDOC' => 'Comment.',
			'CONSTANT_DOC_COMMENT_COPYDOC2' => 'Comment.',
			'CONSTANT_DOC_COMMENT_COPYDOC3' => null,
		);
		foreach ($topLevelConstants as $constantName => $shortDescription) {
			$this->assertTrue($broker->hasConstant($constantName), $constantName);
			$this->assertSame($shortDescription, $broker->getConstant($constantName)->getAnnotation(ReflectionAnnotation::SHORT_DESCRIPTION), $constantName);
		}
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
			$this->markTestSkipped('PHP >= 5.4 only');
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

	/**
	 * Tests the __TRAIT__ magic constant.
	 *
	 * For PHP < 5.4 only.
	 */
	public function testMagicConstants54using53()
	{
		if (PHP_VERSION_ID >= 50400) {
			$this->markTestSkipped('PHP < 5.4 only');
		}

		$broker = new Broker(new Broker\Backend\Memory());
		$broker->process($this->getFilePath('magic54'));

		$token_constants = $broker->getConstants();
		static $expected_constants = array('CONST_TRAIT' => '', 'ns\CONST_TRAIT' => '');

		$this->assertSame(count($expected_constants), count($token_constants));
		foreach ($token_constants as $name => $reflection) {
			$this->assertArrayHasKey($name, $expected_constants);
			$this->assertSame($expected_constants[$name], $reflection->getValue());
		}

		$token_functions = $broker->getFunctions();
		static $expected_functions = array(
			'constantMagic54' => array(
				array('trait' => ''),
				array('trait' => '')
			),
			'ns\\constantMagic54' => array(
				array('trait' => ''),
				array('trait' => '')
			)
		);

		$this->assertSame(count($expected_functions), count($token_functions));
		foreach ($token_functions as $name => $token_function) {
			$this->assertArrayHasKey($name, $expected_functions);

			// Parameters
			$this->assertSame(count($expected_functions[$name][0]), $token_function->getNumberOfParameters(), sprintf('%s()', $name));
			$this->assertSame($token_function->getNumberOfParameters(), count($token_function->getParameters()), sprintf('%s()', $name));

			foreach ($token_function->getParameters() as $parameter) {
				$parameter_name = $parameter->getName();

				$this->assertArrayHasKey($parameter_name, $expected_functions[$name][0]);
				$this->assertTrue($parameter->isDefaultValueAvailable(), sprintf('%s(%s)', $name, $parameter_name));
				$this->assertSame($expected_functions[$name][0][$parameter_name], $parameter->getDefaultValue(), sprintf('%s(%s)', $name, $parameter_name));
			}

			// Static variables
			$token_variables = $token_function->getStaticVariables();
			$this->assertSame(count($expected_functions[$name][1]), count($token_variables), sprintf('%s()', $name));

			foreach ($token_variables as $variable_name => $variable_value) {
				$this->assertArrayHasKey($variable_name, $expected_functions[$name][1]);
				$this->assertSame($expected_functions[$name][1][$variable_name], $variable_value);
			}
		}

		$token_classes = $broker->getClasses();
		static $expected_classes = array(
			'TokenReflection_Test_ConstantMagic54Trait' => array(
				true,
				array(),
				array('t_trait' => array(false, 'TokenReflection_Test_ConstantMagic54Trait'), 't_strait' => array(true, 'TokenReflection_Test_ConstantMagic54Trait')),
				array('t_foo' => array(array('trait' => 'TokenReflection_Test_ConstantMagic54Trait'), array('trait' => 'TokenReflection_Test_ConstantMagic54Trait')))
			),
			'TokenReflection_Test_ConstantMagic54' => array(
				false,
				array('CONST_TRAIT' => ''),
				array('trait' => array(false, ''), 'strait' => array(true, '')),
				array('foo' => array(array('trait' => ''), array('trait' => '')))
			),
			'TokenReflection_Test_ConstantMagic54WithTrait' => array(
				false,
				array('CONST_TRAIT' => ''),
				array('t_trait' => array(false, 'TokenReflection_Test_ConstantMagic54Trait'), 't_strait' => array(true, 'TokenReflection_Test_ConstantMagic54Trait'), 'trait' => array(false, ''), 'strait' => array(true, ''), 'trait2' => array(false, ''), 'strait2' => array(true, '')),
				array('foo' => array(array('trait' => ''), array('trait' => '')), 'bar' => array(array('trait' => ''), array('trait' => '')), 't_foo' => array(array('trait' => 'TokenReflection_Test_ConstantMagic54Trait'), array('trait' => 'TokenReflection_Test_ConstantMagic54Trait'))),
			),
			'ns\\TokenReflection_Test_ConstantMagic54Trait' => array(
				true,
				array(),
				array('t_trait' => array(false, 'ns\\TokenReflection_Test_ConstantMagic54Trait'), 't_strait' => array(true, 'ns\\TokenReflection_Test_ConstantMagic54Trait')),
				array('t_foo' => array(array('trait' => 'ns\\TokenReflection_Test_ConstantMagic54Trait'), array('trait' => 'ns\\TokenReflection_Test_ConstantMagic54Trait')))
			),
			'ns\\TokenReflection_Test_ConstantMagic54' => array(
				false,
				array('CONST_TRAIT' => ''),
				array('trait' => array(false, ''), 'strait' => array(true, '')),
				array('foo' => array(array('trait' => ''), array('trait' => '')))
			),
			'ns\\TokenReflection_Test_ConstantMagic54WithTrait' => array(
				false,
				array('CONST_TRAIT' => ''),
				array('t_trait' => array(false, 'ns\\TokenReflection_Test_ConstantMagic54Trait'), 't_strait' => array(true, 'ns\\TokenReflection_Test_ConstantMagic54Trait'), 'trait' => array(false, ''), 'strait' => array(true, ''), 'trait2' => array(false, ''), 'strait2' => array(true, '')),
				array('foo' => array(array('trait' => ''), array('trait' => '')), 'bar' => array(array('trait' => ''), array('trait' => '')), 't_foo' => array(array('trait' => 'ns\\TokenReflection_Test_ConstantMagic54Trait'), array('trait' => 'ns\\TokenReflection_Test_ConstantMagic54Trait'))),
			),
			'ns2\\TokenReflection_Test_ConstantMagic54' => array(
				false,
				array('CONST_TRAIT' => ''),
				array('trait' => array(false, ''), 'strait' => array(true, '')),
				array('foo' => array(array('trait' => ''), array('trait' => '')))
			),
			'ns2\\TokenReflection_Test_ConstantMagic54WithTrait' => array(
				false,
				array('CONST_TRAIT' => ''),
				array('t_trait' => array(false, 'TokenReflection_Test_ConstantMagic54Trait'), 't_strait' => array(true, 'TokenReflection_Test_ConstantMagic54Trait'), 'trait' => array(false, ''), 'strait' => array(true, ''), 'trait2' => array(false, ''), 'strait2' => array(true, '')),
				array('foo' => array(array('trait' => ''), array('trait' => '')), 'bar' => array(array('trait' => ''), array('trait' => '')), 't_foo' => array(array('trait' => 'TokenReflection_Test_ConstantMagic54Trait'), array('trait' => 'TokenReflection_Test_ConstantMagic54Trait'))),
			),
			'ns3\\TokenReflection_Test_ConstantMagic54' => array(
				false,
				array('CONST_TRAIT' => ''),
				array('trait' => array(false, ''), 'strait' => array(true, '')),
				array('foo' => array(array('trait' => ''), array('trait' => '')))
			),
			'ns3\\TokenReflection_Test_ConstantMagic54WithTrait' => array(
				false,
				array('CONST_TRAIT' => ''),
				array('t_trait' => array(false, 'ns\\TokenReflection_Test_ConstantMagic54Trait'), 't_strait' => array(true, 'ns\\TokenReflection_Test_ConstantMagic54Trait'), 'trait' => array(false, ''), 'strait' => array(true, ''), 'trait2' => array(false, ''), 'strait2' => array(true, '')),
				array('foo' => array(array('trait' => ''), array('trait' => '')), 'bar' => array(array('trait' => ''), array('trait' => '')), 't_foo' => array(array('trait' => 'ns\\TokenReflection_Test_ConstantMagic54Trait'), array('trait' => 'ns\\TokenReflection_Test_ConstantMagic54Trait'))),
			),
		);

		$this->assertSame(count($expected_classes), count($token_classes));
		foreach ($token_classes as $name => $token) {
			$this->assertTrue(isset($expected_classes[$name]), $name);

			$this->assertSame($expected_classes[$name][0], $token->isTrait(), $name);

			// Constants
			$this->assertSame(count($expected_classes[$name][1]), count($token->getConstants()), $name);
			foreach ($token->getConstants() as $constant_name => $value) {
				$this->assertArrayHasKey($constant_name, $expected_classes[$name][1], sprintf('%s::%s', $name, $constant_name));
				$this->assertSame($expected_classes[$name][1][$constant_name], $value, sprintf('%s::%s', $name, $constant_name));
			}


			// Properties
			$this->assertSame(count($expected_classes[$name][2]), count($token->getProperties()), $name);
			foreach ($token->getProperties() as $reflection) {
				$property_name = $reflection->getName();

				$this->assertArrayHasKey($property_name, $expected_classes[$name][2], sprintf('%s::$%s', $name, $property_name));
				$this->assertSame($expected_classes[$name][2][$property_name][0], $token->getProperty($property_name)->isStatic(), sprintf('%s::$%s', $name, $property_name));

				if ($reflection->isStatic()) {
					$this->assertSame($expected_classes[$name][2][$property_name][1], $token->getStaticPropertyValue($property_name), sprintf('%s::$%s', $name, $property_name));
				} else {
					$this->assertSame($expected_classes[$name][2][$property_name][1], $token->getProperty($property_name)->getDefaultValue(), sprintf('%s::$%s', $name, $property_name));
				}
			}

			// Methods
			$this->assertSame(count($expected_classes[$name][3]), count($token->getMethods()), $name);
			foreach ($token->getMethods() as $method) {
				$method_name = $method->getName();

				$this->assertArrayHasKey($method_name, $expected_classes[$name][3], sprintf('%s::%s()', $name, $method_name));

				// Parameters
				$this->assertSame(count($expected_classes[$name][3][$method_name][0]), $method->getNumberOfParameters(), sprintf('%s::%s()', $name, $method_name));
				$this->assertSame($method->getNumberOfParameters(), count($method->getParameters()), sprintf('%s::%s()', $name, $method_name));

				foreach ($method->getParameters() as $parameter) {
					$parameter_name = $parameter->getName();

					$this->assertArrayHasKey($parameter_name, $expected_classes[$name][3][$method_name][0], sprintf('%s::%s(%s)', $name, $method_name, $parameter_name));

					$this->assertTrue($parameter->isDefaultValueAvailable(), sprintf('%s::%s(%s)', $name, $method_name, $parameter_name));
					$this->assertSame($expected_classes[$name][3][$method_name][0][$parameter_name], $parameter->getDefaultValue(), sprintf('%s::%s(%s)', $name, $method_name, $parameter_name));
				}

				// Static variables
				$token_variables = $method->getStaticVariables();
				$this->assertSame(count($expected_classes[$name][3][$method_name][1]), count($token_variables), sprintf('%s::%s()', $name, $method_name));

				foreach ($token_variables as $variable_name => $variable_value) {
					$this->assertArrayHasKey($variable_name, $expected_classes[$name][3][$method_name][1], sprintf('%s::%s()::%s', $name, $method_name, $variable_name));
					$this->assertSame($expected_classes[$name][3][$method_name][1][$variable_name], $variable_value, sprintf('%s::%s()::%s', $name, $method_name, $variable_name));
				}
			}
		}
	}

	/**
	 * Tests returning pretty constant names.
	 */
	public function testPrettyNames()
	{
		static $names = array(
			'ns1\\CONST_PRETTY_NAMES_1',
			'CONST_PRETTY_NAMES_1',
			'ns1\\ConstPrettyNames::INTERNAL',
			'ConstPrettyNames::INTERNAL',
		);

		$broker = $this->getBroker();
		$broker->processFile($this->getFilePath('pretty-names'));

		foreach ($names as $name) {
			$this->assertTrue($broker->hasConstant($name), $name);

			$rfl = $broker->getConstant($name);
			$this->assertSame($name, $rfl->getPrettyName(), $name);
		}
	}

	/**
	 * Tests an exception thrown when trying to get instance of TokenReflection\Php\ReflectionConstant and providing an invalid parent reflection.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testInternalConstantConstructor()
	{
		new Php\ReflectionConstant('foo', 'bar', $this->getBroker(), new Php\ReflectionClass('Exception', $this->getBroker()));
	}

	/**
	 * Tests an exception thrown when trying to export an constant.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testInternalConstantExport1()
	{
		Php\ReflectionConstant::export($this->getBroker(), null, '~non-existent~', true);
	}

	/**
	 * Tests an exception thrown when trying to export an constant.
	 *
	 * @expectedException \TokenReflection\Exception\RuntimeException
	 */
	public function testInternalConstantExport2()
	{
		Php\ReflectionConstant::export($this->getBroker(), '~non-existent~', '~non-existent~', true);
	}

	/**
	 * Tests various constant (mis)definitions.
	 */
	public function testValueDefinitions()
	{
		static $expected = array(
			'VALUE_DEFINITION1' => true,
			'VALUE_DEFINITION2' => true,
			'VALUE_DEFINITION3' => true,
			'VALUE_DEFINITION4' => true,
			'VALUE_DEFINITION5' => true,
			'VALUE_DEFINITION6' => true,
			'VALUE_DEFINITION7' => true
		);

		$broker = $this->getBroker();
		$broker->processFile($this->getFilePath('valueDefinitions'));

		foreach ($expected as $name => $value) {
			$this->assertTrue($broker->hasConstant($name), $name);

			$rfl = $broker->getConstant($name);
			$this->assertSame($value, $rfl->getValue(), $name);
		}
	}

	/**
	 * Tests constants defined in interfaces.
	 */
	public function testInterfaces()
	{
		$broker = new Broker(new Broker\Backend\Memory());
		$broker->process($this->getFilePath('interfaces'));

		$class1 = $broker->getClass('TokenReflection_Test_ConstantInterfaceClass');
		$this->assertTrue($class1->hasConstant('FIRST'));

		$class2 = $broker->getClass('TokenReflection_Test_ConstantInterfaceClass2');
		$this->assertTrue($class2->hasConstant('FIRST'));
		$this->assertTrue($class2->hasConstant('SECOND'));
	}

	/**
	 * Tests constants overriding.
	 *
	 * (btw that sucks even more than eval)
	 */
	public function testOverriding()
	{
		$token = $this->getClassTokenReflection('overriding');

		$this->assertTrue($token->hasConstant('FOO'));
		$constant = $token->getConstantReflection('FOO');
		$this->assertSame('notbar', $constant->getValue());
		$this->assertSame('TokenReflection_Test_ConstantOverriding', $constant->getDeclaringClassName());

		$this->assertTrue($token->getParentClass()->hasConstant('FOO'));
		$constant = $token->getParentClass()->getConstantReflection('FOO');
		$this->assertSame('bar', $constant->getValue());
		$this->assertSame('TokenReflection_Test_ConstantOverridingBase', $constant->getDeclaringClassName());
	}
}
