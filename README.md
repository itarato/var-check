VarCheck
=========

VarCheck is a single class to verify nested complex variable without lots of isset() and exist().

To avoid multiple level of isset/exist/etc this class provides an easy way to verify nested values in a variable.
Typical use case when you have a large variable, and you are not sure if it has the right index, and inside
there an object, and an attribute ...

Usage:

<pre>
// The complex variable:
$myComplexVar = array(1 => new stdClass());
$myComplexVar[1]->name = 'John Doe';

// Checking if the nested value exist:
VarCheck::instance($myComplexVar)->key(1)->attr('name')->exist(); // TRUE;
// Get the nested value:
VarCheck::instance($myComplexVar)->key(1)->attr('name')->value(); // John Doe;
// Failsafe check in case it does not exist:
VarCheck::instance($myComplexVar)->key(1)->attr('job')->exist(); // FALSE;
VarCheck::instance($myComplexVar)->key(1)->attr('job')->attr('title')->exist(); // FALSE;

// Check and value at the same time:
if ($value = VarCheck::instance($form_status)->key('values')->key('#node')->attr('field_image')->key(LANGUAGE_NONE)->key(0)->key('item')->key('fid')->value()) {
  // Use $value;
}

// Custom validation:
VarCheck::instance($myVar)->key(3)->attr('title')->validateWith(function($v) {
  return $v > 10;
});
</pre>