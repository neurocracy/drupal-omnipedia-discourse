<?php

declare(strict_types=1);

namespace Drupal\omnipedia_discourse\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\user\UserStorageInterface;
use Drupal\omnipedia_discourse\Service\SsoUserDataInterface;

/**
 * The Omnipedia Discourse SSO user data service.
 */
class SsoUserData implements SsoUserDataInterface {

  /**
   * The name of the Drupal user entity field indicating an early supporter.
   */
  protected const EARLY_SUPPORTER_DRUPAL_FIELD_NAME = 'field_early_supporter';

  /**
   * The name of the Discourse SSO field indicating an early supporter.
   */
  protected const EARLY_SUPPORTER_DISCOURSE_FIELD_NAME = 'custom.user_field_1';

  /**
   * The Drupal user entity storage.
   *
   * @var \Drupal\user\UserStorageInterface
   */
  protected UserStorageInterface $userStorage;

  /**
   * Constructs this service object; saves dependencies.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The Drupal entity type manager.
   */
  public function __construct(EntityTypeManagerInterface  $entityTypeManager) {
    $this->userStorage = $entityTypeManager->getStorage('user');
  }

  /**
   * {@inheritdoc}
   */
  public function alterParameters(array &$parameters): void {

    /** @var \Drupal\user\UserInterface|null */
    $user = $this->userStorage->load($parameters['external_id']);

    if (!\is_object($user)) {
      return;
    }

    if (!$user->hasField(self::EARLY_SUPPORTER_DRUPAL_FIELD_NAME)) {

      $parameters[self::EARLY_SUPPORTER_DISCOURSE_FIELD_NAME] = false;

      return;

    }

    /** @var Drupal\Core\TypedData\TypedDataInterface|null */
    $fieldData = $user->get(
      self::EARLY_SUPPORTER_DRUPAL_FIELD_NAME
    )->get(0);

    if (!\is_object($fieldData)) {

      $parameters[self::EARLY_SUPPORTER_DISCOURSE_FIELD_NAME] = false;

      return;

    }

    $parameters[
      self::EARLY_SUPPORTER_DISCOURSE_FIELD_NAME
    ] = (bool) $fieldData->getString();

  }

}
