VarCheck
=========


[![Build Status](https://travis-ci.org/itarato/var-check.png?branch=master)](https://travis-ci.org/itarato/var-check)


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
$output = VarCheck::take($myComplexVar)->key(1)->attr('name')->value($otherwise);
// or even simpler:
$output = VarCheck::take($myComplexVar)->{'1'}->name->value($otherwise);
```


Checking if the nested value exist
----------------------------------

```php
VarCheck::take($myComplexVar)->key(1)->attr('name')->exist(); // TRUE;
// or:
VarCheck::take($myComplexVar)->{'1'}->name->exist(); // TRUE;
```


Get the nested value
--------------------

```php
VarCheck::take($myComplexVar)->key(1)->attr('name')->value(); // John Doe;
```


Call a function on the value if exist
-------------------------------------

```php
// Instead of this:
$value = isset($variable['key']['foo']->element) ? my_function($variable['key']['foo']->element) : NULL;
// Do this:
$value = VarCheck::take($variable)->key->foo->element->my_function();
```


Failsafe check in case it does not exist
----------------------------------------

```php
VarCheck::take($myComplexVar)->key(1)->attr('job')->exist(); // FALSE;
VarCheck::take($myComplexVar)->key(1)->attr('job')->attr('title')->exist(); // FALSE;
```


Check and value at the same time
--------------------------------

```php
if ($value = VarCheck::take($form_status)->key('values')->key('#node')->attr('field_image')->key(LANGUAGE_NONE)->key(0)->key('item')->key('fid')->value()) {
  // Use $value;
}
// or:
if ($value = VarCheck::take($form_status)->values->{'#node'}->field_image->{LANGUAGE_NONE}->{'0'}->item->fid->value()) {
  // Use $value;
}
```


Custom validation
-----------------

```php
VarCheck::take($myVar)->key(3)->attr('title')->validateWith(function ($v) {
  return $v > 10;
});
```
