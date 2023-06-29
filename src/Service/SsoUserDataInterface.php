<?php

declare(strict_types=1);

namespace Drupal\omnipedia_discourse\Service;

/**
 * The Omnipedia Discourse SSO user data service interface.
 */
interface SsoUserDataInterface {

  /**
   * Alter Discourse SSO parameters.
   *
   * @param array &$parameters
   *
   * @see \Drupal\discourse_sso\Controller\DiscourseSsoController::validate()
   *   Builds parameters and invokes \hook_discourse_sso_parameters_alter().
   */
  public function alterParameters(array &$parameters): void;

}
