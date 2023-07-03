<?php

declare(strict_types=1);

namespace Drupal\omnipedia_discourse_csp\EventSubscriber\Csp;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\csp\CspEvents;
use Drupal\csp\Event\PolicyAlterEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Alter Content-Security-Policy header to add configured Discourse server URL.
 */
class DiscourseUrlPolicyEventSubscriber implements EventSubscriberInterface {

  /**
   * The Drupal configuration object factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * Event subscriber constructor; saves dependencies.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The Drupal configuration object factory service.
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      CspEvents::POLICY_ALTER => 'onCspPolicyAlter',
    ];
  }

  /**
   * Automagically add the Discourse server URL to the form-action directive.
   *
   * Chrome will refuse to redirect to the external URL if the Discourse server
   * domain isn't present in the 'form-action' directive, while Firefox doesn't
   * seem to care in this case.
   *
   * @param \Drupal\csp\Event\PolicyAlterEvent $alterEvent
   *   The Policy Alter event.
   */
  public function onCspPolicyAlter(PolicyAlterEvent $alterEvent): void {

    /** @var string|null */
    $url = $this->configFactory->get(
      'discourse_sso.settings'
    )->get('discourse_server');

    if (empty($url)) {
      return;
    }

    /** @var \Drupal\csp\Csp */
    $policy = $alterEvent->getPolicy();

    $policy->appendDirective('form-action', [$url]);

  }

}
