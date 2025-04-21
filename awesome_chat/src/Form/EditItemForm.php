<?php

namespace Drupal\awesome_chat\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form to edit an awesome chat item.
 */
class EditItemForm extends FormBase {

  /**
   * The ID of the item being edited.
   *
   * @var int
   */
  protected $itemId;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a new EditItemForm.
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
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'awesome_chat_edit_item_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $item = NULL) {
    if ($item) {
      $this->itemId = $item['id'];
      $form['name'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Name'),
        '#default_value' => $item['name'],
        '#required' => TRUE,
      ];

      $form['email'] = [
        '#type' => 'email',
        '#title' => $this->t('Email Address'),
        '#default_value' => $item['email'],
        '#required' => TRUE,
      ];

      $form['age'] = [
        '#type' => 'number',
        '#title' => $this->t('Age'),
        '#default_value' => $item['age'],
        '#min' => 1,
        '#max' => 150,
      ];

      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Save Changes'),
      ];
    } else {
      $form['message'] = [
        '#markup' => $this->t('Item not found.'),
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (strlen($form_state->getValue('name')) < 5) {
      $form_state->setErrorByName('name', $this->t('Name must be at least 5 characters long.'));
    }

    if (!\Drupal::service('email.validator')->isValid($form_state->getValue('email'))) {
      $form_state->setErrorByName('email', $this->t('The email address is not valid.'));
    }

    if ($form_state->getValue('age') < 18) {
      $form_state->setErrorByName('age', $this->t('Age must be 18 or older.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $name = $form_state->getValue('name');
    $email = $form_state->getValue('email');
    $age = $form_state->getValue('age');

    $this->database->update('awesome_chat_basic_info')
      ->fields([
        'name' => $name,
        'email' => $email,
        'age' => $age,
      ])
      ->condition('id', $this->itemId)
      ->execute();

    $this->messenger()->addMessage($this->t('Item with ID @id has been updated.', ['@id' => $this->itemId]));

    $form_state->setRedirect('awesome_chat.display_data');
  }

}