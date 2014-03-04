/**
 * @file
 * JavaScript implementation of VarCheck.
 */

/**
 * Provides chained facility to check if a nested attribute exists, and returns the value.
 *
 * @param value
 *  Root of the variable.
 * @returns {{key: Function, value: Function}}
 *  Object for chaining. [key] to go a level deeper in the variable. [value] to extract the actual value.
 * @constructor
 *
 * Usage:
 * @code
 * var obj = {
 *   foo: [1, 2, 3],
 *   bar: {}
 * };
 * var_check(obj).key('foo').key(1).value(); // 2
 * var_check(obj).key('foo').key(6).value(); // undefined
 * var_check(obj).key('bar').value(); // {}
 * var_check(obj).key('foobar').key(1).value(); // undefined
 * var_check(obj).key(1).key('foo').key('foo').value(); // undefined
 * @endcode
 */
var_check = function (value) {
  return {
    key: function (attr) {
      return value && value.hasOwnProperty(attr) ? var_check(value[attr]) : var_check(undefined);
    },
    exist: function () {
      return value !== undefined;
    },
    value: function () {
      return value;
    }
  };
};
