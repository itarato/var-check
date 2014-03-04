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
      VarCheck::take($this->object)->attr('bar')->attr('baz')->validateWith(function($v) {
        return is_numeric($v);
      }),
      'Object is numeric.'
    );
  }

  public function testValidationCallbackFail() {
    $this->assertFalse(
      VarCheck::take($this->object)->attr('bar')->attr('baz')->validateWith(function($v) {
        return is_string($v);
      }),
      'Object is numeric.'
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

}
