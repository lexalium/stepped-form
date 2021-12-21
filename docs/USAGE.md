# Usage

1. Create a Step.

```php
<?php

use Lexal\SteppedForm\Steps\StepInterface;

class CustomerStep implements StepInterface
{
    public function handle(mixed $entity, mixed $data): mixed
    {
        // do some logic here
        
        return $entity; // return entity that form will set into the storage
    }
}
```

2. Create a [Builder](FORM_BUILDER.md) class which will build and return a
      StepsCollection instance.

```php
<?php

use Lexal\SteppedForm\Builder\FormBuilderInterface;
use Lexal\SteppedForm\Steps\Builder\StepsBuilder;
use Lexal\SteppedForm\Steps\Builder\StepsBuilderInterface;
use Lexal\SteppedForm\Steps\Collection\StepsCollection;

class CustomBuilder implements FormBuilderInterface
{
    public function __construct(
        private StepsBuilderInterface $builder,
    ) {
    }

    public function build(mixed $entity): StepsCollection
    {
        $this->builder->add('key', new CustomerStep());
        // Some additional steps
        
        return $this->builder->get();
    }
}

$builder = new CustomBuilder(new StepsBuilder());
```

3. Create a [Form State](FORM_STATE.md) instance.

```php
<?php

use Lexal\SteppedForm\Data\FormDataStorage;
use Lexal\SteppedForm\Data\StepControl;
use Lexal\SteppedForm\State\FormState;
use Lexal\SteppedForm\Data\Storage\ArrayStorage;

$formState = new FormState(
    new FormDataStorage(new ArrayStorage()),
    new StepControl(new ArrayStorage()),
);
```

4. Create an Event Dispatcher.

```php
<?php

use Lexal\SteppedForm\EventDispatcher\EventDispatcherInterface;

class EventDispatcher implements EventDispatcherInterface
{
    public function dispatch(object $event): object
    {
        // dispatch events here
        
        return $event;
    }
}

$dispatcher = new EventDispatcher();
```

5. Create a Stepped Form instance.

```php
<?php

use Lexal\SteppedForm\SteppedForm;

$form = new SteppedForm(
    $formState,
    $builder,
    $dispatcher,
);
```

6. Use Stepped Form in the application.

```php
<?php

/* Starts a new form session */
$form->start(/* entity for initialize a form state */);

/* Returns a TemplateDefinition of rendered step */
$form->render('key');

/* Handles a step logic a saves a new form state */
$form->handle('key', /* any submitted data */);

/* Cancels form session */
$form->cancel();
```
