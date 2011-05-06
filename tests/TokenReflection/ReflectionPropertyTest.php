<?php

namespace TokenReflection;

require_once __DIR__ . '/../bootstrap.php';

class ReflectionPropertyTest extends Test
{
	protected $type = 'property';

	public function testLines()
	{
		$token = $this->getPropertyTokenReflection('lines');

		$this->assertEquals(5, $token->getStartLine());
		$this->assertEquals(5, $token->getEndLine());
	}

	public function testComment()
	{
		$rfl = $this->getPropertyReflection('docComment');
		$this->assertEquals($rfl->internal->getDocComment(), $rfl->token->getDocComment());
		$this->assertEquals("/**\n\t * This is a property.\n\t */", $rfl->token->getDocComment());

		$rfl = $this->getPropertyReflection('noComment');
		$this->assertEquals($rfl->internal->getDocComment(), $rfl->token->getDocComment());
		$this->assertFalse($rfl->token->getDocComment());
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

			$this->assertEquals($internal->setAccessible(true), $token->setAccessible(true));
			$this->assertNull($token->setAccessible(true));

			$this->assertEquals($internal->getValue($object), $token->getValue($object));
			$this->assertTrue($token->getValue($object));

			$this->assertEquals($internal->setValue($object, false), $token->setValue($object, false));
			$this->assertNull($token->setValue($object, false));

			$this->assertEquals($internal->getValue($object), $token->getValue($object));
			$this->assertFalse($token->getValue($object));
		}

		$internal = $rfl->internal->getProperty('public');
		$token = $rfl->token->getProperty('public');

		$this->assertEquals($internal->getValue($object), $token->getValue($object));
		$this->assertTrue($token->getValue($object));

		$this->assertEquals($internal->setValue($object, false), $token->setValue($object, false));
		$this->assertNull($token->setValue($object, false));

		$this->assertEquals($internal->getValue($object), $token->getValue($object));
		$this->assertFalse($token->getValue($object));

		$this->assertEquals($internal->setAccessible(false), $token->setAccessible(false));
		$this->assertNull($token->setAccessible(false));
		$this->assertEquals($internal->getValue($object), $token->getValue($object));
	}

	public function testDeclaringClass()
	{
		$rfl = $this->getClassReflection('declaringClass');

		foreach (array('parent' => 'Parent', 'child' => '', 'parentOverlay' => '') as $property => $class) {
			$internal = $rfl->internal->getProperty($property);
			$token = $rfl->token->getProperty($property);

			$this->assertEquals($internal->getDeclaringClass()->getName(), $token->getDeclaringClass()->getName());
			$this->assertEquals('TokenReflection_Test_PropertyDeclaringClass' .  $class, $token->getDeclaringClass()->getName());
			$this->assertEquals('TokenReflection_Test_PropertyDeclaringClass' .  $class, $token->getDeclaringClassName());
			$this->assertEquals('TokenReflection_Test_PropertyDeclaringClass' .  $class, $token->getClass());
			$this->assertInstanceOf('TokenReflection\ReflectionClass', $token->getDeclaringClass());
		}
	}

	public function testDefault()
	{
		ReflectionProperty::setParseValueDefinitions(true);

		$token = $this->getPropertyTokenReflection('default');
		$this->assertTrue($token->isDefault());
		$this->assertEquals('default', $token->getDefaultValue());
		$this->assertEquals("'default'", $token->getDefaultValueDefinition());

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

			$this->assertEquals($internal->$method(), $internal->$method());
			$this->assertTrue($token->$method());
			$this->assertEquals($internal->isStatic(), $internal->isStatic());
			$this->assertFalse($token->isStatic());
			$this->assertEquals($internal->getModifiers(), $token->getModifiers());
			$this->assertEquals(constant('\ReflectionProperty::IS_' . strtoupper($name)), $token->getModifiers());

			$internal = $rfl->internal->getProperty($opposite);
			$token = $rfl->token->getProperty($opposite);

			$this->assertEquals($internal->$method(), $internal->$method());
			$this->assertFalse($token->$method());
			$this->assertEquals($internal->getModifiers(), $token->getModifiers());

			$internal = $rfl->internal->getProperty($staticName);
			$token = $rfl->token->getProperty($staticName);

			$this->assertEquals($internal->$method(), $internal->$method());
			$this->assertTrue($token->$method());
			$this->assertEquals($internal->isStatic(), $internal->isStatic());
			$this->assertTrue($token->isStatic());
			$this->assertEquals($internal->getModifiers(), $token->getModifiers());
			$this->assertEquals(\ReflectionProperty::IS_STATIC | constant('\ReflectionProperty::IS_' . strtoupper($name)), $token->getModifiers());
		}

		// Shadow
		$rfl = $this->getPropertyReflection('shadow');
//		$this->assertEquals($rfl->internal->getModifiers(), $rfl->token->getModifiers());

		$rfl = new \stdClass();
		$rfl->internal = new \ReflectionProperty('TokenReflection_Test_PropertyShadowParent', 'shadow');
		$rfl->token = $this->getBroker()->getClass('TokenReflection_Test_PropertyShadowParent')->getProperty('shadow');
//		$this->assertEquals($rfl->internal->getModifiers(), $rfl->token->getModifiers());
	}

	public function testTypes()
	{
		$constants = array('string' => 'string', 'integer' => 1, 'float' => 1.1, 'boolean' => true, 'null' => null, 'array' => array(1 => 1));
		foreach ($constants as $type => $value) {
			$test = 'type' . ucfirst($type);

			$rfl = $this->getPropertyReflection($test);
			$className = $this->getClassName($test);
			$object = new $className();

			$this->assertEquals($rfl->internal->getValue($object), $rfl->token->getValue($object));
			$this->assertSame($value, $rfl->token->getValue($object));
		}
	}
}
