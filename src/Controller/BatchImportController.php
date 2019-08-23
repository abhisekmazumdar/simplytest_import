<?php

namespace Drupal\simplytest_import\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;
use Drupal\simplytest_import\SimplytestImportService;
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
   * @var \Drupal\simplytest_import\SimplytestImportService
   */
  protected $importService;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    Connection $database,
    ConfigManagerInterface $config_manager,
    Client $http_client,
    SimplytestImportService $importService
  ) {
    $this->database = $database;
    $this->configManager = $config_manager;
    $this->httpClient = $http_client;
    $this->importService = $importService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('config.manager'),
      $container->get('http_client'),
      $container->get('simplytest_import.import')
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
    $url = DrupalUrls::ORG_API . 'node.json?type=project_module&page=0';
    $data = [];
    $items = $this->importService->dataProvider($url);
    $count = $this->importService->getTotalDataCount(
      $this->importService->dataProvider($url, 'last')
    );
    for ($index = $count; $index >= 870; $index--) {
      foreach ($items as $item) {
        $data = [
          'title' => $item['title'],
          'shortname' => $item['field_project_machine_name'],
          'sandbox' => $item['field_project_type'] === 'sandbox' ? 1 : 0,
          'type' => 'Module',
          'creator' => $item['author']['name'],
        ];
      }
      $batch = [
        'title' => $this->t('Inserting Projects'),
        'operations' => [
          $this->myFac($data),
          [],
        ],
        'init_message' => t('Saint of the Day migration is starting.'),
        'progress_message' => t('some thing cooking.'),
        'finished' => $this->finished_callback(),
      ];
      batch_set($batch);
      $items = $this->importService->dataProvider(
        $url . '&page=' . $index
      );
    }
    return NULL;
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

  function myFac(array $data) {
    $project = SimplytestProject::create($data);
    $project->save();
  }

  function finished_callback() {
    return new RedirectResponse('/');
  }

}
