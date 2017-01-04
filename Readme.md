# Day 23 - Unit Tests

https://docs.acquia.com/article/lesson-102-unit-testing

While functional testing allows us to cover the dependencies between parts of the application, we sometimes only want to test whether our individual functions or methods work as they should. Unfortunately in the world of procedural programming, from which Drupal came, testing an individual function usually involves settings up a large array of fake global variables and data services that the function and every function it depends on require. This process must be repeated for each individual test, since we cannot trust one test not to contaminate another one.

Objects which are decoupled from global variables and services through dependency injection do not face this problem, since all their requirements are injected as properties in their constructors. The dependencies can then be "mocked" by replacing them with objects that have the same interface but fixed behaviors. Let's see how this works in practice.

Drupal 8.2 adds a new experimental module called **content_moderation**, thanks to an initiative led by Tim Millwood. In `content_moderation_preprocess_node()`, a class called `ContentPreprocess` is used where a basic procedural hook would have stood in Drupal 7. This class is a wrapper around the required dependencies, which allows the hook to be unit tested in `ContentPreprocessTest`.

`ContentPreprocess` consists of one member service, `$routeMatch`, and two member methods: `preprocessNode` which has the same parameters as the node preprocess hook, and `isLatestVersionPage` which receives a `Node` object from the hook invocation. The preprocessor changes a node template variable to true when a node is being shown on its latest version tab. How can we verify that this behavior works without firing up the whole theming and node systems? Let's now take a look at the `ContentPreprocessTest` class for the answer.

`ContentPreprocessTest` contains one test method with five parameters, `testIsLatestVersionPage()`. Let's run this test. Since we are not going to build up a whole fake website and database and filesystem to run the test, we don't even need any webserver to be running! We can just open a terminal on our host OS, go to the project directory and run:

`../vendor/bin/phpunit -c core core/modules/content_moderation/tests/src/Unit/ContentPreprocessTest.php`

You should see:
```
PHPUnit 4.8.31 by Sebastian Bergmann and contributors.

...

Time: 763 ms, Memory: 8.00MB

OK (3 tests, 3 assertions)
```

This is about 20 times faster than the Javascript test and used practically no memory. But why does it say 3 assertions passed when the test method only featured one? This is done through the next method in the class, `routeNodeProvider()`, which returns three arrays of parameters for `testIsLatestVersionPage()`, using the annotation @dataProvider to tell PHPUnit where to get the parameters.

Recall the behavior of `ContentPreprocess::isLatestVersionPage()` we want to test: when the current route is 'entity.node.latest_version' and the routed `Node`'s id is equal to the preprocessed `Node`'s id, return true. Otherwise return false. Hence, our test data provider sets up three different conditions that should map to these results.

Now comes the tricky part, getting the objects used by the method under test to return these values. `ContentPreprocessTest` provides two mock generating methods, `setupCurrentRouteMatch()` and `setupNode()`, that use the Prophecy framework bundled with PHPUnit to create objects that mock the `CurrentRouteMatch` and `Node` classes, overriding the methods that our unit under test depends on. When these methods are called, they will return the parameters listed in the data provider.

Play with the data in `routeNodeProvider()` and see for yourself how it causes the tests to fail.

_Bonus points: Find out how Prophecy differs from the native mocking framework of PHPUnit: https://www.drupal.org/docs/8/phpunit/comparison-with-phpunit-mocks_

## Let's try some test-driven development

Write a unit test for the `EventSubscriber` you wrote on Day 21. It's an extremely simple behavior that nevertheless depends on two external objects, the current_user service and a `FilterResponseEvent` object, which hold other kinds of objects that we eventually manipulate to implement the logic. Fortunately, those are easy to mock and run assertions on.

Use Prophecy to create mock objects in a test class that correctly verifies that headers are being set for an anonymous user, and not being set for an authenticated user. _Hint: this will require Prophecy's predictions._

https://github.com/phpspec/prophecy
