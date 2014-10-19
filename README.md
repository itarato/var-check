VarCheck
=========


[![Build Status](https://travis-ci.org/itarato/var-check.png?branch=master)](https://travis-ci.org/itarato/var-check)

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/itarato/var-check/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/itarato/var-check/?branch=master)


Changelog
---------

#Version 2
- Use of PSR-4 autoading:
```PHP
require_once __DIR__ . '/vendor/autoload.php';
use itarato\VarCheck\VC;
```
- Instance creation:
```PHP
VC::make($variable);
```
- VarCheck methods got underscore (avoiding real object properties, functions or array keys):
```PHP
VC::make($variable)->_value();
VC::make($variable)->_empty();
```
- ```__call``` magic method now calls the instance function of the current object:
```PHP
class User {
  function getParent() {
    return $this->parent;
  }
}
$child = new User();
VC::make($child)->getParent()->_value();
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
$output = VC::make($myComplexVar)->_key(1)->_attr('name')->_value($otherwise);
// or even simpler:
$output = VC::make($myComplexVar)->{'1'}->name->_value($otherwise);
```


Checking if the nested value exist
----------------------------------

```php
VC::make($myComplexVar)->_key(1)->_attr('name')->_exist(); // TRUE;
// or:
VC::make($myComplexVar)->{'1'}->name->_exist(); // TRUE;
```


Get the nested value
--------------------

```php
VC::make($myComplexVar)->_key(1)->_attr('name')->_value(); // John Doe;
```


Call a function on the value if exist
-------------------------------------

```php
// Instead of this:
$value = isset($variable['key']['foo']->element) ? my_function($variable['key']['foo']->element) : NULL;
// Do this:
$value = VC::make($variable)->key->foo->element->my_function();
// Or:
$myClassInstance;
$value = arCheck::make($variable)->key->foo->element->call(array($myClassInstance, 'instanceFunction'));
```


Failsafe check in case it does not exist
----------------------------------------

```php
VC::make($myComplexVar)->_key(1)->_attr('job')->_exist(); // FALSE;
VC::make($myComplexVar)->_key(1)->_attr('job')->_attr('title')->_exist(); // FALSE;
```


Check and value at the same time
--------------------------------

```php
if ($value = VC::make($form_status)->_key('values')->_key('#node')->_attr('field_image')->_key(LANGUAGE_NONE)->_key(0)->_key('item')->_key('fid')->_value()) {
  // Use $value;
}
// or:
if ($value = VC::make($form_status)->values->{'#node'}->field_image->{LANGUAGE_NONE}->{'0'}->item->fid->_value()) {
  // Use $value;
}
```


Custom validation
-----------------

```php
VC::make($myVar)->_key(3)->_attr('title')->_call(function ($v) {
  return $v > 10;
});
```
