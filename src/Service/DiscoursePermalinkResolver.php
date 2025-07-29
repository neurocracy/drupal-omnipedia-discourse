<?php

declare(strict_types=1);

namespace Drupal\omnipedia_discourse\Service;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;
use Drupal\omnipedia_core\WrappedEntities\NodeWithWikiInfoInterface;
use Drupal\omnipedia_core\WrappedEntities\TaxonomyTermWithWikiInfoInterface;
use Drupal\omnipedia_date\Service\DateResolverInterface;
use Drupal\omnipedia_discourse\Service\DiscourseConfigInterface;
use Drupal\omnipedia_discourse\Service\DiscoursePermalinkResolverInterface;
use Drupal\omnipedia_main_page\Service\MainPageCacheInterface;
use Drupal\omnipedia_main_page\Service\MainPageResolverInterface;
use Drupal\typed_entity\EntityWrapperInterface;
use function implode;
use function is_object;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * The Discourse permalink resolver service.
 */
class DiscoursePermalinkResolver implements DiscoursePermalinkResolverInterface {

  /**
   * Base string for building date/episode cache IDs.
   */
  protected const BASE_CACHE_ID = 'omnipedia_discourse.episode_term';

  /**
   * Cache tag set on all cached date/episode relationships.
   */
  protected const SHARED_CACHE_TAG = 'omnipedia_discourse_episode_term';

  /**
   * Service constructor; saves dependencies.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The default cache bin.
   *
   * @param \Drupal\omnipedia_date\Service\DateResolverInterface $dateResolver
   *   The Omnipedia date resolver service.
   *
   * @param \Drupal\omnipedia_discourse\Service\DiscourseConfigInterface $discourseConfig
   *   The Discourse configuration service.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface
   *   The Drupal entity type manager.
   *
   * @param \Drupal\typed_entity\EntityWrapperInterface $typedEntityRepositoryManager
   *   The Typed Entity repository manager.
   *
   * @param \Drupal\omnipedia_main_page\Service\MainPageCacheInterface $mainPageCache
   *   The Omnipedia main page cache service.
   *
   * @param \Drupal\omnipedia_main_page\Service\MainPageResolverInterface $mainPageResolver
   *   The Omnipedia main page resolver service.
   */
  public function __construct(
    #[Autowire(service: 'cache.default')]
    protected readonly CacheBackendInterface $cache,
    #[Autowire(service: 'omnipedia_date.date_resolver')]
    protected readonly DateResolverInterface $dateResolver,
    protected readonly DiscourseConfigInterface $discourseConfig,
    protected readonly EntityTypeManagerInterface $entityTypeManager,
    #[Autowire(service: 'Drupal\typed_entity\RepositoryManager')]
    protected readonly EntityWrapperInterface $typedEntityRepositoryManager,
    #[Autowire(service: 'omnipedia_main_page.cache')]
    protected readonly MainPageCacheInterface $mainPageCache,
    #[Autowire(service: 'omnipedia_main_page.resolver')]
    protected readonly MainPageResolverInterface $mainPageResolver,
  ) {}

  /**
   * Build a Discourse server permalink given a permalink slug.
   *
   * @param string $slug
   *   A Discourse permalink slug.
   *
   * @return \Drupal\Core\Url
   *   A Url object pointing to the Discourse server permalink.
   */
  protected function buildPermalink(string $slug): Url {

    $serverParts = UrlHelper::parse($this->discourseConfig->getServerUrl());

    return Url::fromUri($serverParts['path'] . '/' . $slug, [
      'query'     => $serverParts['query'],
      'fragment'  => $serverParts['fragment'],
    ]);

  }

  /**
   * Build a cache ID given a wiki date.
   *
   * @param string $date
   *   A wiki date.
   *
   * @return string
   *   A cache ID built using the wiki date and the base cache ID.
   */
  protected function getCacheId(string $date): string {
    return implode(':', [self::BASE_CACHE_ID, $date]);
  }

  /**
   * {@inheritdoc}
   */
  public function fromDate(string $date = 'current'): Url {

    $dateObject = $this->dateResolver->resolve($date);

    $resolvedDate = $dateObject->format('storage');

    $cached = $this->cache->get($this->getCacheId($resolvedDate));

    if (is_object($cached) === true) {

      $termStorage = $this->entityTypeManager->getStorage('taxonomy_term');

      $episode = $this->typedEntityRepositoryManager->wrap(
        $termStorage->load($cached->data),
      );

    // If not cached, infer the episode from the main page for the specified
    // date.
    //
    // @todo Replace this with a direct mapping of episodes and dates when date
    //   system is reworked and simplified.
    //
    // @see https://gitlab.com/neurocracy/omnipedia/modules/omnipedia-date/-/issues/3
    //
    // @see https://gitlab.com/neurocracy/omnipedia/modules/omnipedia-core/-/issues/10
    } else {

      $mainPage = $this->typedEntityRepositoryManager->wrap(
        $this->mainPageResolver->get($resolvedDate),
      );

      $mainPageCacheTags = $this->mainPageCache->getAllCacheTags();

      $episode = $mainPage->getEpisode();

      $episodeCacheTags = $episode->getEntity()->getCacheTags();

      $this->cache->set(
        $this->getCacheId($resolvedDate),
        $episode->id(),
        CacheBackendInterface::CACHE_PERMANENT,
        [
          self::SHARED_CACHE_TAG,
          implode(':', ['omnipedia_dates', $resolvedDate]),
        ] + $episodeCacheTags + $mainPageCacheTags,
      );

    }

    return $this->fromWrappedTaxonomyTerm($episode);

  }

  /**
   * {@inheritdoc}
   */
  public function fromWrappedTaxonomyTerm(
    TaxonomyTermWithWikiInfoInterface $wrappedTerm,
  ): Url {
    return $this->buildPermalink($wrappedTerm->getDiscoursePermalinkSlug());
  }

  /**
   * {@inheritdoc}
   */
  public function fromWrappedWikiNode(
    NodeWithWikiInfoInterface $wrappedNode,
  ): Url {
    return $this->fromWrappedTaxonomyTerm($wrappedNode->getEpisode());
  }

}
