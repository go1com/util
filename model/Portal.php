<?php

namespace go1\util\model;

use Doctrine\DBAL\Connection;
use go1\util\DB;
use go1\util\edge\EdgeTypes;
use go1\util\portal\PortalChecker;
use go1\util\portal\PortalPricing;
use PDO;
use stdClass;

class Portal
{
    /** @var integer */
    public $id, $created, $timestamp;

    /** @var string */
    public $title, $version, $primaryDomain, $userPlan;

    /** @var bool */
    public $status, $isPrimary;

    /** @var string[] */
    public $domains;

    /** @var object */
    public $data;

    public $plan;

    /**
     * @param stdClass        $row
     * @param Connection|null $db
     * @return Portal
     */
    public static function create(stdClass $row, Connection $db = null)
    {
        $data = is_string($row->data) ? json_decode($row->data) : $row->data;

        $portal = new Portal;
        $portal->id = $row->id;
        $portal->title = $row->title;
        $portal->status = $row->status;
        $portal->isPrimary = $row->is_primary;
        $portal->version = $row->version;
        $portal->timestamp = $row->timestamp;
        $portal->created = $row->created;
        $portal->data = clone $data;
        $portal->domains = [];
        $portal->primaryDomain = (new PortalChecker)->getPrimaryDomain($row);

        if ($db) {
            $domainIds = 'SELECT target_id FROM gc_ro WHERE type = ? AND source_id = ?';
            $domainIds = $db->executeQuery($domainIds, [EdgeTypes::HAS_DOMAIN, $portal->id])->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($domainIds)) {
                $sql = 'SELECT title FROM gc_domain WHERE id IN (?)';
                $portal->domains = $db->executeQuery($sql, [$domainIds], [DB::INTEGERS])->fetchAll(PDO::FETCH_COLUMN);
            }
        }

        if (isset($data->user_plan)) {
            $portalData = (object)['data' => $data];
            list($price, $currency) = PortalPricing::getPrice($portalData);
            $trial = PortalPricing::getTrial($portalData);

            $portal->plan = (object)[
                'license'          => PortalPricing::getLicenses($portalData),
                'product'          => PortalPricing::getProduct($portalData),
                'regional'         => PortalPricing::getRegional($portalData),
                'trial'            => $trial,
                'trialText'        => PortalPricing::PLAN_STATUS[$trial],
                'expire'           => PortalPricing::getExpire($portalData),
                'price'            => $price,
                'currency'         => $currency,
                'manual_price'     => PortalPricing::getManualPrice($portalData),
                'custom_contract'  => PortalPricing::getCustomContract($portalData),
                'partner_customer' => PortalPricing::getPartnerCustomer($portalData)
            ];
        }

        $portal->regionalOptions = array_combine(PortalPricing::REGIONAL, PortalPricing::REGIONAL);
        $portal->productOptions = [PortalPricing::PRODUCT_PLATFORM, PortalPricing::PRODUCT_PREMIUM];
        $portal->trialOptions   = PortalPricing::PLAN_STATUS;

        return $portal;
    }
}
