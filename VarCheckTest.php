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
      VarCheck::take($this->var)->value(),
      'foobar',
      'Value is foobar'
    );

    $this->assertEquals(
      VarCheck::take($this->array)->key('foo')->key('bar')->value(),
      1,
      'Array is 1'
    );

    $this->assertEquals(
      VarCheck::take($this->object)->attr('bar')->attr('baz')->value(),
      1,
      'Object is 1'
    );

    $this->assertEquals(
      VarCheck::take($this->mixed)->key('object')->attr('foo')->value(),
      'bar',
      'Mixed is bar'
    );
  }

  public function testExist() {
    $this->assertTrue(
      VarCheck::take($this->var)->exist(),
      'Var exists'
    );

    $this->assertTrue(
      VarCheck::take($this->array)->key('foo')->key('bar')->exist(),
      'Array exists'
    );

    $this->assertTrue(
      VarCheck::take($this->object)->attr('bar')->attr('baz')->exist(),
      'Object exists'
    );

    $this->assertTrue(
      VarCheck::take($this->mixed)->key('object')->attr('foo')->exist(),
      'Mixed exists'
    );
  }

  public function testNotExist() {
    $missing_value = NULL;
    $this->assertFalse(
      VarCheck::take($missing_value)->exist()
    );

    $this->assertFalse(
      VarCheck::take($this->var)->attr('foo')->exist(),
      'Var does not exists'
    );

    $this->assertFalse(
      VarCheck::take($this->var)->key('foo')->exist(),
      'Var does not exists'
    );

    $this->assertFalse(
      VarCheck::take($this->var)->key('foo')->attr('foo')->exist(),
      'Var does not exists'
    );

    $this->assertFalse(
      VarCheck::take($this->array)->key('foo')->key('foo')->exist(),
      'Array does not exists'
    );

    $this->assertFalse(
      VarCheck::take($this->array)->key(123)->key('foo')->exist(),
      'Array does not exists'
    );

    $this->assertFalse(
      VarCheck::take($this->object)->attr('rabbit')->exist(),
      'Object does not exists'
    );

    $this->assertFalse(
      VarCheck::take($this->object)->attr('rabbit')->attr('chicken')->exist(),
      'Object does not exists'
    );

    $this->assertFalse(
      VarCheck::take($this->mixed)->attr('object')->attr('foo')->exist(),
      'Mixed does not exists'
    );

    $this->assertFalse(
      VarCheck::take($this->mixed)->key('object')->key('foo')->exist(),
      'Mixed does not exists'
    );
  }

  public function testValidationCallback() {
    $this->assertTrue(
      VarCheck::take($this->object)->attr('bar')->attr('baz')->call(function($v) {
        return is_numeric($v);
      }),
      'Object is numeric.'
    );

    $this->assertTrue(
      VarCheck::take($this->mixed)->var->call(function ($string_a, $string_b) {
        return $string_a === $string_b;
      }, 'foobar')
    );

    $this->assertEquals(
      6,
      VarCheck::take($this->mixed)->var->call(array('VarCheckFooBar', 'classCharCount'))
    );

    $instance = new VarCheckFooBar();
    $this->assertEquals(
      6,
      VarCheck::take($this->mixed)->var->call(array($instance, 'instanceCharCount'))
    );

    $this->assertEquals(
      6,
      VarCheck::take($this->mixed)->var->call('varcheck_foo_bar_char_count')
    );

    $this->assertEquals(
      6,
      VarCheck::take($this->mixed)->var->varcheck_foo_bar_char_count()
    );
  }

  public function testValidationCallbackFail() {
    $this->assertFalse(
      VarCheck::take($this->object)->attr('bar')->attr('baz')->call(function($v) {
        return is_string($v);
      }),
      'Object is numeric.'
    );

    $this->assertFalse(
      VarCheck::take($this->mixed)->var->call(function ($string_a, $string_b) {
        return $string_a === $string_b;
      }, 'foobar_no_match')
    );

    $this->assertNull(
      VarCheck::take($this->mixed)->var->no_var->call(function ($string_a, $string_b) {
        return $string_a === $string_b;
      }, 'foobar')
    );
  }

  public function testDefaultValue() {
    $default_value = 'foobar';
    $this->assertEquals(VarCheck::take($this->object)->attr('abc')->key('not exist')->value(), FALSE, 'Default value is False if value does not exist.');
    $this->assertEquals(VarCheck::take($this->object)->attr('abc')->key('not exist')->value($default_value), $default_value, 'Default value is defined if value does not exist.');
  }

  public function testNonStaticGeneration() {
    $check = new VarCheck($this->object);
    $check->attr('bar');
    $check->attr('baz');
    $this->assertTrue($check->exist(), 'Value exist');
  }

  public function testMagicGetterWay() {
    $this->assertEquals(VarCheck::take($this->mixed)->var->value(), 'foobar');
    $this->assertEquals(VarCheck::take($this->mixed)->array->foo->bar->value(), 1);
    $this->assertEquals(VarCheck::take($this->mixed)->array->{'2'}->value(), FALSE);
    $this->assertEquals(VarCheck::take($this->mixed)->object->foo->value(), 'bar');
    $this->assertEquals(VarCheck::take($this->mixed)->object->bar->baz->value(), 1);

    $this->assertTrue(VarCheck::take($this->mixed)->var->exist());
    $this->assertTrue(VarCheck::take($this->mixed)->array->foo->bar->exist());
    $this->assertTrue(VarCheck::take($this->mixed)->array->{'2'}->exist());
    $this->assertTrue(VarCheck::take($this->mixed)->object->exist());
    $this->assertTrue(VarCheck::take($this->mixed)->object->bar->exist());

    $this->assertFalse(VarCheck::take($this->mixed)->var2->exist());
    $this->assertFalse(VarCheck::take($this->mixed)->array->foo->bar->baz->exist());
    $this->assertFalse(VarCheck::take($this->mixed)->array->{'5'}->exist());
    $this->assertFalse(VarCheck::take($this->mixed)->object_fake->exist());
    $this->assertFalse(VarCheck::take($this->mixed)->object->bar->{'3'}->exist());
  }

  public function testFunctionCallOnValue() {
    $this->assertEquals(VarCheck::take($this->mixed)->array->foo->bar->min(2), 1);
    $this->assertEquals(VarCheck::take($this->mixed)->array->foo->bar->min(-2), -2);
    $this->assertEquals(VarCheck::take($this->mixed)->array->foo->bar->max(2), 2);
    $this->assertEquals(VarCheck::take($this->mixed)->array->foo->bar->max(-2), 1);

    $array_sample = array(
      'foo' => array(1, 2, 3, 4),
    );
    $this->assertEquals(VarCheck::take($array_sample)->foo->count(), 4);

    // Non existent value calls.
    $this->assertNull(VarCheck::take($array_sample)->bar->count());
  }

  public function testCloning() {
    $item = VarCheck::take($this->mixed);
    $item_clone = clone $item;
    $this->assertEquals($item->var->value(), 'foobar');
    $this->assertEquals($item_clone->var->value(), 'foobar');
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

