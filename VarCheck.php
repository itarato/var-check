<?php
/**
 * @file
 * VarCheck class file.
 */

/**
 * Class VarCheck
 * Nested variable validator.
 *
 * To avoid multiple level of isset/exist/etc this class provides an easy way to verify nested values in a variable.
 * Typical use case when you have a large variable, and you are not sure if it has the right index, and inside
 * there an object, and an attribute ...
 *
 * Usage:
 * $myComplexVar = array(1 => new stdClass());
 * $myComplexVar[1]->name = 'John Doe';
 * VarCheck::instance($myComplexVar)->key(1)->attr('name')->exist(); // TRUE;
 * VarCheck::instance($myComplexVar)->key(1)->attr('name')->value(); // John Doe;
 * VarCheck::instance($myComplexVar)->key(1)->attr('job')->exist(); // FALSE;
 * VarCheck::instance($myComplexVar)->key(1)->attr('job')->attr('title')->exist(); // FALSE;
 */
class VarCheck {

  /**
   * The internal variable.
   *
   * @var Mixed
   */
  private $value;

  /**
   * Constructor.
   * Can be called directly, or just simply through: VarCheck::instance($value);
   *
   * @param Mixed $value
   *  Variable.
   */
  public function __construct($value) {
    $this->value = $value;
  }

  /**
   * Getting a quick VarCheck instance.
   *
   * @param Mixed $value
   *  Variable.
   * @return VarCheck
   *  Instance object.
   */
  public static function instance($value) {
    return new VarCheck($value);
  }

  /**
   * Check if the value exist.
   *
   * @return bool
   *  TRUE if the value exist and different from NULL.
   */
  public function exist() {
    return isset($this->value);
  }

  /**
   * Calls an attribute of the object value.
   *
   * @param String|Integer $attr
   *  Attribute string.
   * @return VarCheck $this
   *  Instance.
   */
  public function attr($attr) {
    if (isset($this->value) && is_object($this->value) && isset($this->value->{$attr})) {
      $this->value = $this->value->{$attr};
    }
    else {
      unset($this->value);
    }
    return $this;
  }

  /**
   * Calls a key of the array value.
   *
   * @param String|Integer $key
   *  Key string.
   * @return VarCheck $this
   *  Instance.
   */
  public function key($key) {
    if (isset($this->value) && is_array($this->value) && isset($this->value[$key])) {
      $this->value = $this->value[$key];
    }
    else {
      unset($this->value);
    }
    return $this;
  }

  /**
   * Returns the value.
   *
   * @return Mixed
   *  Value.
   */
  public function value() {
    return $this->value;
  }

}
