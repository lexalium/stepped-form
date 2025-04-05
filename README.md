# PHP Implementation of the Stepped Form

[![PHPUnit, PHPCS, PHPStan Tests](https://github.com/lexalium/stepped-form/actions/workflows/tests.yml/badge.svg)](https://github.com/lexalium/stepped-form/actions/workflows/tests.yml)

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
        public function handle(object $entity, mixed $data): object
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

        public function build(object $entity): Steps
        {
            $this->builder->add('customer', new CustomerStep());
            // Some additional steps

            return $this->builder->get();
        }
    }

    $builder = new CustomBuilder(new StepsBuilder(/* StepControlInterface */, /* DataControlInterface */));
    ```

3. Create a storage to save current form session key and have ability to split one form into different sessions
   depending on initial user input (e.g. customer id). Use default `NullSessionKeyStorage` when there is no need to split
   form sessions or there is no dependency on initial user input.
   ```php
   use Lexal\SteppedForm\Form\Storage\SessionKeyStorageInterface;
   
   final class SessionKeyStorage implements SessionKeyStorageInterface
   {
       public function get(): ?string
       {
           // return current active session key (from redis, database, session or any other storage)
           return 'main';
       }

       public function put(string $sessionKey): void
       {
           // save current form session key
       }
   }

   $sessionKeyStorage = new SessionKeyStorage();
   ```
   
   Create a Storage to store steps data.
   ```php
   use Lexal\SteppedForm\Form\Storage\StorageInterface;
   
   final class Storage implements StorageInterface
   {
       public function get(string $key, string $session, mixed $default = null): mixed
       {
           // get data by key and form session
       }

       public function put(string $key, string $session, mixed $data): void
       {
           // save data by key and form session
       }

       public function clear(string $session): void
       {
           // clear storage by form session
       }
   }

   $storage = new Storage();
   ```

4. Create a Storage and data controllers.
    ```php
    use Lexal\SteppedForm\Form\DataControl;
    use Lexal\SteppedForm\Form\StepControl;
    use Lexal\SteppedForm\Form\Storage\DataStorage;
    use Lexal\SteppedForm\Form\Storage\FormStorage;

    $formStorage = new FormStorage($storage, $sessionKeyStorage); // can use any other storage (session, database, redis, etc.)
    $stepControl = new StepControl($formStorage);
    $dataControl = new DataControl(new DataStorage($formStorage));
    ```

5. Create an Event Dispatcher.
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

6. Create a Stepped Form.
    ```php
    use Lexal\SteppedForm\SteppedForm;

    $form = new SteppedForm(
        $dataControl,
        $stepControl,
        $builder,
        $dispatcher,
    );
    ```

7. Use Stepped Form in the application.
    ```php
    /* Starts a new form session */
    $form->start(
        /* entity that is used as initial form state */,
        /* unique session key if you need to split different sessions of one form */,
    );

    /* Returns a TemplateDefinition of rendered step */
    $form->render('key');

    /* Handles a step logic and saves a new form state */
    $form->handle('key', /* any user submitted data */);

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
    public function getTemplateDefinition(object $entity, Steps $steps): TemplateDefinition
    {
        return new TemplateDefinition('customer', ['customer' => $entity]);
    }

    public function handle(object $entity, mixed $data): object
    {
        // do some logic here
        $entity->name = $data['name'];
        $entity->amount = (float)$data['amount'];

        return $entity; // return an entity that the form will save as step data into the storage
    }
}
```

The second type of step must implement `StepInterface`. Method `handle` can have business logic for calculating data
and must return an updated form entity. Method will receive a `null` or previously submitted data as
a second argument.

```php
use Lexal\SteppedForm\Step\StepInterface;
    
final class TaxStep implements StepInterface
{
    private const TAX_PERCENT = 20;

    public function handle(object $entity, mixed $data): object
    {
        // do some logic here
        $entity->tax = $entity->amount * self::TAX_PERCENT;

        return $entity; // returns an entity that form will save as step data into the storage
    }
}
```

<div style="text-align: right">(<a href="#readme-top">back to top</a>)</div>

## Form Builder

The Stepped Form uses a Form Builder for building a Steps collection by the form entity.

Stepped Form can have a fixed list of steps or dynamic one depending on the previous user input data.

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

    public function build(object $entity): Steps
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

Stepped Form uses a storage to store current step key, handled steps data and a form session key.
It's possible to create your own storage (e.g. session, database, redis) by implementing
`SessionKeyStorageInterface` and `StorageInterface` interfaces.

Stepped Form uses `NullSessionKeyStorage` as `SessionKeyStorageInterface` by default, which means it
always uses only one session key.

`StepControlInterface` and `DataControlInterface` help to work with current step key
and step data respectively.

Dynamic stepped form triggers clearing all steps data after current one after handling it.
Steps data are not cleared from the storage for static forms or when current step implements
`StepBehaviourInterface` and method `forgetDataAfterCurrent` returns `false`.

Example of skipping data storage from being cleared after currently submitted step (for dynamic forms):
```php
use Lexal\SteppedForm\Step\RenderStepInterface;
use Lexal\SteppedForm\Step\StepBehaviourInterface;
use Lexal\SteppedForm\Step\Steps;
use Lexal\SteppedForm\Step\TemplateDefinition;

final class CustomerStep implements StepBehaviourInterface
{
    public function getTemplateDefinition(object $entity, Steps $steps): TemplateDefinition
    {
        // render
    }

    public function handle(object $entity, mixed $data): object
    {
        // handle
    }
    
    public function forgetDataAfterCurrent(object $entity): bool
    {
        return $entity->code === 'NA'; // remove form data after current step only when code equals to 'NA'
    }
}
```

<div style="text-align: right">(<a href="#readme-top">back to top</a>)</div>

## Form Events

The form can dispatch the following events:
1. **BeforeHandleStep** - is dispatched before handling the step. Event contains data passed to the handle
   method, form entity, and step instance. Event listener can update event data after some validation.
2. **FormFinished** - is dispatched when there is no next step after handling current one. Event contains
   a final form entity.

<div style="text-align: right">(<a href="#readme-top">back to top</a>)</div>

## Form Exceptions

The form can throw the following exceptions:
1. **AlreadyStartedException** - when trying to start already started form.
2. **EntityNotFoundException** - when previous step entity not found for rendering or handling step.
3. **EventDispatcherException** - on dispatching events.
4. **FormNotStartedException** - when trying to render, handle or cancel not started form.
5. **NoStepsAddedException** - when trying to start form without steps.
6. **StepHandleException** - on handling step.
7. **StepNotSubmittedException** - when one of the previous step has not been submitted.
8. **StepNotFoundException** - when trying to render or handle not existed step.
9. **StepNotRenderableException** - when trying to render not renderable step.
10. **ReadSessionKeyException** - when can't get current session key.

<div style="text-align: right">(<a href="#readme-top">back to top</a>)</div>

---

## License

Stepped Form is licensed under the MIT License. See [LICENSE](LICENSE) for the full license text.
