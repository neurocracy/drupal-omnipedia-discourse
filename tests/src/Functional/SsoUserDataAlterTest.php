<?php

declare(strict_types=1);

namespace Drupal\Tests\omnipedia_discourse\Functional;

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

  }

  /**
   * Test that our parameters alter hook works as intended.
   */
  public function testAlterParameters(): void {

    FieldStorageConfig::create([
      'entity_type' => 'user',
      'field_name'  => self::EARLY_SUPPORTER_DRUPAL_FIELD_NAME,
      'type'        => 'boolean',
    ])->save();

    FieldConfig::create([
      'entity_type' => 'user',
      'bundle'      => 'user',
      'field_name'  => self::EARLY_SUPPORTER_DRUPAL_FIELD_NAME,
    ])->save();

    /** @var \Drupal\user\UserInterface */
    $user = $this->drupalCreateUser([], null, false, [
      self::EARLY_SUPPORTER_DRUPAL_FIELD_NAME => 1,
    ]);

    $this->assertEquals('1', $user->get(
      self::EARLY_SUPPORTER_DRUPAL_FIELD_NAME
    )->get(0)->getString(), 'whoops');

  }

}
