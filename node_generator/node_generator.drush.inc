<?php

/**
 * @file
 * Drush commands for the node_generator module.
 */

use Drush\Commands\DrushCommands;

/**
 * Class NodeGeneratorCommands.
 */
class NodeGeneratorCommands extends DrushCommands {

  /**
   * Installs the required dependencies for node_generator.
   *
   * @command node_generator:install-dependencies
   * @aliases ymid
   * @usage drush node_generator:install-dependencies
   * Installs the field and token modules.
   */
  public function installDependencies() {
    $modules_to_enable = ['field', 'token'];
    $missing_modules = [];

    foreach ($modules_to_enable as $module) {
      if (!\Drupal::moduleHandler()->moduleExists($module)) {
        $missing_modules[] = $module;
      }
    }

    if (!empty($missing_modules)) {
      if ($this->confirm(dt('The following modules are required/recommended by node_generator and are not currently enabled: !modules. Do you want to enable them?', ['!modules' => implode(', ', $missing_modules)]))) {
        drush_invoke_process('@self', 'pm-enable', $missing_modules);
        $this->logger()->success(dt('Successfully enabled: !modules', ['!modules' => implode(', ', $missing_modules)]));
      } else {
        $this->logger()->warning(dt('Dependency installation skipped. Your Module may not function correctly.'));
      }
    } else {
      $this->logger()->success(dt('All dependencies for node_generator are already enabled.'));
    }
  }

}