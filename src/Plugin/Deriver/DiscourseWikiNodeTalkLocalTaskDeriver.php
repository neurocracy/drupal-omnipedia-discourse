<?php

declare(strict_types=1);

namespace Drupal\omnipedia_discourse\Plugin\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Omnipedia wiki node talk local task plug-in deriver.
 */
class DiscourseWikiNodeTalkLocalTaskDeriver extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;

  /**
   * The base plugin ID.
   *
   * @var string
   */
  protected string $basePluginId;

  /**
   * Constructs a new ContentTranslationLocalTasks.
   *
   * @param string $base_plugin_id
   *   The base plugin ID.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $stringTranslation
   *   The Drupal string translation service.
   */
  public function __construct(
    $basePluginId,
    TranslationInterface $stringTranslation,
  ) {
    $this->basePluginId       = $basePluginId;
    $this->stringTranslation  = $stringTranslation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $basePluginId) {
    return new static(
      $basePluginId,
      $container->get('string_translation'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($basePluginDefinition) {

    $this->derivatives['entity.node.omnipedia_talk'] = [
      'title'       => $this->t('Talk'),
      'route_name'  => 'entity.node.omnipedia_talk',
      'base_route'  => 'entity.node.canonical',
    ] + $basePluginDefinition;

    return parent::getDerivativeDefinitions($basePluginDefinition);

  }

}
