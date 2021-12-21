# Form Builder

---

The Stepped Form uses a Form Builder for building a Steps Collection by 
the form entity.

If a Steps Collection will always have a fixed list of steps you can use a
`Lexal\SteppedForm\Builder\StaticFormBuilder` builder. For example:

```php
<?php

use Lexal\SteppedForm\Builder\StaticFormBuilder;
use Lexal\SteppedForm\SteppedForm;
use Lexal\SteppedForm\Steps\Collection\Step;
use Lexal\SteppedForm\Steps\Collection\StepsCollection;

$collection = new StepsCollection([
    new Step('key', new CustomerStep()),
    /* some more steps */
]);

$builder = new StaticFormBuilder($collection);

$form = new SteppedForm(
    /* FormStateInterface */,
    $builder,
    /* EventDispatcherInterface */,
);
```

See [interface definition](../src/Builder/FormBuilderInterface.php)
for more details.