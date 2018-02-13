<?php

namespace Drupal\til_related\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;

/**
 * Provides a link for a related post based on the current one's tags and some
 * other magic.
 *
 * @Block(
 *   id = "related",
 *   admin_label = @Translation("Related post"),
 * )
 */
class Related extends BlockBase {
  private $generatedLabel = NULL;

  public function label() {
    return $this->generatedLabel;
  }

  public function build() {
    $node = \Drupal::routeMatch()->getParameter('node');
    if (!$node instanceof \Drupal\node\NodeInterface || $node->getType() !== 'post')
      return NULL;

    $linkedNode = $this->tryPreviouslyByFirstTag($node);
    if (!$linkedNode)
      $linkedNode = $this->tryLatestFromAnotherTag($node);
    if (!$linkedNode)
      $linkedNode = $this->tryLatestFromFirstTag($node);
    if (!$linkedNode)
      $linkedNode = $this->tryLatest($node);

    if (!$linkedNode)
      return NULL;
    return [
      '#type' => 'link',
      '#url' => $linkedNode->toUrl(),
      '#title' => $linkedNode->label(),
    ];
  }

  private function tryPreviouslyByFirstTag(Node $node) {
    if (count($node->field_tags) === 0)
      return FALSE;

    /** @var \Drupal\taxonomy\Entity\Term $tag */
    $tag = $node->field_tags[0]->entity;

    $query = \Drupal::entityQuery('node');
    $query->condition('type', 'post');
    $query->condition('status', Node::PUBLISHED);

    $query->condition('field_tags.0', $tag->id());
    $query->condition('created', $node->getCreatedTime(), '<');

    $query->sort('created', 'DESC');

    $query->range(0, 1);

    $nids = $query->execute();
    if (!$nids)
      return FALSE;

    $this->generatedLabel = $this->t('Previous on <a href="@link">@tag</a>', [
      '@tag' => $tag->label(),
      '@link' => $tag->toUrl()->toString(),
    ]);

    return Node::load(current($nids));
  }

  private function tryLatestFromFirstTag(Node $node) {
    if (count($node->field_tags) === 0)
      return FALSE;

    /** @var \Drupal\taxonomy\Entity\Term $tag */
    $tag = $node->field_tags[0]->entity;

    $query = \Drupal::entityQuery('node');
    $query->condition('type', 'post');
    $query->condition('status', Node::PUBLISHED);

    $query->condition('field_tags', $tag->id());
    $query->condition('nid', $node->id(), '!=');

    $query->sort('created', 'DESC');

    $query->range(0, 1);

    $nids = $query->execute();
    if (!$nids)
      return FALSE;

    $this->generatedLabel = $this->t('Latest on <a href="@link">@tag</a>', [
      '@tag' => $tag->label(),
      '@link' => $tag->toUrl()->toString(),
    ]);

    return Node::load(current($nids));
  }

  private function tryLatest(Node $node) {
    /** @var \Drupal\taxonomy\Entity\Term $tag */
    $tag = $node->field_tags[0]->entity;

    $query = \Drupal::entityQuery('node');
    $query->condition('type', 'post');
    $query->condition('status', Node::PUBLISHED);

    $query->sort('created', 'DESC');

    $query->range(0, 1);

    $nids = $query->execute();
    if (!$nids)
      return FALSE;

    if (current($nids) == $node->id())
      return FALSE;

    $this->generatedLabel = $this->t('Latest');

    return Node::load(current($nids));
  }

  private function tryLatestFromAnotherTag(Node $node) {
    if (count($node->field_tags) < 2)
      return FALSE;

    $tagTids = [];
    foreach ($node->field_tags as $tagRef)
      $tagTids[] = $tagRef->target_id;

    $query = \Drupal::entityQuery('node');
    $query->condition('type', 'post');
    $query->condition('status', Node::PUBLISHED);

    $query->condition('field_tags', $tagTids, 'IN');
    $query->condition('nid', $node->id(), '!=');

    $query->sort('created', 'DESC');

    $query->range(0, 1);

    $nids = $query->execute();
    if (!$nids)
      return FALSE;

    $ret = Node::load(current($nids));
    /** @var \Drupal\taxonomy\Entity\Term $tag */
    $tag = $ret->field_tags[0]->entity;

    $this->generatedLabel = $this->t('Latest on <a href="@link">@tag</a>', [
      '@tag' => $tag->label(),
      '@link' => $tag->toUrl()->toString(),
    ]);

    return $ret;
  }
}
