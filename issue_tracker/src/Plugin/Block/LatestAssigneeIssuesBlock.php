<?php

namespace Drupal\issue_tracker\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Link;

/**
 * Provides a block that shows the latest issues assigned to the current user.
 *
 * @Block(
 *   id = "latest_assignee_issues",
 *   admin_label = @Translation("Latest issues"),
 *   category = @Translation("Custom"),
 *   cache = {
 *     "max-age" = 0
 *   }
 * )
 */
class LatestAssigneeIssuesBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Retrieve the user ID of the currently logged in user.
    $user_id = \Drupal::currentUser()->id();

    // Retrieve the three latest nodes assigned to the current user with a user reference field in the "Issue" content type.
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'issue')
      ->condition('field_assignee', $user_id)
      ->range(0, 3)
      ->sort('created', 'DESC');
    $nids = $query->execute();
    $nodes = \Drupal\node\Entity\Node::loadMultiple($nids);

    // Render the nodes in a list.
    $list = [];
    foreach ($nodes as $node) {
      $list[] = Link::createFromRoute($node->getTitle(), 'entity.node.canonical', ['node' => $node->id()]);
    }

    // If there are no nodes, display a message instead.
    if (empty($list) && \Drupal::currentUser()->isAuthenticated()) {
      $content = [
        '#markup' => $this->t('You have no assigned issues.'),
      ];
    }
    else {
      $content = [
        '#theme' => 'item_list',
        '#list_type' => 'ul',
        '#items' => $list,
      ];
    }

    return $content;
  }

}
