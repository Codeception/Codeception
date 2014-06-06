
## Codeception\Util\Template



Basic template engine used for generating initial Cept/Cest/Test files.


#### *public* __construct($template) 

Takes a template string

 * `param`  $template

[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Template.php#L17)

#### *public* place($var, $val) 

Replaces {{var}} string with provided value

 * `param`  $var
 * `param`  $val
 * `return`  $this

[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Template.php#L29)

#### *public* produce() 

Fills up template string with placed variables.

 * `return`  mixed

[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Template.php#L40)

