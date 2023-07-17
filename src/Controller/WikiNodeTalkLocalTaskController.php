<?php

declare(strict_types=1);

namespace Drupal\omnipedia_discourse\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\omnipedia_core\Entity\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Omnipedia wiki node talk local task controller.
 */
class WikiNodeTalkLocalTaskController implements ContainerInjectionInterface {

  /**
   * The Discourse SSO module configuration name.
   */
  protected const DISCOURSE_SSO_CONFIG_NAME = 'discourse_sso.settings';

  /**
   * The Discourse SSO module configuration.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected ImmutableConfig $discourseSsoConfig;

  /**
   * The Discourse server URL, if configured.
   *
   * @var string
   */
  protected string $discourseServerUrl;

  /**
   * Constructs this controller; saves dependencies.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The Drupal configuration object factory service.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The current user proxy service.
   */
  public function __construct(
    protected readonly ConfigFactoryInterface $configFactory,
    protected readonly AccountProxyInterface  $currentUser,
  ) {

    $this->discourseSsoConfig = $this->configFactory->get(
      self::DISCOURSE_SSO_CONFIG_NAME
    );

    /** @var string|null */
    $url = $this->discourseSsoConfig->get('discourse_server');

    if (!empty($url)) {
      $this->discourseServerUrl = $url;
    } else {
      $this->discourseServerUrl = '';
    }

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('current_user'),
    );
  }


  /**
   * Checks access for the request.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result. Access is granted if the provided node is a wiki node,
   *   the wiki node is not a main page, $account has access to view the wiki
   *   node, and if the Discourse server URL is configured.
   */
  public function access(
    AccountInterface $account, NodeInterface $node
  ): AccessResultInterface {

    return AccessResult::allowedIf(
      $node->isWikiNode() &&
      !$node->isMainPage() &&
      $node->access('view', $account) &&
      !empty($this->discourseServerUrl)
    )
    ->addCacheableDependency($this->discourseSsoConfig)
    ->addCacheableDependency($node);

  }

  /**
   * Callback for the talk route.
   *
   * @param \Drupal\omnipedia_core\Entity\NodeInterface $node
   *   A node object.
   *
   * @return \Drupal\Core\Routing\TrustedRedirectResponse
   *   A trusted redirect response object.
   */
  public function view(NodeInterface $node): TrustedRedirectResponse {

    return new TrustedRedirectResponse($this->discourseServerUrl, 302);

  }

}
