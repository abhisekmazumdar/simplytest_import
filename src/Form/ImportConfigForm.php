<?php

namespace Drupal\simplytest_import\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configurations of import in batch.
 */
class ImportConfigForm extends ConfigFormBase {

  /**
   * {@inheritDoc}
   */
  protected function getEditableConfigNames() {
    return [
      'simplytest_import.settings'
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'simplytest_import_config';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('simplytest_import.settings');
    $form['count'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Count of projects to import per operation'),
      '#default_value' => $config->get('count'),
    ];
    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('simplytest_import.settings')
      ->set('count', $form_state->getValue('count'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
