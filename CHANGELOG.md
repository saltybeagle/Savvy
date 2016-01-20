# Savvy Changelog

## 0.9.0

Fixes the following issues:

* An issue occurs when an exception is thrown during inside a template which causes output buffers to remain open and the template stack to be left in an unexpected state.
* Traversable objects with array access are not able to be iterated when proxied
* Scalar arrays could not be rendered unless an ArrayObject template existed
* renderElse API method never returned content
* Library API is not fully covered by tests


## 0.8.0

Feature Release:

* Add a config option for iterating over escaped Traversable objects iterate_traversable (defaults to false)

## 0.7.3

Improve support for proxying arrays:

* Objects which implement ArrayIterator are now proxied using a compatible object
* Ensure keys are proxied when returned from ArrayObject

## 0.7.2

Bugfix Release:

* Refactor auto-escaping for arrays to preserve standard array interaction.

## 0.7.1

Bugfix Release:

* Improve escaping of arrays
* Add tests for globals


## 0.7.0

Feature Release:

* Add support for global variables, available in every template

## 0.6.1

Fix:

* Also filter the return values of methods.

## 0.6.0

Feature Release

* Allow direct access to the raw object when escaping. $context->getRawObject()
* Implement Countable interface for auto-escaped context. count($context);

## 0.5.0

Filter iterated/traversable objects.

## 0.4.0

Add renderNULL method for rendering NULL variables or direct templates.

## 0.3.0

Add __toString() support for the object proxy

## 0.2.1

Fix Savvy_UnexpectedValueException.

## 0.2.0

Add __isset and __unset to the ObjectProxy class.

## 0.1.0

Port PEAR2_Templates_Savant to Savvy.

* Always throw exceptions.
* Never load files internally.