<?php

namespace Drupal\Tests\my_header_module\Unit;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\my_header_module\EventSubscriber\MyHeaderSubscriber;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * Class MyHeaderSubscriberTest
 * @package Drupal\Tests\my_header_module\Unit
 * @coversDefaultClass \Drupal\my_header_module\EventSubscriber\MyHeaderSubscriber
 * @group TrainingCards
 */
class MyHeaderSubscriberTest extends \PHPUnit_Framework_TestCase {
  /**
   * @covers ::addHeader
   */
  public function testAddHeader() {
    // MyHeaderSubscriber's constructor has a second-order dependency, so we need two stubs.
    $stubAnonymousUser = $this->prophesize(AccountInterface::class);
    $stubAnonymousUser
      ->isAnonymous()
      ->willReturn(TRUE);
    $stubCurrentUserService = $this->prophesize(AccountProxyInterface::class);
    $stubCurrentUserService
      ->getAccount()
      ->willReturn($stubAnonymousUser->reveal()); // Transform the Prophecy into a stub object to prophesize.

    // We can now construct our test object.
    $anonymousMyHeaderSubscriber = new MyHeaderSubscriber($stubCurrentUserService->reveal());

    // The ::addHeader method expects a FilterResponseEvent as parameter, let's make it a mock.
    $mockFilterResponseEvent = $this->prophesize(FilterResponseEvent::class);
    $mockResponse = $this->prophesize(Response::class);
    $mockResponseHeaderBag = $this->prophesize(ResponseHeaderBag::class);

    // ::shouldBeCalled() will be our test assertion, verifying that the correct method and parameters are called.
    // This additional feature makes our object a mock instead of a stub.
    $mockResponseHeaderBag
      ->set("Access-Control-Allow-Origin", "*")
      ->shouldBeCalled();
    $mockResponse->headers = $mockResponseHeaderBag->reveal();
    $mockFilterResponseEvent
      ->getResponse()
      ->willReturn($mockResponse->reveal());

    // We can now test our class unit.
    $anonymousMyHeaderSubscriber->addHeader($mockFilterResponseEvent->reveal());

    // We verified that headers are being added for anonymous users, but the method has two code execution paths,
    // an additional one which does nothing if the user is not anonymous. How can we test nothing happening? With an
    // extra mock object.
    $stubNotAnonymousUser = $this->prophesize(AccountInterface::class);
    $stubNotAnonymousUser->isAnonymous()->willReturn(FALSE);
    $stubCurrentUserService = $this->prophesize(AccountProxyInterface::class);
    $stubCurrentUserService->getAccount()->willReturn($stubNotAnonymousUser->reveal());

    $notAnonymousMyHeaderSubscriber = new MyHeaderSubscriber($stubCurrentUserService->reveal());

    $mockFilterResponseEvent = $this->prophesize(FilterResponseEvent::class);
    $mockFilterResponseEvent->getResponse()->shouldNotBeCalled();

    // Let's test nothing happening
    $notAnonymousMyHeaderSubscriber->addHeader($mockFilterResponseEvent->reveal());
  }
}
