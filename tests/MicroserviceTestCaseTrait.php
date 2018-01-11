<?php

namespace go1\util\tests;

use Doctrine\DBAL\DriverManager;
use go1\app\App;
use go1\util\DB;

/**
 * Helper methods to make cross-service request testing simple.
 *
 * Example usage: https://gist.github.com/andytruong/4976ca6bb2f14adc1a0246ff9ddf94e0
 */
trait MicroserviceTestCaseTrait
{
    /**
     * @return App
     */
    abstract protected function getApp();

    protected function getServiceApplication($service): App
    {
        $here = $this->getApp();
        $serviceBaseDir = dirname(dirname(__DIR__));
        $cnf = "/{$serviceBaseDir}/{$service}/config.default.php";
        $cnf = require $cnf;
        $class = getenv('SERVICE_APP_NAME') ?: 'go1\\%service%\\App';
        $class = str_replace('%service%', $service, $class);
        $app = new $class($cnf);

        isset($cnf['dbOptions']) && $app->extend('dbs', function () use (&$here, &$cnf) {
            $return = [];

            foreach ($cnf['dbOptions'] as $name => $options) {
                if ($options == DB::connectionOptions('go1')) {
                    $return[$name] = $here['dbs']['default'];
                }
                elseif (isset($here['dbs'][$name])) {
                    $return[$name] = $here['dbs'][$name];
                }
                else {
                    $return[$name] = DriverManager::getConnection(['url' => 'sqlite://sqlite::memory:']);
                }
            }

            return $return;
        });

        return $app;
    }
}
