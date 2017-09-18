<?php

namespace go1\util\tests;

use go1\util\report\Export;
use go1\util\UtilServiceProvider;
use PHPUnit\Framework\TestCase;
use Pimple\Container;

class ReportTest extends TestCase
{
    public function testContainerValidation()
    {
        $c = new Container;
        $c
            ->register(new UtilServiceProvider, [
                    's3Options'    => [
                        'key' => 'testing key',
                        'secret' => 'testing secret',
                        'region' => 'testing region',
                        'bucket' => 'test bucket',
                        'version' => 'latest',
                        'endpoint' => 'test endpoint',
                    ],
                    'esOptions'    => [
                        'credential' => true,
                        'key' => 'testing key',
                        'secret' => 'testing secret',
                        'region' => 'testing region',
                        'endpoint' => 'http://es:9200',
                    ],
                ]);

        $this->assertTrue($c['report_export'] instanceof Export);
    }
}
