<?php
/**
 * @file
 */

class VarCheckTest extends PHPUnit_Framework_TestCase {

  protected $var;
  protected $array;
  protected $object;
  protected $mixed;

  public function setUp() {
    require_once 'VarCheck.php';

    $this->var = 'foobar';

    $this->array = array(
      'foo' => array(
        'bar' => 1,
      ),
      'baz' => TRUE,
      2 => FALSE,
    );

    $this->object = new stdClass();
    $this->object->foo = 'bar';
    $this->object->bar = new stdClass();
    $this->object->bar->baz = 1;

    $this->mixed = array(
      'var' => $this->var,
      'array' => $this->array,
      'object' => $this->object,
    );
  }

  public function testValue() {
    $this->assertEquals(
      VarCheck::make($this->var)->_value(),
      'foobar',
      'Value is foobar'
    );

    $this->assertEquals(
      VarCheck::make($this->array)->_key('foo')->_key('bar')->_value(),
      1,
      'Array is 1'
    );

    $this->assertEquals(
      VarCheck::make($this->object)->_attr('bar')->_attr('baz')->_value(),
      1,
      'Object is 1'
    );

    $this->assertEquals(
      VarCheck::make($this->mixed)->_key('object')->_attr('foo')->_value(),
      'bar',
      'Mixed is bar'
    );
  }

  public function testExist() {
    $this->assertTrue(
      VarCheck::make($this->var)->_exist(),
      'Var exists'
    );

    $this->assertTrue(
      VarCheck::make($this->array)->_key('foo')->_key('bar')->_exist(),
      'Array exists'
    );

    $this->assertTrue(
      VarCheck::make($this->object)->_attr('bar')->_attr('baz')->_exist(),
      'Object exists'
    );

    $this->assertTrue(
      VarCheck::make($this->mixed)->_key('object')->_attr('foo')->_exist(),
      'Mixed exists'
    );
  }

  public function testNotExist() {
    $missing_value = NULL;
    $this->assertFalse(
      VarCheck::make($missing_value)->_exist()
    );

    $this->assertFalse(
      VarCheck::make($this->var)->_attr('foo')->_exist(),
      'Var does not exists'
    );

    $this->assertFalse(
      VarCheck::make($this->var)->_key('foo')->_exist(),
      'Var does not exists'
    );

    $this->assertFalse(
      VarCheck::make($this->var)->_key('foo')->_attr('foo')->_exist(),
      'Var does not exists'
    );

    $this->assertFalse(
      VarCheck::make($this->array)->_key('foo')->_key('foo')->_exist(),
      'Array does not exists'
    );

    $this->assertFalse(
      VarCheck::make($this->array)->_key(123)->_key('foo')->_exist(),
      'Array does not exists'
    );

    $this->assertFalse(
      VarCheck::make($this->object)->_attr('rabbit')->_exist(),
      'Object does not exists'
    );

    $this->assertFalse(
      VarCheck::make($this->object)->_attr('rabbit')->_attr('chicken')->_exist(),
      'Object does not exists'
    );

    $this->assertFalse(
      VarCheck::make($this->mixed)->_attr('object')->_attr('foo')->_exist(),
      'Mixed does not exists'
    );

    $this->assertFalse(
      VarCheck::make($this->mixed)->_key('object')->_key('foo')->_exist(),
      'Mixed does not exists'
    );
  }

  public function testValidationCallback() {
    $this->assertTrue(
      VarCheck::make($this->object)->_attr('bar')->_attr('baz')->_call(function($v) {
        return is_numeric($v);
      }),
      'Object is numeric.'
    );

    $this->assertTrue(
      VarCheck::make($this->mixed)->var->_call(function ($string_a, $string_b) {
        return $string_a === $string_b;
      }, 'foobar')
    );

    $this->assertEquals(
      6,
      VarCheck::make($this->mixed)->var->_call(array('VarCheckFooBar', 'classCharCount'))
    );

    $instance = new VarCheckFooBar();
    $this->assertEquals(
      6,
      VarCheck::make($this->mixed)->var->_call(array($instance, 'instanceCharCount'))
    );

    $this->assertEquals(
      6,
      VarCheck::make($this->mixed)->var->_call('varcheck_foo_bar_char_count')
    );
  }

  public function testValidationCallbackFail() {
    $this->assertFalse(
      VarCheck::make($this->object)->_attr('bar')->_attr('baz')->_call(function($v) {
        return is_string($v);
      }),
      'Object is numeric.'
    );

    $this->assertFalse(
      VarCheck::make($this->mixed)->var->_call(function ($string_a, $string_b) {
        return $string_a === $string_b;
      }, 'foobar_no_match')
    );

    $this->assertNull(
      VarCheck::make($this->mixed)->var->no_var->_call(function ($string_a, $string_b) {
        return $string_a === $string_b;
      }, 'foobar')
    );
  }

  public function testDefaultValue() {
    $default_value = 'foobar';
    $this->assertEquals(VarCheck::make($this->object)->_attr('abc')->_key('not exist')->_value(), FALSE, 'Default value is False if value does not exist.');
    $this->assertEquals(VarCheck::make($this->object)->_attr('abc')->_key('not exist')->_value($default_value), $default_value, 'Default value is defined if value does not exist.');
  }

  public function testNonStaticGeneration() {
    $check = new VarCheck($this->object);
    $check->_attr('bar');
    $check->_attr('baz');
    $this->assertTrue($check->_exist(), 'Value exist');
  }

  public function testMagicGetterWay() {
    $this->assertEquals(VarCheck::make($this->mixed)->var->_value(), 'foobar');
    $this->assertEquals(VarCheck::make($this->mixed)->array->foo->bar->_value(), 1);
    $this->assertEquals(VarCheck::make($this->mixed)->array->{'2'}->_value(), FALSE);
    $this->assertEquals(VarCheck::make($this->mixed)->object->foo->_value(), 'bar');
    $this->assertEquals(VarCheck::make($this->mixed)->object->bar->baz->_value(), 1);

    $this->assertTrue(VarCheck::make($this->mixed)->var->_exist());
    $this->assertTrue(VarCheck::make($this->mixed)->array->foo->bar->_exist());
    $this->assertTrue(VarCheck::make($this->mixed)->array->{'2'}->_exist());
    $this->assertTrue(VarCheck::make($this->mixed)->object->_exist());
    $this->assertTrue(VarCheck::make($this->mixed)->object->bar->_exist());

    $this->assertFalse(VarCheck::make($this->mixed)->var2->_exist());
    $this->assertFalse(VarCheck::make($this->mixed)->array->foo->bar->baz->_exist());
    $this->assertFalse(VarCheck::make($this->mixed)->array->{'5'}->_exist());
    $this->assertFalse(VarCheck::make($this->mixed)->object_fake->_exist());
    $this->assertFalse(VarCheck::make($this->mixed)->object->bar->{'3'}->_exist());
  }

  public function testFunctionCallOnValue() {
    $this->assertEquals(VarCheck::make($this->mixed)->array->foo->bar->_call('min', 2), 1);
    $this->assertEquals(VarCheck::make($this->mixed)->array->foo->bar->_call('min', -2), -2);
    $this->assertEquals(VarCheck::make($this->mixed)->array->foo->bar->_call('max', 2), 2);
    $this->assertEquals(VarCheck::make($this->mixed)->array->foo->bar->_call('max', -2), 1);

    $array_sample = array(
      'foo' => array(1, 2, 3, 4),
    );
    $this->assertEquals(VarCheck::make($array_sample)->foo->_call('count'), 4);

    // Non existent value calls.
    $this->assertNull(VarCheck::make($array_sample)->bar->_call('count'));
  }

  public function testCloning() {
    $item = VarCheck::make($this->mixed);
    $item_clone = clone $item;
    $this->assertEquals($item->var->_value(), 'foobar');
    $this->assertEquals($item_clone->var->_value(), 'foobar');
  }

  public function testCallingInstanceFunction() {
    $foobar = new VarCheckFooBar();
    $foo = array(
      'bar' => $foobar,
    );

    $this->assertEquals(6, VarCheck::make($foo)->bar->instanceCharCount('foobar')->_value(), 'Calling instance function with argument return proper value.');
  }

}

class VarCheckFooBar {

  public function instanceCharCount($foo) {
    return strlen($foo);
  }

  public static function classCharCount($foo) {
    return strlen($foo);
  }

}

function varcheck_foo_bar_char_count($word) {
  return strlen($word);
}

