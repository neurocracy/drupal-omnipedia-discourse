<?php

declare(strict_types=1);

namespace Drupal\omnipedia_discourse\Hooks;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Url;
use Drupal\hux\Attribute\Alter;
use Drupal\omnipedia_core\Service\WikiNodeResolverInterface;
use Drupal\omnipedia_discourse\Service\DiscourseConfigInterface;
use Drupal\omnipedia_discourse\Service\DiscoursePermalinkResolverInterface;
use Drupal\typed_entity\EntityWrapperInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Wiki node Talk local task hooks.
 *
 * @see \Drupal\omnipedia_discourse\Hooks\Controller\WikiNodeTalkLocalTaskController
 *   Handles access control to the local task and acts as fallback if we fail to
 *   alter the URL here.
 */
class WikiNodeTalkLocalTask {

  /**
   * Constructor; saves dependencies.
   *
   * @param \Drupal\omnipedia_discourse\Service\DiscourseConfigInterface $discourseConfig
   *   The Discourse configuration service.
   *
   * @param \Drupal\omnipedia_discourse\Service\DiscoursePermalinkResolverInterface $discoursePermalinkResolver
   *   The Discourse permalink resolver service.
   *
   * @param \Drupal\typed_entity\EntityWrapperInterface $typedEntityRepositoryManager
   *   The Typed Entity repository manager.
   *
   * @param \Drupal\omnipedia_core\Service\WikiNodeResolverInterface $wikiNodeResolver
   *   The Omnipedia wiki node resolver service.
   */
  public function __construct(
    protected readonly DiscourseConfigInterface $discourseConfig,
    protected readonly DiscoursePermalinkResolverInterface $discoursePermalinkResolver,
    #[Autowire(service: 'Drupal\typed_entity\RepositoryManager')]
    protected readonly EntityWrapperInterface $typedEntityRepositoryManager,
    #[Autowire(service: 'omnipedia.wiki_node_resolver')]
    protected readonly WikiNodeResolverInterface $wikiNodeResolver,
  ) {}

  #[Alter('menu_local_tasks')]
  /**
   * Replace the internal Talk Url object with the Discourse permalink.
   *
   * @param array $data
   *
   * @param string $routeName
   *
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $cacheability
   *
   * @see hook_menu_local_tasks_alter
   */
  public function replaceUrl(
    array &$data, string $routeName,
    RefinableCacheableDependencyInterface &$cacheability,
  ): void {

    if (!isset($data['tabs'][0]['entity.node.omnipedia_talk'])) {
      return;
    }

    $task = &$data['tabs'][0]['entity.node.omnipedia_talk'];

    $parameters = $task['#link']['url']->getRouteParameters();

    try {

      $node = $this->wikiNodeResolver->resolveWikiNode($parameters['node']);

      $wrappedNode = $this->typedEntityRepositoryManager->wrap($node);

      $episodeUrl = $this->discoursePermalinkResolver->fromWrappedWikiNode(
        $wrappedNode,
      );

    // If there's any error thrown by the above, return here.
    } catch (\Error|\Exception $exception) {

      return;

    }

    $task['#link']['url'] = $episodeUrl;

    // @todo Also get cache tags from the permalink resolver, including the date
    //  this is cached for, etc.
    $cacheability->addCacheContexts(['omnipedia_dates'])->addCacheTags(
      $this->discourseConfig->getConfigCacheTags(),
    );

  }

}
