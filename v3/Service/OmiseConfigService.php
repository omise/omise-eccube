<?php
namespace Plugin\OmisePaymentGateway\Service;

use Eccube\Application;
use Symfony\Component\Yaml\Yaml;

class OmiseConfigService {
    public $app;
    public $pluginConfig;
    
    public function __construct(Application $app) {
        $this->app = $app;
        $this->pluginConfig = Yaml::parse(__DIR__ . '/../config.yml');
    }
    
    public function getPluginConfig() {
        return $this->pluginConfig;
    }
}
