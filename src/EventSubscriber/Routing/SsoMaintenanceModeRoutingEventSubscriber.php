<?php

declare(strict_types=1);

namespace Drupal\omnipedia_discourse\EventSubscriber\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Discourse SSO maintenance mode routing event subscriber.
 */
class SsoMaintenanceModeRoutingEventSubscriber extends RouteSubscriberBase {

  /**
   * Alter routes.
   *
   * This sets the '_maintenance_access' route option to true for the
   * 'discourse_sso.sso' route so that users can log into Discourse.
   *
   * @param \Symfony\Component\Routing\RouteCollection $collection
   *   A collection of routes.
   *
   * @todo Make this configurable and default to off.
   */
  public function alterRoutes(RouteCollection $collection) {

    $collection->get('discourse_sso.sso')->setOption(
      '_maintenance_access', true
    );

  }

}
