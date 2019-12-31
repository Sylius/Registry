# Registry

Simple registry component useful for all types of applications.

## Installation

```bash
composer require sylius/registry
```

## Basic usage

A registry object acts as a collection of objects.
`ServiceRegistry` allows you to store objects which implement a specific interface.

### ServiceRegistry

To create a new `ServiceRegistry` you need to determine what kind of interface should be kept inside.

For the sake of examples lets use the `\DateTimeInterface`.

```php
use Sylius\Component\Registry\ServiceRegistry;

$registry = new ServiceRegistry(\DateTimeInterface::class);
```

Once you've done that you can manage any object with the corresponding interface.

So for starters, lets add some services:

```php
$registry->register('date_time', new \DateTime());
$registry->register('date_time_immutable', new \DateTimeImmutable());
```

The first parameter of `register` is incredibly important, as we will use it for all further operations.
Also it's the key at which our service is stored in the array returned by `all` method.

After specifying the interface and inserting services, we can manage them:

```php
$registry->has('date_time'); // returns true

$registry->get('date_time'); // returns the \DateTime we inserted earlier on

$registry->all(); // returns an array containing both rule checkers
```

Removing a service from the registry is as easy as adding:

```php
$registry->unregister('date_time');

$registry->has('date_time'); // now returns false
```
