<?php

declare(strict_types=1);

namespace Drupal\omnipedia_discourse\Service;

use Drupal\Core\Url;
use Drupal\omnipedia_core\WrappedEntities\NodeWithWikiInfoInterface;
use Drupal\omnipedia_core\WrappedEntities\TaxonomyTermWithWikiInfoInterface;

/**
 * Interface for a Discourse permalink resolver service.
 */
interface DiscoursePermalinkResolverInterface {

  /**
   * Build a permalink given a wiki date.
   *
   * @param string $date
   *   A date or keyword that can be resolved by the Omnipedia date resolver.
   *
   * @return \Drupal\Core\Url
   *   A Url object pointing to the Discourse server permalink for the provided
   *   date's episode.
   *
   * @see \Drupal\omnipedia_date\Service\DateResolverInterface::resolve()
   */
  public function fromDate(string $date = 'current'): Url;

  /**
   * Build a permalink given a wrapped taxonomy term.
   *
   * @param \Drupal\omnipedia_core\WrappedEntities\TaxonomyTermWithWikiInfoInterface $wrappedTerm
   *   A wrapped taxonomy term entity.
   *
   * @return \Drupal\Core\Url
   *   A Url object pointing to the Discourse server permalink for the provided
   *   episode.
   */
  public function fromWrappedTaxonomyTerm(
    TaxonomyTermWithWikiInfoInterface $wrappedTerm,
  ): Url;

  /**
   * Build a permalink given a wrapped wiki node.
   *
   * @param \Drupal\omnipedia_core\WrappedEntities\NodeWithWikiInfoInterface $    NodeWithWikiInfoInterface $wrappedNode
   *   A wrapped wiki node entity.
   *
   * @return \Drupal\Core\Url
   *   A Url object pointing to the Discourse server permalink for the provided
   *   wiki node's episode.
   */
  public function fromWrappedWikiNode(
    NodeWithWikiInfoInterface $wrappedNode,
  ): Url;

}
