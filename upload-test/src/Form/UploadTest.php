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

    $form['anomalies_file'] = [
      '#type' => 'managed_file',
      '#title' =>  $this->t('Valkyrie Anomalies File'),
      '#description' => $this->t('Upload a CSV file.'),
      '#upload_validators' => [
        'file_validate_extensions' => ['csv'],
        'file_validate_mime_type' => ['text/csv'],
      ],
      '#required' => TRUE,
      '#upload_location' => 'public://csv/',
    ];

    $form['patrol_photos'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Patrol Photos'),
      '#description' => $this->t('Upload one or more images.'),
      '#multiple' => TRUE,
      '#upload_validators' => [
        'file_validate_extensions' => ['jpg jpeg'],
        'file_validate_mime_type' => ['image/jpeg'],
      ],
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

    $csv_fid = $form_state->getValue('csv_file')[0]; // Get the file ID.
    if (!$csv_fid) {
      $form_state->setErrorByName('csv_file', $this->t('A CSV file is required.'));
    }

    $image_fids = $form_state->getValue('image_files');
    if (empty($image_fids)) {
      $form_state->setErrorByName('image_files', $this->t('At least one image is required.'));
    }
  }

  
  public function submitForm(array &$form, FormStateInterface $form_state) {
    
    $csv_fid = $form_state->getValue('csv_file')[0]; // Get the file ID.
    $image_fids = $form_state->getValue('image_files');

    // Load the CSV file.
    $csv_file = \Drupal\file\Entity\File::load($csv_fid);
    if (!$csv_file) {
      $this->messenger()->addError($this->t('Failed to load the CSV file.'));
      return;
    }

    // Process the CSV file (example: read the first line).
   $csv_uri = $csv_file->getFileUri();
    $handle = fopen($csv_uri, 'r');
    $csv_data = [];
    if ($handle) {
      $csv_data = fgetcsv($handle); // Get the first line of the CSV.
      fclose($handle);
      if ($csv_data) {
        $this->messenger()->addMessage($this->t('First line of CSV: @data', ['@data' => implode(', ', $csv_data)]));
      }
    }
    else {
      $this->messenger()->addError($this->t('Could not open the csv file.'));
      return;
    }

    $image_uris = [];
    $images_data = [];
    // Load and process the image files.
    foreach ($image_fids as $image_fid) {
      $image_file = \Drupal\file\Entity\File::load($image_fid);
      if ($image_file) {
        $image_uri = $image_file->getFileUri();
        $image_uris[] = $image_uri;
        $images_data[] = [
          'uri' => $image_uri,
          'alt' => $image_file->getFilename(), // You might want a separate field for alt text.
        ];
      }
    }
    if (count($image_uris) > 0) {
      $this->messenger()->addMessage($this->t('Uploaded images: @images', ['@images' => implode(', ', $image_uris)]));
    }


    $node = Node::create([
      'type' => 'valkyrie_upload',
      'field_csv_data' => serialize($csv_data), // Store the CSV data.  Consider creating a dedicated field.
      'field_image_files' => $images_data, // Store the image file information.  Adjust field name as needed.
    ]);


    $node->save();

    // Set a success message.
    $this->messenger()->addMessage($this->t('Node created successfully.'));

    $this->loggerFactory->get('my_node_creator')->notice('Node created');
    // Redirect to the node's view page.
    $form_state->setRedirect('entity.node.canonical', ['node' => $node->id()]);

  }

}
