<?php

namespace go1\util\tests\model;

use go1\util\edge\EdgeTypes;
use go1\util\model\Portal;
use go1\util\portal\PortalHelper;
use go1\util\portal\PortalPricing;
use go1\util\schema\mock\InstanceMockTrait;
use go1\util\schema\mock\UserMockTrait;
use go1\util\tests\UtilTestCase;

class PortalModelTest extends UtilTestCase
{
    use InstanceMockTrait;
    use UserMockTrait;

    public function test()
    {
        $data = [
            'title'      => 'az.mygo1.com',
            'status'     => 1,
            'is_primary' => 1,
            'version'    => PortalHelper::STABLE_VERSION,
            'data'       => [
                'user_plan' => [
                    'license'   => 100,
                    'product'   => PortalPricing::PRODUCT_PLATFORM,
                    'regional'  => PortalPricing::REGIONAL_DEFAULT
                ],
                'configuration' => [
                    'site_name' => 'foo'
                ]
            ],
            'timestamp'  => time(),
            'created'    => time(),
        ];

        $id = $this->createInstance($this->db, $data);

        $this->db->insert('gc_domain', ['title' => 'domain.go1.com']);
        $domainId = $this->db->lastInsertId('gc_instance');
        $this->link($this->db, EdgeTypes::HAS_DOMAIN, $id, $domainId);

        $portal = PortalHelper::load($this->db, $id);
        $model = Portal::create($portal, $this->db);

        $this->assertEquals($data['title'], $model->title);
        $this->assertEquals($data['status'], $model->status);
        $this->assertEquals($data['is_primary'], $model->isPrimary);
        $this->assertEquals($data['version'], $model->version);
        $this->assertEquals($data['timestamp'], $model->timestamp);
        $this->assertEquals($data['created'], $model->created);
        $this->assertEquals($data['data']['user_plan']['license'], $model->plan->license);
        $this->assertEquals($data['data']['user_plan']['product'], $model->plan->product);
        $this->assertEquals($data['data']['user_plan']['regional'], $model->plan->regional);
        $this->assertEquals($data['data']['configuration']['site_name'], $model->data->configuration->site_name);
        $this->assertEquals(2400, $model->plan->price);
        $this->assertEquals('AUD', $model->plan->currency);
        $this->assertTrue(in_array('domain.go1.com', $model->domains));
    }
}
