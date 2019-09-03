<?php

namespace Drupal\simplytest_import\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\simplytest_import\SimplytestImportService;

/**
 * Class ImportForm.
 */
class ImportForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'automatic_import_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['type'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Importing Type:'),
      '#options' => [
        'project_module' => $this->t('Modules'),
        'project_theme' => $this->t('Themes'),
        'project_distribution' => $this->t('Distributions'),
      ],
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $type = $form_state->getValue('type');
    // Import the core data.
    import_items([$this->getCoreData()]);
    foreach ($type as $value) {
      if ($value) {
        $this->import($value);
      }
    }
  }

  /**
   * Get core data from drupal.org.
   */
  protected function getCoreData() {
    return [
      'title' => 'Drupal core',
      'shortname' => 'drupal',
      'sandbox' => "0",
      'type' => 'Drupal core',
      'creator' => 'dries',
    ];
  }

  /**
   *
   *
   * @param $type
   */
  protected function import($type) {
    $items = data_provider($type);
    if (!empty($items)) {
      $operations = [];
      $count = $this->getTotalDataCount($items['last']);
      for ($index = 0; $index < $count; $index++) {
        $operations[] = ['batch_fetch_import_process', [$index, $type]];
      }
      $batch = [
        'title' => $this->t('Importing @num @type',
          [
            '@num' => $count,
            '@type' => str_replace('project_', '', $type),
          ]
        ),
        'operations' => $operations,
        'finished' => 'batch_finished',
      ];
      batch_set($batch);
    }
  }

  /**
   * Get the total page count from a particular request.
   *
   * @param string $lastUrl
   *   The last data url.
   *
   * @return string|null
   *   Return the total page count.
   */
  protected function getTotalDataCount($lastUrl) {
    if (preg_match('/&page=(\d*)/', $lastUrl, $count)) {
      return $count[1];
    }
    return NULL;
  }

}
