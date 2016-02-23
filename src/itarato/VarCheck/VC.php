<?php
/**
 * @file
 *
 * VC class file.
 */

namespace itarato\VarCheck;

use itarato\VarCheck\Exception\NoBackupException;

/**
 * Class VC
 * Nested variable validator.
 *
 * To avoid multiple level of isset/exist/etc this class provides an easy way to verify nested values in a variable.
 * Typical use case when you have a large variable, and you are not sure if it has the right index, and inside
 * there an object, and an attribute ...
 *
 * Usage:
 * $myComplexVar = array(1 => new stdClass());
 * $myComplexVar[1]->name = 'John Doe';
 * VC::make($myComplexVar)->key(1)->attr('name')->_exist(); // TRUE;
 * VC::make($myComplexVar)->key(1)->attr('name')->_value(); // John Doe;
 * VC::make($myComplexVar)->key(1)->attr('job')->_exist(); // FALSE;
 * VC::make($myComplexVar)->{1}->job->title->_exist(); // FALSE;
 *
 * Another example shows the simple way of accessing elements/methods by via "native" name"
 * VC::make($acmeObject)->job->setTitle('new title')->getTitle()->_value();
 */
class VC {

  /**
   * The internal variable.
   *
   * @var Mixed
   */
  private $value;

  /**
   * Backup storage. Used to store values for later restore and reuse.
   *
   * @var array
   */
  private $backups = array();

  /**
   * Constructor.
   * Can be called directly, or just simply through: VC::make($value);
   *
   * @param Mixed $value
   *  Variable.
   */
  public function __construct($value) {
    $this->value = $value;
  }

  /**
   * Getting a quick VC instance.
   *
   * @param Mixed $value
   *  Variable.
   * 
   * @return VC
   *  Instance object.
   */
  public static function make($value) {
    return new VC($value);
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
   * @return VC
   *  Instance.
   */
  public function _attr($attr) {
    if (isset($this->value) && is_object($this->value) && isset($this->value->{$attr})) {
      $this->value = $this->value->{$attr};
    }
    else {
      $this->_unset();
    }
    return $this;
  }

  /**
   * Calls a key of the array value.
   *
   * @param String|Integer $key
   *  Key string.
   *
   * @return VC
   *  Self instance.
   */
  public function _key($key) {
    if (isset($this->value) && is_array($this->value) && isset($this->value[$key])) {
      $this->value = $this->value[$key];
    }
    else {
      $this->_unset();
    }
    return $this;
  }

  /**
   * Returns the value.
   * Any passed value will serve as a default return value.
   * In case there is no argument and the value does not exit it throws an exception.
   *
   * @param null $default
   *  Default value when expected return is missing/invalid.
   * @return mixed
   */
  public function _value($default = NULL) {
    if ($this->_exist()) {
      return $this->value;
    }

    return $default;
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
   * @return VC
   *  Self instance.
   */
  public function __get($key) {
    if (isset($this->value) && is_object($this->value) && isset($this->value->{$key})) {
      $this->value = $this->value->{$key};
    }
    elseif (isset($this->value) && is_array($this->value) && array_key_exists($key, $this->value)) {
      $this->value = $this->value[$key];
    }
    else {
      $this->_unset();
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
      $this->_unset();
      return $this;
    }

    $this->value = call_user_func_array(array($this->value, $name), $arguments);
    return $this;
  }

  /**
   * Only keeps value if the value is an instance of a given class.
   *
   * @param string $class
   *  Class to check against
   * @return VC
   */
  public function _ifInstanceOf($class) {
    if (!$this->_exist()) {
      return $this;
    }

    if (!($this->value instanceof $class)) {
      $this->_unset();
    }

    return $this;
  }

  /**
   * Only keeps value if the value is a subclass of a given class.
   *
   * @param string $class
   *  Class to check against
   * @return VC
   */
  public function _ifSubclassOf($class) {
    if (!$this->_exist()) {
      return $this;
    }

    if (!is_subclass_of($this->value, $class)) {
      $this->_unset();
    }

    return $this;
  }

  /**
   * Unset stored value.
   *
   * @return VC
   */
  private function _unset() {
    unset($this->value);
    return $this;
  }

  /**
   * Save current value into a stack.
   *
   * @return VC
   */
  public function _backupPush() {
    $this->backups[] = $this->value;
    return $this;
  }

  /**
   * Restore the latest saved value.
   *
   * @return VC
   * @throws \itarato\VarCheck\Exception\NoBackupException
   */
  public function _backupPop() {
    if (count($this->backups) == 0) {
      throw new NoBackupException();
    }

    $this->value = array_pop($this->backups);
    return $this;
  }

}
