<?php

namespace Drupal\simplytest_import\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigManagerInterface;
use GuzzleHttp\ClientInterface;

/**
 * Class AutomaticImportForm.
 */
class AutomaticImportForm extends FormBase {

  /**
   * Drupal\Core\Config\ConfigManagerInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigManagerInterface
   */
  protected $configManager;

  /**
   * GuzzleHttp\ClientInterface definition.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Constructs a new AutomaticImportForm object.
   */
  public function __construct(
    ConfigManagerInterface $config_manager,
    ClientInterface $http_client
  ) {
    $this->configManager = $config_manager;
    $this->httpClient = $http_client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.manager'),
      $container->get('http_client')
    );
  }

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
    $form['count'] = [
      '#type' => 'number',
      '#title' => $this->t('Count of projects to import per operation'),
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
  public function validateForm(array &$form, FormStateInterface $form_state) {
    foreach ($form_state->getValues() as $key => $value) {
      // @TODO: Validate fields.
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Display result.
    foreach ($form_state->getValues() as $key => $value) {
      \Drupal::messenger()->addMessage($key . ': ' . ($key === 'text_format'?$value['value']:$value));
    }
  }

}
