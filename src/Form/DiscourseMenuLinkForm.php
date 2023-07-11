<?php

declare(strict_types=1);

namespace Drupal\omnipedia_discourse\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Menu\Form\MenuLinkDefaultForm;
use Drupal\Core\Menu\MenuLinkManagerInterface;
use Drupal\Core\Menu\MenuParentFormSelectorInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the edit form for the Discourse menu link plug-in.
 */
class DiscourseMenuLinkForm extends MenuLinkDefaultForm {

  /**
   * Our menu link configuration name.
   */
  protected const MENU_LINK_CONFIG_NAME = 'omnipedia_discourse.settings';

  /**
   * Constructs this form object; saves depenencies.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The Drupal configuration object factory service.
   *
   * @param \Drupal\Core\Menu\MenuLinkManagerInterface $menuLinkManager
   *   The Drupal menu link manager.
   *
   * @param \Drupal\Core\Menu\MenuParentFormSelectorInterface $menuParentSelector
   *   The Drupal menu parent form selector service.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The Drupal module handler service.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $stringTranslation
   *   The Drupal string translation service.
   */
  public function __construct(
    protected readonly ConfigFactoryInterface $configFactory,
    MenuLinkManagerInterface        $menuLinkManager,
    MenuParentFormSelectorInterface $menuParentSelector,
    ModuleHandlerInterface          $moduleHandler,
    TranslationInterface            $stringTranslation,
  ) {

    parent::__construct(
      $menuLinkManager,
      $menuParentSelector,
      $stringTranslation,
      $moduleHandler,
    );

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('plugin.manager.menu.link'),
      $container->get('menu.parent_form_selector'),
      $container->get('module_handler'),
      $container->get('string_translation'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(
    array $form, FormStateInterface $formState
  ) {

    $form = parent::buildConfigurationForm($form, $formState);

    // Remove the notice that the text and path can't be edited.
    unset($form['info'], $form['path']);

    $form['discourse_title'] = [
      '#type'           => 'textfield',
      '#title'          => $this->t('Menu link text'),
      '#description'    => $this->t(
        'The text to be used for this link in the menu.'
      ),
      '#default_value'  => $this->configFactory->get(
        self::MENU_LINK_CONFIG_NAME
      )->get('menu_link_text'),
      '#required'       => true,
      '#weight'         => -2,
    ];

    /** @var \Drupal\Core\Url The Url object for the Discourse SSO module settings form route. */
    $ssoUrlObject = Url::fromRoute('discourse_sso.settings.form');

    if (!$ssoUrlObject->access()) {
      return $form;
    }

    $form['discourse_url'] = [
      'link' => [
        '#type'   => 'link',
        '#title'  => $this->t('Discourse SSO settings'),
        '#url'    => $ssoUrlObject,
      ],
      '#type'         => 'item',
      '#title'        => $this->t('Link'),
      '#description'  => $this->t(
        'This uses the Discourse server URL set in the Discourse SSO settings form.'
      ),
      '#weight'       => -1,
    ];

    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(
    array &$form, FormStateInterface $formState
  ) {

    $this->configFactory->getEditable(self::MENU_LINK_CONFIG_NAME)->set(
      'menu_link_text', $formState->getValue('discourse_title')
    )->save();

    return parent::submitConfigurationForm($form, $formState);

  }

}
