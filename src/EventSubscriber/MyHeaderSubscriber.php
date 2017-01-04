<?php

namespace Drupal\my_header_module\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class MyHeaderSubscriber.
 *
 * @package Drupal\my_header_module
 */
class MyHeaderSubscriber implements EventSubscriberInterface {

  protected $user;

  /**
   * Constructor.
   * @param AccountProxyInterface $userService
   */
  public function __construct(AccountProxyInterface $userService) {
    $this->user = $userService->getAccount();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('current_user'));
  }

  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = ['addHeader'];
    return $events;
  }

  /**
   * Add some access control headers for anonymous users.
   * @param FilterResponseEvent $event
   */
  public function addHeader(FilterResponseEvent $event) {
    if ($this->user->isAnonymous()) {
      $event->getResponse()
        ->headers
        ->set("Access-Control-Allow-Origin", "*");
    }
  }
}
