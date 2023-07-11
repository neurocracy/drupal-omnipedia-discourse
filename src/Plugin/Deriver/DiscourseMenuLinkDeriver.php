<?php

declare(strict_types=1);

namespace Drupal\omnipedia_discourse\Plugin\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\omnipedia_discourse\Form\DiscourseMenuLinkForm;
use Drupal\omnipedia_discourse\Plugin\Menu\DiscourseMenuLink;

/**
 * Omnipedia Discourse menu link menu link plug-in deriver.
 *
 * This is necessary to automagically create the Discourse link menu item with
 * an external URL that isn't hard-coded in the .links.menu.yml file. Drupal's
 * menu system would not know about our menu link plug-in without this.
 */
class DiscourseMenuLinkDeriver extends DeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($basePluginDefinition) {

    // We just generate a single plug-in.
    return [
      [
        'class'       => DiscourseMenuLink::class,
        'form_class'  => DiscourseMenuLinkForm::class,
        'provider'    => 'omnipedia_discourse',
        'menu_name'   => 'main',
        'weight'      => 0,
        'id'          => 'omnipedia_discourse:server',
        'url'         => '',
      ],
    ];

  }

}
