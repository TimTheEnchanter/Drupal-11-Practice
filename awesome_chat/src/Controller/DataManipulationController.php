<?php

namespace Drupal\awesome_chat\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Controller for data manipulation operations (edit, delete).
 */
class DataManipulationController extends ControllerBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a new DataManipulationController.
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
   * Displays the edit form for a specific item.
   *
   * @param int $id
   * The ID of the item to edit.
   *
   * @return array
   * A render array for the edit form.
   */
  public function editItem($id) {
    $item = $this->loadItem($id);
    if (!$item) {
      $this->messenger()->addError($this->t('Item with ID @id not found.', ['@id' => $id]));
      return new RedirectResponse(Url::fromRoute('awesome_chat.display_data')->toString());
    }

    // Get the edit form. Drupal's formBuilder()->getForm() expects the form class
    // name and any arguments to pass to the form's buildForm() method.
    $form = $this->formBuilder()->getForm('Drupal\awesome_chat\Form\EditItemForm', $item);

    return $form;
  }

  /**
   * Handles the deletion of a specific item.
   *
   * @param int $id
   * The ID of the item to delete.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   * A redirect response to the data listing page.
   */
  public function deleteItem($id) {
    $item = $this->loadItem($id);
    if (!$item) {
      $this->messenger()->addError($this->t('Item with ID @id not found.', ['@id' => $id]));
      return new RedirectResponse(Url::fromRoute('awesome_chat.display_data')->toString());
    }

    $this->database->delete('awesome_chat_basic_info')
      ->condition('id', $id)
      ->execute();

    $this->messenger()->addMessage($this->t('Item with ID @id has been deleted.', ['@id' => $id]));

    return new RedirectResponse(Url::fromRoute('awesome_chat.display_data')->toString());
  }

  /**
   * Loads a single item from the database by ID.
   *
   * @param int $id
   * The ID of the item to load.
   *
   * @return array|false
   * An associative array representing the item, or FALSE if not found.
   */
  protected function loadItem($id) {
    $query = $this->database->select('awesome_chat_basic_info', 'm')
      ->fields('m')
      ->condition('id', $id);
    return $query->execute()->fetchAssoc();
  }

}