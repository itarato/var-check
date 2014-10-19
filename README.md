VarCheck
=========


[![Build Status](https://travis-ci.org/itarato/var-check.png?branch=master)](https://travis-ci.org/itarato/var-check)


Changelog
---------

#Version 2
- Instance creation:
```PHP
VarCheck::make($variable);
```
- VarCheck methods got underscore (avoiding real object properties, functions or array keys):
```PHP
VarCheck::make($variable)->_value();
VarCheck::make($variable)->_empty();
```
- ```__call``` magic method now calls the instance function of the current object:
```PHP
class User {
  function getParent() {
    return $this->parent;
  }
}
$child = new User();
VarCheck::make($child)->getParent()->_value();
```


VarCheck is a single class to verify nested complex variable without lots of isset() and exist().

To avoid multiple level of isset/exist/etc this class provides an easy way to verify nested values in a variable.
Typical use case when you have a large variable, and you are not sure if it has the right index, and inside
there an object, and an attribute ...


The complex variable
--------------------

```php
$myComplexVar = array(1 => new stdClass());
$myComplexVar[1]->name = 'John Doe';
```


Problem to solve
----------------

```php
// Get the value:
$output = isset($myComplexVar[1]) && isset($myComplexVar[1]->name) ? $myComplexVar[1]->name : $otherwise;
```


# Solution

```php
$output = VarCheck::make($myComplexVar)->_key(1)->_attr('name')->_value($otherwise);
// or even simpler:
$output = VarCheck::make($myComplexVar)->{'1'}->name->_value($otherwise);
```


Checking if the nested value exist
----------------------------------

```php
VarCheck::make($myComplexVar)->_key(1)->_attr('name')->_exist(); // TRUE;
// or:
VarCheck::make($myComplexVar)->{'1'}->name->_exist(); // TRUE;
```


Get the nested value
--------------------

```php
VarCheck::make($myComplexVar)->_key(1)->_attr('name')->_value(); // John Doe;
```


Call a function on the value if exist
-------------------------------------

```php
// Instead of this:
$value = isset($variable['key']['foo']->element) ? my_function($variable['key']['foo']->element) : NULL;
// Do this:
$value = VarCheck::make($variable)->key->foo->element->my_function();
// Or:
$myClassInstance;
$value = arCheck::make($variable)->key->foo->element->call(array($myClassInstance, 'instanceFunction'));
```


Failsafe check in case it does not exist
----------------------------------------

```php
VarCheck::make($myComplexVar)->_key(1)->_attr('job')->_exist(); // FALSE;
VarCheck::make($myComplexVar)->_key(1)->_attr('job')->_attr('title')->_exist(); // FALSE;
```


Check and value at the same time
--------------------------------

```php
if ($value = VarCheck::make($form_status)->_key('values')->_key('#node')->_attr('field_image')->_key(LANGUAGE_NONE)->_key(0)->_key('item')->_key('fid')->_value()) {
  // Use $value;
}
// or:
if ($value = VarCheck::make($form_status)->values->{'#node'}->field_image->{LANGUAGE_NONE}->{'0'}->item->fid->_value()) {
  // Use $value;
}
```


Custom validation
-----------------

```php
VarCheck::make($myVar)->_key(3)->_attr('title')->_call(function ($v) {
  return $v > 10;
});
```
