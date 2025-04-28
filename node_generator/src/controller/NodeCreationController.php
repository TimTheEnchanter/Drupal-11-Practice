<?php

namespace Drupal\node_generator\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

class NodeCreationController extends ControllerBase {

  protected $entityTypeManager;

  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  public function createMyNode() {
    $user = $this->entityTypeManager->getStorage('user')->load(1);

    $node = $this->entityTypeManager->getStorage('node')->create([
      'type' => 'test_content_type',
      'uid' => $user->id(),
      'status' => 1,
      'promote' => 0,
      'sticky' => 0,
      'created' => \Drupal::time()->getRequestTime(),
      'changed' => \Drupal::time()->getRequestTime(),
    ]);

    $node->setTitle('My Programmatically Created Node at ' . date('Y-m-d H:i:s'));
    $node->set('body', [
      'value' => 'This node was created programmatically by the awesome_chat at ' . date('Y-m-d H:i:s') . '.',
      'format' => 'basic_html',
    ]);

    try {
      $node->save();
      $this->messenger()->addMessage($this->t('Node "@title" (ID: @nid) created successfully.', [
        '@title' => $node->getTitle(),
        '@nid' => $node->id(),
      ]));
    } catch (\Exception $e) {
      $this->messenger()->addError($this->t('An error occurred while creating the node: @error', ['@error' => $e->getMessage()]));
      \Drupal::logger('your_module')->error('Error creating node: @error', ['@error' => $e->getMessage()]);
    }

    return new RedirectResponse(Url::fromRoute('entity.node.canonical', ['node' => $node->id()])->toString());
  }

}