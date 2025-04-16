<?php

namespace Drupal\awesome_chat\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface; //Not sure about this one lol


final class MyForm extends FormBase {

  public function __construct(Connection $database) {
    $this->database = $database;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  public function getFormId() {
    return 'awesome_chat_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your Name'),
      '#description' => $this->t('Please enter your full name.'),
      '#required' => TRUE,
    ];
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Your Email Address'),
      '#required' => TRUE,
    ];

    $form['age'] = [
      '#type' => 'number',
      '#title' => $this->t('Your Age'),
      '#min' => 1,
      '#max' => 150,
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];
    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (strlen($form_state->getValue('name')) < 5) {
      $form_state->setErrorByName('name', $this->t('Your name must be at least 5 characters long.'));
    }

    if (!\Drupal::service('email.validator')->isValid($form_state->getValue('email'))) {
      $form_state->setErrorByName('email', $this->t('The email address you entered is not valid.'));
    }

    if ($form_state->getValue('age') < 18) {
      $form_state->setErrorByName('age', $this->t('You must be at least 18 years old.'));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

    $name = $form_state->getValue('name');
    $email = $form_state->getValue('email');
    $age = $form_state->getValue('age');

    try {
      $this->database->insert('awesome_chat_basic_info')
        ->fields([
          'name' => $name,
          'email' => $email,
          'age' => $age,
          'created' => \Drupal::time()->getRequestTime(),
        ])
        ->execute();

      $this->messenger()->addMessage($this->t('Thank you, @name! Your information has been saved.', ['@name' => $name]));
      $this->logger('my_module')->notice('New submission from @name with email @email and age @age.', [
        '@name' => $name,
        '@email' => $email,
        '@age' => $age,
      ]);
    }
    catch (\Exception $e) {
      $this->messenger()->addError($this->t('An error occurred while saving your information. Please try again.'));
      $this->logger('my_module')->error('Database error saving submission: @error', ['@error' => $e->getMessage()]);
    }
  }

}