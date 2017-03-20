<?php

namespace go1\clients;

use Doctrine\Common\Cache\CacheProvider;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use GuzzleHttp\Client;
use InvalidArgumentException;
use Pimple\Container;
use Silex\Provider\DoctrineServiceProvider;

class AccountsClient
{
    private $go1;
    private $cache;
    private $accountsName;
    private $connection;

    public function __construct(Connection $go1, CacheProvider $cache, string $accountsName)
    {
        $this->go1 = $go1;
        $this->cache = $cache;
        $this->accountsName = $accountsName;
    }

    public function db(bool $refresh = false): Connection
    {
        if (!$this->connection) {
            try {
                $c = new Container;
                $c->register(new DoctrineServiceProvider, ['db.options' => $this->dbOptions($refresh)]);
                $this->connection = $c['dbs']['default'];
                $this->connection->executeQuery('SELECT 1');
            }
            catch (DBALException $e) {
                if (!$refresh) {
                    $this->connection = null;

                    return $this->db(true);
                }

                throw $e;
            }
        }

        return $this->connection;
    }

    private function dbOptions(bool $refresh = false): array
    {
        $cacheId = substr(md5($this->accountsName), 16) . ':dbOptions';

        if (!$refresh) {
            if ($this->cache->contains($cacheId)) {
                if ($options = $this->cache->fetch($cacheId)) {
                    return $options;
                }
            }
        }

        $key = 'SELECT uuid FROM gc_user WHERE instance = ? AND mail = ?';
        $key = $this->go1->fetchColumn($key, [$this->accountsName, "user.1@{$this->accountsName}"]);
        $url = "http://{$this->accountsName}/api/1.0/custom/gc/db-options/default.json?api_key={$key}";
        $connect = json_decode((new Client)->get($url)->getBody()->getContents(), true);
        $options = [
            'driver'        => 'pdo_mysql',
            'dbname'        => $connect['database'],
            'host'          => $connect['host'],
            'user'          => $connect['username'],
            'password'      => $connect['password'],
            'port'          => $connect['port'],
            'driverOptions' => [1002 => 'SET NAMES utf8'],
        ];

        $this->cache->save($cacheId, $options);

        return $options;
    }

    public function bump(string $type, string $bundle): int
    {
        $tables = [
            'node'     => ['node', 'type'],
            'profile2' => ['profile', 'type'],
            'simple'   => ['eck_simple', 'type'],
        ];

        if (!isset($tables[$type])) {
            throw new InvalidArgumentException('Unsupported entity type: ' . $type);
        }

        $bundles = ['instance', 'course', 'enrollment', 'learning_item', 'domain', 'relationship'];
        if (getenv('MONOLITH') && strpos($this->accountsName, '.gocatalyze.com') && $type == 'simple' && in_array($bundle, $bundles)) {
            switch ($bundle) {
                case 'instance':
                    $sql = 'SELECT MAX(id) FROM gc_instance';
                    break;
                case 'course':
                case 'learning_item':
                    $sql = 'SELECT MAX(id) FROM gc_lo';
                    break;
                case 'enrollment':
                    $sql = 'SELECT MAX(id) FROM gc_enrolment';
                    break;
                case 'domain':
                    $sql = 'SELECT MAX(id) FROM gc_domain';
                    break;
                case 'relationship':
                    $sql = 'SELECT MAX(id) FROM gc_ro';
                    break;
            }
            return (int) $this->go1->fetchColumn($sql) + 1;
        }

        list($table, $columnBundle) = $tables[$type];
        $db = $this->db();
        $db->insert($table, [$columnBundle => $bundle]);

        return $db->lastInsertId($table);
    }
}
