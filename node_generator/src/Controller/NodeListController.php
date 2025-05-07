<?php

namespace Drupal\node_generator\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Controller for displaying programmatically generated nodes in a DataTable.
 */
class NodeListController extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new MyNodeListController.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Displays the programmatically generated nodes in a DataTable.
   *
   * @return array
   * A render array for the page.
   */
  public function displayNodeList() {
    $query = $this->entityTypeManager->getStorage('node')->getQuery();
    $query->condition('type', 'test_content_type'); // Filter by your content type
    $query->sort('created', 'DESC');
    $nids = $query->execute();

    $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);

    $rows = [];
    foreach ($nodes as $node) {
      $rows[] = [
        $node->getTitle(),
        $node->getOwner()->getDisplayName(),
        \Drupal::service('date.formatter')->format($node->getCreatedTime(), 'short'),
        $node->isPublished() ? $this->t('Published') : $this->t('Unpublished'),
        [
          '#type' => 'link',
          '#title' => $this->t('View'),
          '#url' => $node->toUrl(),
        ],
        [
          '#type' => 'link',
          '#title' => $this->t('Edit'),
          '#url' => $node->toUrl('edit-form'),
        ],
        // Add more actions as needed
      ];
    }

    $build = [
      '#theme' => 'table',
      '#header' => [
        $this->t('Title'),
        $this->t('Author'),
        $this->t('Created'),
        $this->t('Status'),
        $this->t('Actions'),
        '', // For the Edit link
      ],
      '#rows' => $rows,
      '#attributes' => ['id' => 'programmatic-nodes-table', 'class' => ['table', 'table-striped']],
      '#attached' => [
        'library' => [
          'airs_menu_theme/datatables', // Replace 'your_theme' with your theme's machine name
          'airs_menu_theme/datatables_init', // Your DataTables initialization script
        ],
      ],
    ];

    return $build;
  }

}