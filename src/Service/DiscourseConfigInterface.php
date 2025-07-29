<?php

declare(strict_types=1);

namespace Drupal\omnipedia_discourse\Service;

use Drupal\Core\Config\Config;

/**
 * Interface for a Discourse configuration service.
 */
interface DiscourseConfigInterface {

  /**
   * Get the Discourse configuration.
   *
   * @param bool|boolean $editable
   *   If true, returns an editable configuration object. If false, returns an
   *   immutable configuration object. Defaults to false.
   *
   * @return \Drupal\Core\Config\ImmutableConfig|\Drupal\Core\Config\Config
   */
  public function getConfig(bool $editable = false): Config;

  /**
   * Get the Discourse configuration name.
   *
   * @return string
   *   The Discourse configuration name.
   */
  public function getConfigName(): string;

  /**
   * Get cache tags for the Discourse configuration.
   *
   * @return string[]
   *   One or more cache tags for the Discourse configuration.
   */
  public function getConfigCacheTags(): array;

  /**
   * Get the configured Discourse server URL.
   *
   * @return string
   *   The configured Discourse server URL.
   */
  public function getServerUrl(): string;

}
