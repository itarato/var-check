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
  public static function make($value) {
    return new VarCheck($value);
  }

  /**
   * Check if the value exist.
   *
   * @return bool
   *  TRUE if the value exist and different from NULL.
   */
  public function _exist() {
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
  public function _attr($attr) {
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
  public function _key($key) {
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
  public function _value($default_value = FALSE) {
    return isset($this->value) ? $this->value : $default_value;
  }

  /**
   * Universal function applicator.
   *
   * @param callable $callback
   *  Custom function to call with the value (and arguments in it).
   * @param mixed
   *  All additional arguments go to the validator.
   *
   * @return mixed
   */
  public function _call($callback) {
    if (!$this->_exist()) {
      return NULL;
    }

    $extra_arguments = func_get_args();
    array_shift($extra_arguments);
    array_unshift($extra_arguments, $this->value);

    return call_user_func_array($callback, $extra_arguments);
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

  /**
   * Magic __call() method.
   * Used to call a function on the stored value - if exist.
   *
   * @param $name
   *  Name of the called function.
   * @param array $arguments
   *  Extra arguments for the function.
   * @return mixed|null
   *  The return value of the called function, or NULL if the value is invalid.
   */
  public function __call($name, array $arguments = array()) {
    // No value - call nothing.
    if (
      !$this->_exist() ||
      !method_exists($this->value, $name)
    ) {
      return $this;
    }

    $this->value = call_user_func_array(array($this->value, $name), $arguments);
    return $this;
  }

}
