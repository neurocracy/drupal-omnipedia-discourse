<?php

declare(strict_types=1);

namespace Drupal\omnipedia_discourse_csp\EventSubscriber\Csp;

use Drupal\omnipedia_discourse\Service\DiscourseConfigInterface;
use Drupal\csp\CspEvents;
use Drupal\csp\Event\PolicyAlterEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Alter Content-Security-Policy header to add configured Discourse server URL.
 */
class DiscourseUrlPolicyEventSubscriber implements EventSubscriberInterface {

  /**
   * Event subscriber constructor; saves dependencies.
   *
   * @param \Drupal\omnipedia_discourse\Service\DiscourseConfigInterface $discourseConfig
   *   The Discourse configuration service.
   */
  public function __construct(
    protected readonly DiscourseConfigInterface $discourseConfig,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      // @see https://www.drupal.org/docs/extending-drupal/contributed-modules/contributed-module-documentation/content-security-policy/altering-a-sites-policy#s-default-policy-subscribers
      CspEvents::POLICY_ALTER => ['onCspPolicyAlter', 254],
    ];
  }

  /**
   * Automagically add the Discourse server URL to CSP directives.
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
    $url = $this->discourseConfig->getServerUrl();

    if (empty($url)) {
      return;
    }

    /** @var \Drupal\csp\Csp */
    $policy = $alterEvent->getPolicy();

    $policy->appendDirective('form-action', [$url]);

    // RefreshLess/Turbo needs to be able to send a fetch request to figure out
    // that it should do a full page load.
    $policy->appendDirective('connect-src', [$url]);

  }

}
