<?php

declare(strict_types=1);

namespace Drupal\awesome_chat\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for Awesome chat routes.
 */
final class AwesomeChatController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function chat(): array {

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('Hello from Awesome Chat!'),
    ];

    return $build;
  }

}
