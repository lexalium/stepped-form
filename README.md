# PHP Implementation of the Stepped Form

[![PHPUnit, PHPCS, PHPStan Tests](https://github.com/alexxxxkkk/stepped-form/actions/workflows/tests.yml/badge.svg)](https://github.com/alexxxxkkk/stepped-form/actions/workflows/tests.yml)

With this package you can create a Stepped Form and render or handle
its steps.

<a id="readme-top" mame="readme-top"></a>

Table of Contents

1. [Requirements](#requirements)
2. [Installation](#installation)
3. [Usage](#usage)
   - [Step](#step)
   - [Form Builder](#form-builder)
   - [Form Data Storage](#form-data-storage)
   - [Entity Copy](#entity-copy)
4. [License](#license)

---

## Requirements

**PHP:** >=8.1

## Installation

Via Composer

```
composer require lexal/stepped-form
```

## Usage

1. Create a Step.
    ```php
    use Lexal\SteppedForm\Step\StepInterface;
    
    final class CustomerStep implements StepInterface
    {
        public function handle(mixed $entity, mixed $data): mixed
        {
            // do some logic here
            
            return $entity; // returns an entity that form will save as step data into the storage
        }
    }
    ```

2. Create a Form Builder.
    ```php
    use Lexal\SteppedForm\Form\Builder\FormBuilderInterface;
    use Lexal\SteppedForm\Step\Builder\StepsBuilder;
    use Lexal\SteppedForm\Step\Builder\StepsBuilderInterface;
    use Lexal\SteppedForm\Step\Steps;
    
    final class CustomBuilder implements FormBuilderInterface
    {
        public function __construct(private readonly StepsBuilderInterface $builder)
        {
        }

        public function build(mixed $entity): Steps
        {
            $this->builder->add('customer', new CustomerStep());
            // Some additional steps

            return $this->builder->get();
        }
    }

    $builder = new CustomBuilder(new StepsBuilder(/* StepControlInterface */, /* DataControlInterface */));
    ```

3. Create a Storage and data controllers.
    ```php
    use Lexal\SteppedForm\Form\DataControl;
    use Lexal\SteppedForm\Form\StepControl;
    use Lexal\SteppedForm\Form\Storage\ArrayStorage;
    use Lexal\SteppedForm\Form\Storage\DataStorage;

    $storage = new ArrayStorage(); // can use any other storage (session, database, redis, etc.)
    $stepControl = new StepControl($storage);
    $dataControl = new DataControl(new DataStorage($storage));
    ```

4. Create an Event Dispatcher.
    ```php
    use Lexal\SteppedForm\EventDispatcher\EventDispatcherInterface;
   
    final class EventDispatcher implements EventDispatcherInterface
    {
        public function dispatch(object $event): object
        {
            // dispatch events here

            return $event;
        }
    }

    $dispatcher = new EventDispatcher();
    ```

5. Create a Stepped Form.
    ```php
    use Lexal\SteppedForm\EntityCopy\SimpleEntityCopy;
    use Lexal\SteppedForm\SteppedForm;

    $form = new SteppedForm(
        $dataControl,
        $stepControl,
        $storage,
        $builder,
        $dispatcher,
        new SimpleEntityCopy(),
    );
    ```

6. Use Stepped Form in the application.
    ```php
    /* Starts a new form session */
    $form->start(/* entity for initialize a form state */);

    /* Returns a TemplateDefinition of rendered step */
    $form->render('key');

    /* Handles a step logic and saves a new form state */
    $form->handle('key', /* any submitted data */);

    /* Cancels form session */
    $form->cancel();
    ```

<div style="text-align: right">(<a href="#readme-top">back to top</a>)</div>

## Step

Step can render any information to user with different types of input or just
do calculations on backend depending on previous user input.

The first type of step must implement `RenderStepInterface` interface. Method
`getTemplateDefinition` must return `TemplateDefinition` with template name to
render and data to pass to template, e.g.:

```php
use Lexal\SteppedForm\Step\RenderStepInterface;
use Lexal\SteppedForm\Step\Steps;
use Lexal\SteppedForm\Step\TemplateDefinition;
    
final class CustomerStep implements RenderStepInterface
{
    public function getTemplateDefinition(mixed $entity, Steps $steps): TemplateDefinition
    {
        return new TemplateDefinition('customer', ['customer' => $entity]);
    }

    public function handle(mixed $entity, mixed $data): mixed
    {
        // do some logic here
        $entity->name = $data['name'];
        $entity->amount = (float)$data['amount'];

        return $entity; // returns an entity that form will save as step data into the storage
    }
}
```

The second type of step must implement `StepInterface`. Method `handle` can have
business logic by calculating data and must return an updated form entity. Method
will receive a `null` or a previous renderable step data as a second argument.

```php
use Lexal\SteppedForm\Step\StepInterface;
    
final class TaxStep implements StepInterface
{
    private const TAX_PERCENT = 20;

    public function handle(mixed $entity, mixed $data): mixed
    {
        // do some logic here
        $entity->tax = $entity->amount * self::TAX_PERCENT;

        return $entity; // returns an entity that form will save as step data into the storage
    }
}
```

<div style="text-align: right">(<a href="#readme-top">back to top</a>)</div>

## Form Builder

The Stepped Form uses a Form Builder for building a Steps collection by
the form entity.

Stepped Form can have a fixed count of steps or different steps depending
on previous user input data.

Example of Stepped Form with fixed list of steps:
```php
use Lexal\SteppedForm\Form\Builder\StaticStepsFormBuilder;
use Lexal\SteppedForm\Step\Step;
use Lexal\SteppedForm\Step\Steps;

$steps = new Steps([
    new Step('customer', new CustomerStep()),
    new Step('broker', new BrokerStep()),
    /* some more steps */
]);

$builder = new StaticStepsFormBuilder($steps);
```

Example of Stepped Form with dynamic list of steps depending on previous user input:
```php
use Lexal\SteppedForm\Form\Builder\FormBuilderInterface;
use Lexal\SteppedForm\Step\Builder\StepsBuilderInterface;

final class CustomBuilder implements FormBuilderInterface
{
    public function __construct(private readonly StepsBuilderInterface $builder)
    {
    }

    public function build(mixed $entity): Steps
    {
        $this->builder->add('customer', new CustomerStep());

        // add step depending on previous user input
        if ($entity->createNewBroker) {
            $this->builder->add('broker', new BrokerStep());
        }

        // Some additional steps

        return $this->builder->get();
    }
}
```

> **Note:** Step key must have only "A-z", "0-9", "-" and "_".

<div style="text-align: right">(<a href="#readme-top">back to top</a>)</div>

## Form Data Storage

Stepped Form uses a storage to store current step key and handled steps data.
The package has implementation of simple in-memory storage (ArrayStorage). To create
your own storage (e.g. session, database, redis) implement `StorageInterface` interface.

`StepControlInterface` and `DataControlInterface` help to work with current step key
and step data respectively.

Dynamic stepped form will trigger clearing all steps data after current one when handle step.
Steps data are not cleared from the storage for static forms or when current step implements
`StepBehaviourInterface` and method `forgetDataAfterCurrent` returns `false`.

<div style="text-align: right">(<a href="#readme-top">back to top</a>)</div>

## Entity Copy

The Stepped Form uses an Entity Copy for passing previous submitted entity copy to the step `handle`
method and saving it to the storage.

The package already has `SimpleEntityCopy` implementation of `EntityCopyInterface`. But you have to
implement `__clone` method to clone internal objects if you use objects as a form entity.

Alternative for the package `SimpleEntityCopy` is `DeepClone`.

<div style="text-align: right">(<a href="#readme-top">back to top</a>)</div>

## Form Events

The form can dispatch the following events:
1. **BeforeHandleStep** - will dispatch before step handling. Event contains data passed to the handle
   method, form entity, and step instance. Event listener can update event data after some validation.
2. **FormFinished** - will dispatch when there is no next step after handling current one. Event contains
   form entity.

<div style="text-align: right">(<a href="#readme-top">back to top</a>)</div>

## Form Exceptions

The form can dispatch the following exceptions:
1. **AlreadyStartedException** - when trying to start already started form.
2. **EntityNotFoundException** - when previous step entity not found for rendering or handling step.
3. **EventDispatcherException** - on dispatching events.
4. **FormIsNotStartedException** - when trying to render, handle or cancel not started form.
5. **NoStepsAddedException** - when trying to start form without steps.
6. **StepHandleException** - on handling step.
7. **StepIsNotSubmittedException** - when one of the previous step is not submitted.
8. **StepNotFoundException** - when trying to render or handle not existed step.
9. **StepNotRenderableException** - when trying to render not renderable step.

<div style="text-align: right">(<a href="#readme-top">back to top</a>)</div>

---

## License

Stepped Form is licensed under the MIT License. See [LICENSE](LICENSE) for the full license text.