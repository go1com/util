<?php

namespace go1\util\award;

use Doctrine\DBAL\Connection;
use go1\util\plan\Plan;
use go1\util\plan\PlanRepository;
use go1\util\plan\PlanStatuses;
use go1\util\plan\PlanTypes;
use go1\util\schema\mock\AwardMockTrait;

class AwardTestData
{
    use AwardMockTrait;

    private $dbAward;
    public $award;
    public $awardItems;
    public $awardItemManuals;
    public $awardAchievements;
    public $awardEnrolments;
    public $plans;

    public static function create(Connection $dbAward)
    {
        $data = new self;
        $data->dbAward = $dbAward;

        return $data;
    }

    public function setAward(array $options = [])
    {
        if (isset($options['items'])) {
            unset($options['items']);
        }
        $awardId = $this->createAward($this->dbAward, $options);
        $this->award = AwardHelper::load($this->dbAward, $awardId);

        return $this;
    }

    public function setAwardItems(array $options = [])
    {
        $this->checkAward();

        foreach ($options as $option) {
            $awardItemIds[] = $this->createAwardItem(
                $this->dbAward,
                $this->award->revision_id,
                $option['entity_id'],
                $option['quantity'] ?? null,
                $option['weight'] ?? null
            );
        }
        $this->awardItems = isset($awardItemIds) ? AwardHelper::loadItems($this->dbAward, $awardItemIds) : [];

        return $this;
    }

    public function setAwardItemManuals(array $options = [])
    {
        $this->checkAward();

        foreach ($options as $option) {
            $option['award_id'] = $this->award->id;
            $awardItemManualIds[] = $this->createAwardItemManual($this->dbAward, $option);
        }
        $this->awardItemManuals = isset($awardItemManualIds) ? AwardHelper::loadManualItems($this->dbAward, $awardItemManualIds) : [];

        return $this;
    }

    public function setAwardAchievements(array $options = [])
    {
        $this->checkAward();

        foreach ($options as $option) {
            $achievementIds[] = $this->createAwardAchievement(
                $this->dbAward,
                $option['user_id'],
                $option['award_item_id'],
                $option['created'] ?? null
            );
        }
        $this->awardAchievements = isset($achievementIds) ? AwardHelper::loadAchievements($this->dbAward, $achievementIds) : [];

        return $this;
    }

    public function setAwardEnrolments(array $options = [])
    {
        $this->checkAward();

        foreach ($options as $option) {
            $option['award_id'] = $this->award->id;
            $option['instance_id'] = $options['instance_id'] ?? $this->award->instance_id;
            $awardEnrolmentIds[] = $this->createAwardEnrolment($this->dbAward, $option);
        }
        $this->awardEnrolments = isset($awardEnrolmentIds) ? AwardHelper::loadEnrolments($this->dbAward, $awardEnrolmentIds) : [];

        return $this;
    }

    public function setPlans(PlanRepository $rPlan, array $options = [])
    {
        $this->checkAward();

        foreach ($options as $option) {
            $plan = Plan::create((object) [
                'user_id'      => $option['user_id'] ?? $option['assigner_id'],
                'assigner_id'  => $option['assigner_id'],
                'instance_id'  => $option['instance_id'] ?? $this->award->instance_id,
                'entity_type'  => PlanTypes::ENTITY_AWARD,
                'entity_id'    => $this->award->revision_id,
                'status'       => $option['status'] ?? PlanStatuses::ASSIGNED,
                'due_date'     => $option['due_date'] ?? null,
                'created_date' => $option['created_date'] ?? time(),
                'data'         => $option['data'] ?? null,
            ]);
            $planIds[] = $rPlan->create($plan);
        }
        $this->plans = isset($planIds) ? $rPlan->loadMultiple($planIds) : [];

        return $this;
    }

    private function checkAward()
    {
        if (!$this->award) {
            $this->setAward();
        }
    }
}
