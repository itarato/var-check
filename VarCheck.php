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
 * VarCheck::take($myComplexVar)->key(1)->attr('name')->exist(); // TRUE;
 * VarCheck::take($myComplexVar)->key(1)->attr('name')->value(); // John Doe;
 * VarCheck::take($myComplexVar)->key(1)->attr('job')->exist(); // FALSE;
 * VarCheck::take($myComplexVar)->key(1)->attr('job')->attr('title')->exist(); // FALSE;
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
   * Can be called directly, or just simply through: VarCheck::take($value);
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
   * 
   * @return VarCheck
   *  Instance object.
   */
  public static function take($value) {
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
   *
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
   *
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
   * @param mixed $default_value
   *  Value to return if the original value does not exist.
   *
   * @return Mixed
   *  Value.
   */
  public function value($default_value = FALSE) {
    return isset($this->value) ? $this->value : $default_value;
  }

  /**
   * Universal validator.
   *
   * @param Closure $callback
   *  Custom function to for value validation. Has to return a BOOL value.
   *
   * @return Bool
   *  Validation success.
   */
  public function validateWith(Closure $callback) {
    return $callback($this->value);
  }

  /**
   * Magic getter to make accessing properties even simpler.
   * Because of the limitations of the magic getter we try to guess if it's an array or object.
   * Key
   *
   * @param $key
   * @return $this
   */
  public function __get($key) {
    if (isset($this->value) && is_object($this->value) && isset($this->value->{$key})) {
      $this->value = $this->value->{$key};
    }
    elseif (isset($this->value) && is_array($this->value) && array_key_exists($key, $this->value)) {
      $this->value = $this->value[$key];
    }
    else {
      unset($this->value);
    }
    return $this;
  }

}
