<?php


namespace ApiGen\TokenReflection\Tests;

use ApiGen;
use ApiGen\TokenReflection\Php\ReflectionProperty;
use ApiGen\TokenReflection\ReflectionAnnotation;
use ReflectionProperty as InternalReflectionProperty;


/**
 * Property test.
 */
class ReflectionPropertyTest extends TestCase
{
	/**
	 * Element type.
	 *
	 * @var string
	 */
	protected $type = 'property';

	/**
	 * Tests getting of start and end line.
	 */
	public function testLines()
	{
		$token = $this->getPropertyTokenReflection('lines');

		$this->assertSame(5, $token->getStartLine());
		$this->assertSame(5, $token->getEndLine());
	}

	/**
	 * Tests getting of documentation comment.
	 */
	public function testComment()
	{
		$rfl = $this->getClassReflection('docComment');
		foreach ($rfl->internal->getProperties() as $property) {
			$this->assertFalse(false === $property->getDocComment(), $property->getName());
			$this->assertTrue($rfl->token->hasProperty($property->getName()), $property->getName());
			$this->assertSame($property->getDocComment(), $rfl->token->getProperty($property->getName())->getDocComment(), $property->getName());
		}

		$propertyName = 'docComment';
		$this->assertTrue($rfl->token->hasProperty($propertyName));

		/** @var ApiGen\TokenReflection\ReflectionProperty */
		$tokenProperty = $rfl->token->getProperty($propertyName);
		$this->assertTrue($tokenProperty->hasAnnotation('var'));
		$this->assertSame(array("String It is a string\n\tand this comment has multiple\n\tlines."), $tokenProperty->getAnnotation('var'));

		$rfl = $this->getPropertyReflection('noComment');
		$this->assertSame($rfl->internal->getDocComment(), $rfl->token->getDocComment());
		$this->assertFalse($rfl->token->getDocComment());
	}

	/**
	 * Tests heredoc defined value.
	 */
	public function testHeredoc()
	{
		$token = $this->getClassTokenReflection('heredoc');

		$this->assertTrue($token->hasOwnProperty('heredoc'));
		$property = $token->getProperty('heredoc');
		$this->assertTrue($property->isDefault());
		$this->assertSame('property value', $property->getDefaultValue());

		$this->assertTrue($token->hasOwnProperty('nowdoc'));
		$property = $token->getProperty('nowdoc');
		$this->assertTrue($property->isDefault());
		$this->assertSame('property value', $property->getDefaultValue());
	}

	/**
	 * Tests getting of copydoc documentation comment.
	 */
	public function testCommentCopydoc()
	{
		static $properties = array(
			'property' => 'This is a property.',
			'property2' => 'This is a property.',
			'property3' => 'This is a property.',
			'property4' => 'This is a property.',
			'property5' => null,
			'property6' => null,
			'property7' => null
		);

		$class = $this->getClassTokenReflection('docCommentCopydoc');
		foreach ($properties as $propertyName => $shortDescription) {
			$this->assertTrue($class->hasProperty($propertyName), $propertyName);
			$this->assertSame($shortDescription, $class->getProperty($propertyName)->getAnnotation(ReflectionAnnotation::SHORT_DESCRIPTION), $propertyName);
		}
	}

	/**
	 * Tests getting of inherited documentation comment.
	 */
	public function testDocCommentInheritance()
	{
		require_once $this->getFilePath('docCommentInheritance');
		$this->getBroker()->processFile($this->getFilePath('docCommentInheritance'));

		$grandParent = new \stdClass();
		$grandParent->token = $this->getBroker()->getClass('TokenReflection_Test_PropertyDocCommentInheritanceGrandParent');

		$parent = new \stdClass();
		$parent->token = $this->getBroker()->getClass('TokenReflection_Test_PropertyDocCommentInheritanceParent');

		$rfl = new \stdClass();
		$rfl->token = $this->getBroker()->getClass('TokenReflection_Test_PropertyDocCommentInheritance');

		$this->assertSame($parent->token->getProperty('param1')->getAnnotations(), $rfl->token->getProperty('param1')->getAnnotations());
		$this->assertSame('Private1 short. Protected1 short.', $rfl->token->getProperty('param1')->getAnnotation(ReflectionAnnotation::SHORT_DESCRIPTION));
		$this->assertSame('Protected1 long. Private1 long.', $rfl->token->getProperty('param1')->getAnnotation(ReflectionAnnotation::LONG_DESCRIPTION));

		$this->assertSame($parent->token->getProperty('param2')->getAnnotations(), $rfl->token->getProperty('param2')->getAnnotations());
		$this->assertSame($grandParent->token->getProperty('param2')->getAnnotations(), $rfl->token->getProperty('param2')->getAnnotations());

		$this->assertSame('Public3 Protected3  short.', $rfl->token->getProperty('param3')->getAnnotation(ReflectionAnnotation::SHORT_DESCRIPTION));
		$this->assertNull($rfl->token->getProperty('param3')->getAnnotation(ReflectionAnnotation::LONG_DESCRIPTION));

		$this->assertSame('Protected4 short.', $rfl->token->getProperty('param4')->getAnnotation(ReflectionAnnotation::SHORT_DESCRIPTION));
		$this->assertNull($rfl->token->getProperty('param4')->getAnnotation(ReflectionAnnotation::LONG_DESCRIPTION));
		$this->assertSame(array('boolean'), $rfl->token->getProperty('param4')->getAnnotation('var'));
	}

	/**
	 * Tests getting of documentation comment from templates.
	 */
	public function testCommentTemplate()
	{
		static $expected = array(
			'public1' => array( // Template definition
				ReflectionAnnotation::SHORT_DESCRIPTION => 'Short description.',
				ReflectionAnnotation::LONG_DESCRIPTION => 'Long description.',
				'var' => array('string')
			),
			'public2' => array( // No own docblock -> using template
				ReflectionAnnotation::LONG_DESCRIPTION => 'Long description.',
				'var' => array('string')
			),
			'public3' => array( // Another template to the stack plus using the previuos template
				ReflectionAnnotation::SHORT_DESCRIPTION => 'Another short description.',
				ReflectionAnnotation::LONG_DESCRIPTION => "Long description.\nAnother long description.",
				'var' => array('array', 'string')
			),
			'public4' => array( // Own short description, inheriting the rest from the two templates
				ReflectionAnnotation::SHORT_DESCRIPTION => 'Own short description.',
				ReflectionAnnotation::LONG_DESCRIPTION => "Long description.\nAnother long description.",
				'var' => array('array', 'string')
			),
			// Template end -> remove the second template from the stack
			'public5' => array(
				ReflectionAnnotation::SHORT_DESCRIPTION => 'Another own short description.',
				ReflectionAnnotation::LONG_DESCRIPTION => "Long description.\nOwn long description.",
				'var' => array('integer', 'string')
			),
			// Template end -> remove the first template from the stack
			'public6' => array(
				// No annotations
			),
			'public7' => array(
				ReflectionAnnotation::SHORT_DESCRIPTION => 'Outside of template.',
				'var' => array('boolean')
			),
		);

		$rfl = $this->getClassReflection('docCommentTemplate')->token;

		foreach ($expected as $name => $annotations) {
			$property = $rfl->getProperty($name);
			$this->assertSame($annotations, $property->getAnnotations());
			if (empty($annotations)) {
				$this->assertFalse($property->getDocComment());
			}
		}
	}

	/**
	 * TestCase property accessibility.
	 */
	public function testAccessible()
	{
		$rfl = $this->getClassReflection('accessible');
		$className = $this->getClassName('accessible');
		$object = new $className();

		foreach (array('protected', 'private') as $property) {
			$internal = $rfl->internal->getProperty($property);
			$token = $rfl->token->getProperty($property);

			try {
				$token->getValue($object);
				$this->fail('Expected exception ApiGen\TokenReflection\Exception\RuntimeException.');
			} catch (\PHPUnit_Framework_AssertionFailedError $e) {
				throw $e;
			} catch (\Exception $e) {
				// Correctly thrown exception
				$this->assertInstanceOf('ApiGen\TokenReflection\Exception\RuntimeException', $e);
			}

			$this->assertSame($internal->setAccessible(true), $token->setAccessible(true));
			$this->assertNull($token->setAccessible(true));

			$this->assertSame($internal->getValue($object), $token->getValue($object));
			$this->assertTrue($token->getValue($object));

			$this->assertSame($internal->setValue($object, false), $token->setValue($object, false));
			$this->assertNull($token->setValue($object, false));

			$this->assertSame($internal->getValue($object), $token->getValue($object));
			$this->assertFalse($token->getValue($object));
		}

		$internal = $rfl->internal->getProperty('public');
		$token = $rfl->token->getProperty('public');

		$this->assertSame($internal->getValue($object), $token->getValue($object));
		$this->assertTrue($token->getValue($object));

		$this->assertSame($internal->setValue($object, false), $token->setValue($object, false));
		$this->assertNull($token->setValue($object, false));

		$this->assertSame($internal->getValue($object), $token->getValue($object));
		$this->assertFalse($token->getValue($object));

		$this->assertSame($internal->setAccessible(false), $token->setAccessible(false));
		$this->assertNull($token->setAccessible(false));
		$this->assertSame($internal->getValue($object), $token->getValue($object));
	}

	/**
	 * Tests the access level of a property that is declared as just "static".
	 */
	public function testOnlyStatic()
	{
		$rfl = $this->getPropertyReflection('onlyStatic');

		$this->assertTrue($rfl->internal->isStatic());
		$this->assertSame($rfl->internal->isStatic(), $rfl->token->isStatic());
		$this->assertTrue($rfl->internal->isPublic());
		$this->assertSame($rfl->internal->isPublic(), $rfl->token->isPublic());
		$this->assertSame($rfl->internal->getModifiers(), $rfl->token->getModifiers());
	}

	/**
	 * Tests getting of declaring class.
	 */
	public function testDeclaringClass()
	{
		$rfl = $this->getClassReflection('declaringClass');

		foreach (array('parent' => 'Parent', 'child' => '', 'parentOverlay' => '') as $property => $class) {
			$internal = $rfl->internal->getProperty($property);
			$token = $rfl->token->getProperty($property);

			$this->assertSame($internal->getDeclaringClass()->getName(), $token->getDeclaringClass()->getName());
			$this->assertSame('TokenReflection_Test_PropertyDeclaringClass' .  $class, $token->getDeclaringClass()->getName());
			$this->assertSame('TokenReflection_Test_PropertyDeclaringClass' .  $class, $token->getDeclaringClassName());
			$this->assertInstanceOf('ApiGen\TokenReflection\ReflectionClass', $token->getDeclaringClass());
		}
	}

	/**
	 * Tests getting of default value.
	 */
	public function testDefault()
	{
		$class = $this->getClassTokenReflection('default');

		$this->assertTrue($class->hasProperty('default'));
		$property = $class->getProperty('default');
		$this->assertTrue($property->isDefault());
		$this->assertSame('default', $property->getDefaultValue());
		$this->assertSame("'default'", $property->getDefaultValueDefinition());

		$this->assertTrue($class->hasProperty('default2'));
		$property = $class->getProperty('default2');
		$this->assertTrue($property->isDefault());
		$this->assertSame('default', $property->getDefaultValue());
		$this->assertSame('self::DEFAULT_VALUE', $property->getDefaultValueDefinition());

		$this->assertTrue($class->hasProperty('default3'));
		$property = $class->getProperty('default3');
		$this->assertTrue($property->isDefault());
		$this->assertSame('default', $property->getDefaultValue());
		$this->assertSame('TokenReflection_Test_PropertyDefault::DEFAULT_VALUE', $property->getDefaultValueDefinition());

		$class = $this->getBroker()->getClass('TokenReflection_Test_PropertyDefault2');

		$this->assertTrue($class->hasProperty('default4'));
		$property = $class->getProperty('default4');
		$this->assertTrue($property->isDefault());
		$this->assertSame('not default', $property->getDefaultValue());
		$this->assertSame('self::DEFAULT_VALUE', $property->getDefaultValueDefinition());

		$this->assertTrue($class->hasProperty('default5'));
		$property = $class->getProperty('default5');
		$this->assertTrue($property->isDefault());
		$this->assertSame('not default', $property->getDefaultValue());
		$this->assertSame('TokenReflection_Test_PropertyDefault2::DEFAULT_VALUE', $property->getDefaultValueDefinition());

		$this->assertTrue($class->hasProperty('default6'));
		$property = $class->getProperty('default6');
		$this->assertTrue($property->isDefault());
		$this->assertSame('default', $property->getDefaultValue());
		$this->assertSame('parent::DEFAULT_VALUE', $property->getDefaultValueDefinition());

		$this->assertTrue($class->hasProperty('default7'));
		$property = $class->getProperty('default7');
		$this->assertTrue($property->isDefault());
		$this->assertSame('default', $property->getDefaultValue());
		$this->assertSame('TokenReflection_Test_PropertyDefault::DEFAULT_VALUE', $property->getDefaultValueDefinition());

		$this->assertTrue($class->hasProperty('default8'));
		$property = $class->getProperty('default8');
		$this->assertTrue($property->isDefault());
		$this->assertSame('default', $property->getDefaultValue());
		$this->assertSame('self::PARENT_DEFAULT_VALUE', $property->getDefaultValueDefinition());

		$this->assertTrue($class->hasProperty('default9'));
		$property = $class->getProperty('default9');
		$this->assertTrue($property->isDefault());
		$this->assertSame(array('not default', 'default', 'default'), $property->getDefaultValue());
		$this->assertSame('array(self::DEFAULT_VALUE, parent::DEFAULT_VALUE, self::PARENT_DEFAULT_VALUE)', $property->getDefaultValueDefinition());

		$token = $this->getPropertyTokenReflection('noDefault');
		$this->assertTrue($token->isDefault());
		$this->assertNull($token->getDefaultValue());
	}

	/**
	 * Tests all property modifiers.
	 */
	public function testModifiers()
	{
		$rfl = $this->getClassReflection('modifiers');

		foreach (array('public', 'protected', 'private') as $name) {
			$method = 'is' . ucfirst($name);
			$opposite = 'no' . ucfirst($name);
			$staticName = $name . 'Static';

			$internal = $rfl->internal->getProperty($name);
			$token = $rfl->token->getProperty($name);

			$this->assertSame($internal->$method(), $internal->$method());
			$this->assertTrue($token->$method());
			$this->assertSame($internal->isStatic(), $internal->isStatic());
			$this->assertFalse($token->isStatic());
			$this->assertSame($internal->getModifiers(), $token->getModifiers());
			$this->assertSame(constant('\ReflectionProperty::IS_' . strtoupper($name)), $token->getModifiers());

			$internal = $rfl->internal->getProperty($opposite);
			$token = $rfl->token->getProperty($opposite);

			$this->assertSame($internal->$method(), $internal->$method());
			$this->assertFalse($token->$method());
			$this->assertSame($internal->getModifiers(), $token->getModifiers());

			$internal = $rfl->internal->getProperty($staticName);
			$token = $rfl->token->getProperty($staticName);

			$this->assertSame($internal->$method(), $internal->$method());
			$this->assertTrue($token->$method());
			$this->assertSame($internal->isStatic(), $internal->isStatic());
			$this->assertTrue($token->isStatic());
			$this->assertSame($internal->getModifiers(), $token->getModifiers());
			$this->assertSame(InternalReflectionProperty::IS_STATIC | constant('\ReflectionProperty::IS_' . strtoupper($name)), $token->getModifiers());
		}
	}

	/**
	 * Tests different types of property value.
	 */
	public function testTypes()
	{
		$constants = array('string' => 'string', 'integer' => 1, 'float' => 1.1, 'boolean' => true, 'null' => null, 'array' => array(1 => 1));
		foreach ($constants as $type => $value) {
			$test = 'type' . ucfirst($type);

			$rfl = $this->getPropertyReflection($test);
			$className = $this->getClassName($test);
			$object = new $className();

			$this->assertSame($rfl->internal->getValue($object), $rfl->token->getValue($object));
			$this->assertSame($value, $rfl->token->getValue($object));
		}
	}

	/**
	 * Tests export.
	 */
	public function testToString()
	{
		$tests = array(
			'lines', 'docComment', 'noComment',
			'default', 'typeNull', 'typeArray', 'typeString', 'typeInteger', 'typeFloat'
		);
		foreach ($tests as $test) {
			$rfl = $this->getPropertyReflection($test);
			$this->assertSame($rfl->internal->__toString(), $rfl->token->__toString());
			$this->assertSame(InternalReflectionProperty::export($this->getClassName($test), $test, true), ApiGen\TokenReflection\ReflectionProperty::export($this->getBroker(), $this->getClassName($test), $test, true));

			// TestCase loading from a string
			$rfl = $this->getPropertyReflection($test, true);
			$this->assertSame($rfl->internal->__toString(), $rfl->token->__toString());
		}

		$rfl = $this->getClassReflection('modifiers');
		$rfl_fromString = $this->getClassReflection('modifiers');
		foreach (array('public', 'protected', 'private') as $name) {
			$internal = $rfl->internal->getProperty($name);
			$token = $rfl->token->getProperty($name);
			$this->assertSame($internal->__toString(), $token->__toString());
			$this->assertSame(InternalReflectionProperty::export($this->getClassName('modifiers'), $name, true), ApiGen\TokenReflection\ReflectionProperty::export($this->getBroker(), $this->getClassName('modifiers'), $name, true));

			// TestCase loading from a string
			$this->assertSame($internal->__toString(), $rfl_fromString->token->getProperty($name)->__toString());
		}

		$this->assertSame(InternalReflectionProperty::export('ReflectionProperty', 'name', true), ApiGen\TokenReflection\ReflectionProperty::export($this->getBroker(), 'ReflectionProperty', 'name', true));
		$this->assertSame(InternalReflectionProperty::export(new InternalReflectionProperty('ReflectionProperty', 'name'), 'name', true), ApiGen\TokenReflection\ReflectionProperty::export($this->getBroker(), new InternalReflectionProperty('ReflectionProperty', 'name'), 'name', true));
	}

	/**
	 * Tests new PHP 5.4 features.
	 */
	public function test54features()
	{
		$tests = array('public', 'protected', 'private');

		$rfl = $this->getClassReflection('54features');
		$class = $rfl->internal->newInstance();

		foreach ($tests as $test) {
			$this->assertTrue($rfl->internal->hasProperty($test));
			$this->assertTrue($rfl->token->hasProperty($test));

			$internal = $rfl->internal->getProperty($test);
			$token = $rfl->token->getProperty($test);

			$internal->setAccessible(true);
			$token->setAccessible(true);

			$this->assertSame($internal->getValue($class), $token->getValue($class));
			$this->assertSame($internal->getValue($class), $token->getDefaultValue());
		}
	}

	/**
	 * Tests an exception thrown when trying to create the reflection from a PHP internal reflection.
	 *
	 * @expectedException ApiGen\TokenReflection\Exception\RuntimeException
	 */
	public function testInternalPropertyReflectionCreate()
	{
		ReflectionProperty::create(new \ReflectionClass('Exception'), $this->getBroker());
	}

	/**
	 * Tests various constant (mis)definitions.
	 */
	public function testValueDefinitions()
	{
		static $expected = array(
			'property1' => true,
			'property2' => true,
			'property3' => true,
			'property4' => true,
			'property5' => true,
			'property6' => true,
			'property7' => true,
			'property8' => true
		);

		$rfl = $this->getClassTokenReflection('valueDefinitions');

		foreach ($expected as $name => $value) {
			$this->assertTrue($rfl->hasProperty($name), $name);
			$this->assertSame($value, $rfl->getProperty($name)->getDefaultValue(), $name);
		}
	}
}
