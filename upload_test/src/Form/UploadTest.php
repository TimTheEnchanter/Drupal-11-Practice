<?php

namespace Drupal\upload_test\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\Entity\File;

/**
 * Implements the SimpleForm form controller.
 *
 * This example demonstrates a simple form with a single text input element. We
 * extend FormBase which is the simplest form base class used in Drupal.
 *
 * @see \Drupal\Core\Form\FormBase
 */
class UploadTest extends FormBase {

  protected $routeMatch;
  protected $loggerFactory;
  protected $fileSystem;



  public function __construct(RouteMatchInterface $route_match, LoggerChannelFactoryInterface $logger_factory, FileSystemInterface $file_system) {
    $this->routeMatch = $route_match;
    $this->loggerFactory = $logger_factory;
    $this->fileSystem = $file_system;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_route_match'),
      $container->get('logger.factory'),
      $container->get('file_system')
    );
  }

  public function getFormId() {
    return 'upload_test_form';
  }
  
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['#attributes']['enctype'] = 'multipart/form-data';

    $form['anomalies_file'] = [
      '#type' => 'file',
      '#title' =>  $this->t('Valkyrie Anomalies File'),
      '#description' => $this->t('Upload a CSV file.'),
      //'#upload_validators' => [
      //  'file_validate_extensions' => ['csv'],
      //  'file_validate_mime_type' => ['text/csv'],
      //],
      '#required' => TRUE,
      '#upload_location' => 'public://csv/',
    ];

    $form['patrol_photos'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Patrol Photos'),
      '#description' => $this->t('Upload one or more images.'),
      '#multiple' => TRUE,
      //'#upload_validators' => [
      //  'file_validate_extensions' => ['jpg jpeg'],
      //  'file_validate_mime_type' => ['image/jpeg'],
      //],
      '#required' => TRUE,
      '#upload_location' => 'public://images/',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Create Node'),
    ];

    return $form;
  }

 
  public function validateForm(array &$form, FormStateInterface $form_state) {

    /*
    $csv_fid = $form_state->getValue('csv_file')[0]; // Get the file ID.
    if (!$csv_fid) {
      $form_state->setErrorByName('csv_file', $this->t('A CSV file is required.'));
    }

    $image_fids = $form_state->getValue('image_files');
    if (empty($image_fids)) {
      $form_state->setErrorByName('image_files', $this->t('At least one image is required.'));
    }
      */
  }

  
  public function submitForm(array &$form, FormStateInterface $form_state) {
    
    $csv_fid = $form_state->getValue('anomalies_file');
    $image_fids = $form_state->getValue('patrol_photos');

    // Make the uploaded files permanent.
    // For CSV file:
    if (!empty($csv_fid)) {
      $file = File::load(reset($csv_fid)); // Get the first (and only) FID.
      if ($file) {
        $file->setPermanent();
        $file->save();
        // Update the form state value with the permanent file ID.
        $form_state->setValue('anomalies_file', $file->id());
        $this->messenger()->addStatus($this->t('CSV file "@filename" uploaded permanently.', ['@filename' => $file->getFilename()]));
      }
    }

    // For image files:
    $permanent_image_fids = [];
    if (!empty($image_fids)) {
      foreach ($image_fids as $fid) {
        $file = File::load($fid);
        if ($file) {
          $file->setPermanent();
          $file->save();
          $permanent_image_fids[] = $file->id();
          $this->messenger()->addStatus($this->t('Image file "@filename" uploaded permanently.', ['@filename' => $file->getFilename()]));
        }
      }
      // Update the form state value with the permanent file IDs.
      $form_state->setValue('patrol_photos', $permanent_image_fids);
    }

    // At this point, the files are permanent, and their FIDs are updated in $form_state.
    // The custom hook will now receive the form state with permanent FIDs.

    $this->messenger()->addStatus($this->t('Form submitted successfully. Processing in custom hook.'));
    // No redirect here, let the hook handle further actions or redirect.
  }

}
