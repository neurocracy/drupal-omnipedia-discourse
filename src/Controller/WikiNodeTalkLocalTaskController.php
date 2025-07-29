<?php

declare(strict_types=1);

namespace Drupal\omnipedia_discourse\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\omnipedia_core\Service\WikiNodeResolverInterface;
use Drupal\omnipedia_discourse\Service\DiscourseConfigInterface;
use Drupal\omnipedia_discourse\Service\DiscoursePermalinkResolverInterface;
use Drupal\omnipedia_main_page\Service\MainPageResolverInterface;
use Drupal\typed_entity\EntityWrapperInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Omnipedia wiki node talk local task controller.
 *
 * Note that this controller exists primarily for access control to the local
 * task and as a fallback if our alter hook doesn't succeed in replacing the
 * internal URL with the resolved Discourse permalink URL.
 *
 * @see \Drupal\omnipedia_discourse\Hooks\WikiNodeTalkLocalTask::replaceUrl()
 *   Replaces the internal URL with the Discourse permalink URL.
 */
class WikiNodeTalkLocalTaskController implements ContainerInjectionInterface {

  use AutowireTrait;

  /**
   * Constructor; saves dependencies.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The current user proxy service.
   *
   * @param \Drupal\omnipedia_discourse\Service\DiscourseConfigInterface $discourseConfig
   *   The Discourse configuration service.
   *
   * @param \Drupal\omnipedia_discourse\Service\DiscoursePermalinkResolverInterface $discoursePermalinkResolver
   *   The Discourse permalink resolver service.
   *
   * @param \Drupal\omnipedia_main_page\Service\MainPageResolverInterface $mainPageResolver
   *   The Omnipedia main page resolver service.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeResolverInterface $wikiNodeResolver
   *   The Omnipedia wiki node resolver service.
   */
  public function __construct(
    protected readonly AccountProxyInterface $currentUser,
    protected readonly DiscourseConfigInterface $discourseConfig,
    protected readonly DiscoursePermalinkResolverInterface $discoursePermalinkResolver,
    #[Autowire(service: 'Drupal\typed_entity\RepositoryManager')]
    protected readonly EntityWrapperInterface $typedEntityRepositoryManager,
    #[Autowire(service: 'omnipedia_main_page.resolver')]
    protected readonly MainPageResolverInterface $mainPageResolver,
    #[Autowire(service: 'omnipedia.wiki_node_resolver')]
    protected readonly WikiNodeResolverInterface $wikiNodeResolver,
  ) {}

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
      $this->wikiNodeResolver->isWikiNode($node) &&
      !$this->mainPageResolver->is($node) &&
      $node->access('view', $account)
    // @todo Also get cache tags from the permalink resolver, including the date
    //  this is cached for, etc.
    )->addCacheContexts(['omnipedia_dates'])->addCacheTags(
      $this->discourseConfig->getConfigCacheTags(),
    )->addCacheableDependency($node);

  }

  /**
   * Get the Discourse URL to redirect to.
   *
   * @param \Drupal\node\NodeInterface $node
   *   A node object.
   *
   * @return string
   *   A URL to redirect to. This will point to a configured Discourse permalink
   *   for the episode the node is part of, or if the permanlink couldn't be
   *   found, this will point to the base URL of the Discourse server.
   */
  protected function getRedirectUrl(NodeInterface $node): string {

    $wrappedNode = $this->typedEntityRepositoryManager->wrap($node);

    try {

      $episodeUrl = $this->discoursePermalinkResolver->fromWrappedWikiNode(
        $wrappedNode,
      );

    // If there's any error thrown by the above, return the server URL here as a
    // fallback.
    } catch (\Error|\Exception $exception) {

      return $this->discourseConfig->getServerUrl();

    }

    return $episodeUrl->toString();

  }

  /**
   * Callback for the talk route.
   *
   * @param \Drupal\node\NodeInterface $node
   *   A node object.
   *
   * @return \Drupal\Core\Routing\TrustedRedirectResponse
   *   A trusted redirect response object.
   */
  public function view(NodeInterface $node): TrustedRedirectResponse {

    return new TrustedRedirectResponse(
      $this->getRedirectUrl($node), 302,
    );

  }

}
