<?php

declare(strict_types=1);

namespace Drupal\Tests\omnipedia_discourse\Functional;

use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\omnipedia_discourse\Service\SsoUserDataInterface;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests for the Omnipedia Discourse SSO user data service alter hook.
 *
 * @group omnipedia_discourse
 */
class SsoUserDataAlterTest extends BrowserTestBase {

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
  protected SsoUserDataInterface $ssoUserData;

  /**
   * The Drupal field configuration entity storage.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface
   */
  protected ConfigEntityStorageInterface $fieldConfigStorage;

  /**
   * The Drupal field storage configuration entity storage.
   *
   * Try saying that three times fast.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface
   */
  protected ConfigEntityStorageInterface $fieldStorageConfigStorage;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['field', 'user', 'omnipedia_discourse'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {

    parent::setUp();

    $this->ssoUserData = $this->container->get(
      'omnipedia_discourse.sso_user_data'
    );

    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface */
    $entityTypeManager = $this->container->get('entity_type.manager');

    /** @var \Drupal\Core\Entity\EntityTypeRepositoryInterface */
    $entityTypeRepository = $this->container->get('entity_type.repository');

    $this->fieldConfigStorage = $entityTypeManager->getStorage(
      $entityTypeRepository->getEntityTypeFromClass(FieldConfig::class)
    );

    $this->fieldStorageConfigStorage = $entityTypeManager->getStorage(
      $entityTypeRepository->getEntityTypeFromClass(FieldStorageConfig::class)
    );

  }

  /**
   * Create the early supporter field on the user entity.
   */
  protected function createEarlySupporterField(): void {

    $this->fieldStorageConfigStorage->create([
      'entity_type' => 'user',
      'field_name'  => self::EARLY_SUPPORTER_DRUPAL_FIELD_NAME,
      'type'        => 'boolean',
    ])->save();

    $this->fieldConfigStorage->create([
      'entity_type' => 'user',
      'bundle'      => 'user',
      'field_name'  => self::EARLY_SUPPORTER_DRUPAL_FIELD_NAME,
    ])->save();

  }

  /**
   * Test that our parameters alter hook works as intended.
   */
  public function testAlterParameters(): void {

    /** @var \Drupal\user\UserInterface */
    $user = $this->drupalCreateUser([], null, false, [
      self::EARLY_SUPPORTER_DRUPAL_FIELD_NAME => 1,
    ]);

    $this->assertEquals('1', $user->get(
      self::EARLY_SUPPORTER_DRUPAL_FIELD_NAME
    )->get(0)->getString(), 'whoops');

  }

}
