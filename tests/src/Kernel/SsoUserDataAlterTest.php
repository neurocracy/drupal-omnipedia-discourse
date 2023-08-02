<?php

declare(strict_types=1);

namespace Drupal\Tests\omnipedia_discourse\Kernel;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityTypeRepositoryInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;
use Drupal\omnipedia_discourse\Service\SsoUserDataInterface;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\user\UserStorageInterface;

/**
 * Tests for the Omnipedia Discourse SSO user data service alter hook.
 *
 * @group omnipedia
 *
 * @group omnipedia_discourse
 */
class SsoUserDataAlterTest extends KernelTestBase {

  use UserCreationTrait {
    createUser      as drupalCreateUser;
    createRole      as drupalCreateRole;
    createAdminRole as drupalCreateAdminRole;
  }

  /**
   * The name of the Drupal user entity field indicating an early supporter.
   */
  protected const EARLY_SUPPORTER_DRUPAL_FIELD_NAME = 'field_early_supporter';

  /**
   * The name of the Discourse SSO field indicating an early supporter.
   */
  protected const EARLY_SUPPORTER_DISCOURSE_FIELD_NAME = 'custom.user_field_1';

  /**
   * The Omnipedia Discourse SSO user data service.
   *
   * @var \Drupal\omnipedia_discourse\Service\SsoUserDataInterface
   */
  protected readonly SsoUserDataInterface $ssoUserData;

  /**
   * The Drupal entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected readonly EntityTypeManagerInterface $entityTypeManager;

  /**
   * The Drupakl entity type repository.
   *
   * @var \Drupal\Core\Entity\EntityTypeRepositoryInterface
   */
  protected readonly EntityTypeRepositoryInterface $entityTypeRepository;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['field', 'user', 'omnipedia_discourse'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {

    parent::setUp();

    $this->installEntitySchema('user');

    $this->ssoUserData = $this->container->get(
      'omnipedia_discourse.sso_user_data'
    );

    $this->entityTypeManager = $this->container->get('entity_type.manager');

    $this->entityTypeRepository = $this->container->get(
      'entity_type.repository'
    );

  }

  /**
   * Create the early supporter field on the user entity.
   */
  protected function createEarlySupporterField(): void {

    /** @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface The Drupal field configuration entity storage. */
    $fieldConfigStorage = $this->entityTypeManager->getStorage(
      $this->entityTypeRepository->getEntityTypeFromClass(FieldConfig::class),
    );

    /** @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface The Drupal field storage configuration entity storage. Try saying that three times fast. */
    $fieldStorageConfigStorage = $this->entityTypeManager->getStorage(
      $this->entityTypeRepository->getEntityTypeFromClass(
        FieldStorageConfig::class,
      ),
    );

    $fieldStorageConfigStorage->create([
      'entity_type' => 'user',
      'field_name'  => self::EARLY_SUPPORTER_DRUPAL_FIELD_NAME,
      'type'        => 'boolean',
    ])->save();

    $fieldConfigStorage->create([
      'entity_type' => 'user',
      'bundle'      => 'user',
      'field_name'  => self::EARLY_SUPPORTER_DRUPAL_FIELD_NAME,
    ])->save();

  }

  /**
   * Tests that the $parameters array is unchanged if user entity doesn't exist.
   */
  public function testNonExistentUser(): void {

    /** @var \Drupal\user\UserStorageInterface The Drupal user entity storage. */
    $userStorage = $this->entityTypeManager->getStorage('user');

    /** @var integer A user ID that should not exist in user entity storage. */
    $uid = 0;

    // Find the first user ID that doesn't exist.
    while (\is_object($userStorage->load($uid))) {
      $uid++;
    }

    $parameters = ['external_id' => $uid];

    $this->ssoUserData->alterParameters($parameters);

    $this->assertEquals(['external_id' => $uid], $parameters);

  }

  /**
   * Test that the Discourse field is set to false if user is missing field.
   */
  public function testUserMissingField(): void {

    /** @var \Drupal\user\UserInterface */
    $user = $this->drupalCreateUser();

    $this->assertEquals(
      false, $user->hasField(self::EARLY_SUPPORTER_DRUPAL_FIELD_NAME),
      'The early supporter field should not be present on the user in this test.'
    );

    $parameters = ['external_id' => $user->id()];

    $this->ssoUserData->alterParameters($parameters);

    $this->assertEquals([
      'external_id' => $user->id(),
      self::EARLY_SUPPORTER_DISCOURSE_FIELD_NAME => false,
    ], $parameters);

  }

  /**
   * Test that the Discourse field is false when user field is explicitly false.
   */
  public function testUserIsNotEarlySupporter(): void {

    $this->createEarlySupporterField();

    /** @var \Drupal\user\UserInterface */
    $user = $this->drupalCreateUser([], null, false, [
      self::EARLY_SUPPORTER_DRUPAL_FIELD_NAME => 0,
    ]);

    $parameters = ['external_id' => $user->id()];

    $this->ssoUserData->alterParameters($parameters);

    $this->assertEquals([
      'external_id' => $user->id(),
      self::EARLY_SUPPORTER_DISCOURSE_FIELD_NAME => false,
    ], $parameters);

  }

  /**
   * Test that the Discourse field is true when user field is explicitly true.
   */
  public function testUserIsEarlySupporter(): void {

    $this->createEarlySupporterField();

    /** @var \Drupal\user\UserInterface */
    $user = $this->drupalCreateUser([], null, false, [
      self::EARLY_SUPPORTER_DRUPAL_FIELD_NAME => 1,
    ]);

    $parameters = ['external_id' => $user->id()];

    $this->ssoUserData->alterParameters($parameters);

    $this->assertEquals([
      'external_id' => $user->id(),
      self::EARLY_SUPPORTER_DISCOURSE_FIELD_NAME => true,
    ], $parameters);

  }

}
