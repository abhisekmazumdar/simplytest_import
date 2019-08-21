<?php

namespace Drupal\simplytest_import\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;
use Drupal\simplytest_import\InsertProject;
use Drupal\simplytest_projects\DrupalUrls;
use Drupal\simplytest_projects\Entity\SimplytestProject;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Driver\mysql\Connection;
use Drupal\Core\Config\ConfigManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class BatchImportController.
 */
class BatchImportController extends ControllerBase {

  /**
   * Drupal\Core\Database\Driver\mysql\Connection definition.
   *
   * @var \Drupal\Core\Database\Driver\mysql\Connection
   */
  protected $database;

  /**
   * Drupal\Core\Config\ConfigManagerInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigManagerInterface
   */
  protected $configManager;

  /**
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * {@inheritdoc}
   */
  public function __construct(Connection $database, ConfigManagerInterface $config_manager, Client $http_client) {
    $this->database = $database;
    $this->configManager = $config_manager;
    $this->httpClient = $http_client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('config.manager'),
      $container->get('http_client')
    );
  }

  /**
   * Build For the Import process.
   *
   * @return array
   *   The build for the controller.
   */
  public function insert() {
    $data = [];
    //$data = $this->getCoreData();
    $data = $this->getAllModuleData();
//    $data = $this->getAllThemeData();
//    $data = $this->getAllDistributionData();
    return new RedirectResponse('/');
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
   * Get all module data from drupal.org.
   */
  protected  function getAllModuleData() {
    $operations = [];
    $info = [];
    $page = '';
    $url = DrupalUrls::ORG_API . 'node.json?type=project_module';
    $result = $this->httpClient->get($url);
    if ($result->getStatusCode() != 200 || empty($result->getBody())) {
      $this->log->warning('Failed to fetch initial data.');
      return FALSE;
    }
    $data = Json::decode($result->getBody());
    if ($data === NULL) {
      $this->log->warning('Failed to fetch initial data.');
      return FALSE;
    }
    while (array_key_exists('next', $data)) {
      if ($page) {
        $result = $this->httpClient->get($url . '&' . $page);
        $data = Json::decode($result->getBody());
      }
      foreach ($data['list'] as $module) {
        $info[] = [
          'title' => $module['title'],
          'shortname' => $module['field_project_machine_name'],
          'sandbox' => $module['field_project_type'] === 'sandbox' ? 1 : 0,
          'type' => 'Module',
          'creator' => $module['author']['name'],
        ];
      }
      $page = explode('&', $data['next'])[1];
      if ($page === 'page=5') {
        break;
      }
    }
//    $operations[] = [InsertProject::myFac, $info];

    $batch = [
      'title' => $this->t('Inserting Projects'),
      'operations' => [
        $this->myFac($info),
        [],
      ],
      'init_message' => t('Saint of the Day migration is starting.'),
      'progress_message' => t('some thing cooking.'),
      'finished' => $this->finished_callback(),
    ];
    batch_set($batch);

    return $result;
  }

  /**
   * Get all theme data from drupal.org.
   */
  protected  function getAllThemeData() {
    $data = [];
    return $data;
  }

  /**
   * Inserts distribution data from drupal.org.
   */
  protected  function getAllDistributionData() {
    $data = [];
    return $data;
  }

  function myFac(array $info) {
    foreach ($info as $item) {
      $project = SimplytestProject::create($item);
      $project->save();
    }
  }

  function finished_callback() {
    return new RedirectResponse('/');
  }

}
