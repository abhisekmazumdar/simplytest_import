<?php

namespace Drupal\simplytest_import;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigManagerInterface;
use GuzzleHttp\ClientInterface;

/**
 * Class SimplytestImportService.
 */
class SimplytestImportService {

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
   * Constructs a new SimplytestImportService object.
   */
  public function __construct(ConfigManagerInterface $config_manager, ClientInterface $http_client) {
    $this->configManager = $config_manager;
    $this->httpClient = $http_client;
  }

  /**
   * Get data from the API.
   *
   * @param string|$url
   *  Url for the api.
   * @param string $type
   *
   * @return bool
   */
  public function dataProvider($url, $type = 'list') {
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
    return $data[$type];
  }

  /**
   * Get the total count.
   *
   * @param string $last
   *   The last data url.
   *
   * @return string|null
   *   Return the total page count.
   */
  public function getTotalDataCount($last) {
    if (preg_match('/&page=(\d*)/', $last, $count)) {
      return $count[1];
    }
    return NULL;
  }

}
