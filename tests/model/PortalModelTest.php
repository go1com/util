<?php

namespace go1\util\tests\model;

use go1\util\model\Portal;
use go1\util\portal\PortalHelper;
use go1\util\portal\PortalPricing;
use go1\util\schema\mock\PortalMockTrait;
use go1\util\schema\mock\UserMockTrait;
use go1\util\tests\UtilTestCase;

class PortalModelTest extends UtilTestCase
{
    use PortalMockTrait;
    use UserMockTrait;

    public function test()
    {
        $data = [
            'title'      => 'az.mygo1.com',
            'status'     => 1,
            'is_primary' => 1,
            'version'    => PortalHelper::STABLE_VERSION,
            'data'       => [
                'user_plan'     => [
                    'license'  => 100,
                    'trial'    => 1,
                    'expire'   => time(),
                    'product'  => PortalPricing::PRODUCT_PLATFORM,
                    'regional' => PortalPricing::REGIONAL_DEFAULT,
                ],
                'configuration' => [
                    'site_name' => 'foo',
                ],
            ],
            'timestamp'  => time(),
            'created'    => time(),
        ];

        $id = $this->createPortal($this->db, $data);
        $this->createPortalDomain($this->db, $id, 'domain.go1.com');
        $portal = PortalHelper::load($this->db, $id);
        $model = Portal::create($portal, $this->db);

        $this->assertEquals($data['title'], $model->title);
        $this->assertEquals($data['status'], $model->status);
        $this->assertEquals($data['is_primary'], $model->isPrimary);
        $this->assertEquals($data['version'], $model->version);
        $this->assertEquals($data['timestamp'], $model->timestamp);
        $this->assertEquals($data['created'], $model->created);
        $this->assertEquals($data['data']['user_plan']['license'], $model->plan->license);
        $this->assertEquals($data['data']['user_plan']['trial'], $model->plan->trial);
        $this->assertEquals(PortalPricing::PLAN_STATUS[$model->plan->trial], $model->plan->trialText);
        $this->assertEquals($data['data']['user_plan']['expire'], $model->plan->expire);
        $this->assertEquals($data['data']['user_plan']['product'], $model->plan->product);
        $this->assertEquals($data['data']['user_plan']['regional'], $model->plan->regional);
        $this->assertEquals(0, $model->plan->price);
        $this->assertEquals('AUD', $model->plan->currency);

        $this->assertEquals($data['data']['configuration']['site_name'], $model->data->configuration->site_name);
        $this->assertTrue(in_array('domain.go1.com', $model->domains));
    }
}
