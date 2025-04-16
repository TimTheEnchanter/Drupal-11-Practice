<?php

namespace Drupal\awesome_chat\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for displaying data from the awesome_chat_basic_info table.
 */
class DataDisplayController extends ControllerBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a new DataDisplayController.
   *
   * @param \Drupal\Core\Database\Connection $database
   * The database connection service.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  /**
   * Displays the submitted basic information in a Bootstrap table.
   *
   * @return array
   * A render array containing the Bootstrap table.
   */
  public function displayData() {
    $query = $this->database->select('awesome_chat_basic_info', 'm');
    $query->fields('m', ['name', 'email', 'age', 'created']);
    $query->orderBy('created', 'DESC');
    $results = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);

    $header = [
      $this->t('Name'),
      $this->t('Email'),
      $this->t('Age'),
      $this->t('Created'),
    ];

    $rows = [];
    foreach ($results as $record) {
      $rows[] = [
        $record['name'],
        $record['email'],
        $record['age'],
        \Drupal::service('date.formatter')->format($record['created'], 'short'),
      ];
    }

    $build = [
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#attributes' => ['class' => ['table', 'table-striped']], // Bootstrap table classes
    ];

    return $build;
  }

}