<?php
namespace Plugin\OmisePaymentGateway\Service;

use Eccube\Application;
use Eccube\Common\Constant;
use Symfony\Component\Yaml\Yaml;

/**
 * プラグイン設定処理
 */
class RemiseConfigService
{
    /**
     * Application
     */
    public $app;

    /**
     * config情報
     */
    public $pluginConfig;

    /**
     * コンストラクタ
     *
     * @param  Application  $app  
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        // configファイル読込
        $this->pluginConfig = Yaml::parse(__DIR__ . '/../config.yml');
    }

    /**
     * config取得
     *
     * @return  array  
     */
    public function getPluginConfig()
    {
        return $this->pluginConfig;
    }

}
