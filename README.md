VarCheck
=========


[![Build Status](https://travis-ci.org/itarato/var-check.png?branch=master)](https://travis-ci.org/itarato/var-check)


VarCheck is a single class to verify nested complex variable without lots of isset() and exist().

To avoid multiple level of isset/exist/etc this class provides an easy way to verify nested values in a variable.
Typical use case when you have a large variable, and you are not sure if it has the right index, and inside
there an object, and an attribute ...


Problem to solve
----------------


#Solution


The complex variable
--------------------

    ```php
    $myComplexVar = array(1 => new stdClass());
    $myComplexVar[1]->name = 'John Doe';
    ```


Checking if the nested value exist
----------------------------------
<pre>
VarCheck::instance($myComplexVar)->key(1)->attr('name')->exist(); // TRUE;
</pre>

Get the nested value
--------------------
<pre>
VarCheck::instance($myComplexVar)->key(1)->attr('name')->value(); // John Doe;
</pre>

Failsafe check in case it does not exist
----------------------------------------
<pre>
VarCheck::instance($myComplexVar)->key(1)->attr('job')->exist(); // FALSE;
VarCheck::instance($myComplexVar)->key(1)->attr('job')->attr('title')->exist(); // FALSE;
</pre>

Check and value at the same time
--------------------------------
<pre>
if ($value = VarCheck::instance($form_status)->key('values')->key('#node')->attr('field_image')->key(LANGUAGE_NONE)->key(0)->key('item')->key('fid')->value()) {
  // Use $value;
}
</pre>

Custom validation
-----------------
<pre>
VarCheck::instance($myVar)->key(3)->attr('title')->validateWith(function($v) {
  return $v > 10;
});
</pre>

Tests are available.