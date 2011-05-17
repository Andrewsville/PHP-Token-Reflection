<?php

namespace TokenReflection;

require_once __DIR__ . '/../bootstrap.php';

class ReflectionPropertyTest extends Test
{
	protected $type = 'property';

	public function testLines()
	{
		$token = $this->getPropertyTokenReflection('lines');

		$this->assertSame(5, $token->getStartLine());
		$this->assertSame(5, $token->getEndLine());
	}

	public function testComment()
	{
		$rfl = $this->getPropertyReflection('docComment');
		$this->assertSame($rfl->internal->getDocComment(), $rfl->token->getDocComment());
		$this->assertSame("/**\n\t * This is a property.\n\t */", $rfl->token->getDocComment());

		$rfl = $this->getPropertyReflection('noComment');
		$this->assertSame($rfl->internal->getDocComment(), $rfl->token->getDocComment());
		$this->assertFalse($rfl->token->getDocComment());
	}

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

		$this->assertSame(array(), $rfl->token->getProperty('param4')->getAnnotations());
		$this->assertNull($rfl->token->getProperty('param4')->getAnnotation(ReflectionAnnotation::LONG_DESCRIPTION));
	}

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
				$this->fail('Expected exception \TokenReflection\Exception.');
			} catch (\PHPUnit_Framework_AssertionFailedError $e) {
				throw $e;
			} catch (\Exception $e) {
				// Correctly thrown exception
				$this->assertInstanceOf('TokenReflection\Exception', $e);
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

	public function testDeclaringClass()
	{
		$rfl = $this->getClassReflection('declaringClass');

		foreach (array('parent' => 'Parent', 'child' => '', 'parentOverlay' => '') as $property => $class) {
			$internal = $rfl->internal->getProperty($property);
			$token = $rfl->token->getProperty($property);

			$this->assertSame($internal->getDeclaringClass()->getName(), $token->getDeclaringClass()->getName());
			$this->assertSame('TokenReflection_Test_PropertyDeclaringClass' .  $class, $token->getDeclaringClass()->getName());
			$this->assertSame('TokenReflection_Test_PropertyDeclaringClass' .  $class, $token->getDeclaringClassName());
			$this->assertSame('TokenReflection_Test_PropertyDeclaringClass' .  $class, $token->getClass());
			$this->assertInstanceOf('TokenReflection\ReflectionClass', $token->getDeclaringClass());
		}
	}

	public function testDefault()
	{
		ReflectionProperty::setParseValueDefinitions(true);

		$token = $this->getPropertyTokenReflection('default');
		$this->assertTrue($token->isDefault());
		$this->assertSame('default', $token->getDefaultValue());
		$this->assertSame("'default'", $token->getDefaultValueDefinition());

		$token = $this->getPropertyTokenReflection('noDefault');
		$this->assertFalse($token->isDefault());
		$this->assertNull($token->getDefaultValue());

		ReflectionProperty::setParseValueDefinitions(false);
	}

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
			$this->assertSame(\ReflectionProperty::IS_STATIC | constant('\ReflectionProperty::IS_' . strtoupper($name)), $token->getModifiers());
		}
	}

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
}
