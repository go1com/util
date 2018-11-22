<?php

namespace go1\util\award;

use Doctrine\DBAL\Connection;
use go1\util\enrolment\EnrolmentHelper;
use go1\util\plan\Plan;
use go1\util\plan\PlanRepository;
use go1\util\plan\PlanStatuses;
use go1\util\plan\PlanTypes;
use go1\util\schema\mock\AwardMockTrait;
use go1\util\user\UserHelper;

class AwardTestHelper
{
    use AwardMockTrait;

    private $db;
    public  $award;
    public  $awardItems;
    public  $awardItemManuals;
    public  $awardAchievements;
    public  $awardEnrolments;
    public  $awardItemEnrolments;
    public  $plans;

    public static function create(Connection $db)
    {
        $helper = new self;
        $helper->db = $db;

        return $helper;
    }

    public function setAward(array $options = [])
    {
        if (isset($options['items'])) {
            unset($options['items']);
        }

        $awardId = $this->createAward($this->db, $options);
        $this->award = AwardHelper::load($this->db, $awardId);

        return $this;
    }

    public function setAwardItems(array $options = [])
    {
        $this->checkAward();

        foreach ($options as $option) {
            $awardItemIds[] = $this->createAwardItem(
                $this->db,
                $this->award->revision_id,
                $option['type'],
                $option['entity_id'],
                $option['quantity'] ?? null,
                $option['parent_award_item_id'] ?? null,
                $option['weight'] ?? null,
                $option['mandatory'] ?? false
            );
        }
        $this->awardItems = isset($awardItemIds) ? AwardHelper::loadItems($this->db, $awardItemIds) : [];

        return $this;
    }

    public function addAwardItem(array $option = [])
    {
        $this->checkAward();
        $awardItemId = $this->createAwardItem(
            $this->db,
            $this->award->revision_id,
            $option['type'],
            $option['entity_id'],
            $option['quantity'] ?? null,
            $option['parent_award_item_id'] ?? null,
            $option['weight'] ?? null,
            $option['mandatory'] ?? false
        );
        $this->awardItems[] = AwardHelper::loadItem($this->db, $awardItemId);

        return $this;
    }

    public function setAwardItemManuals(array $options = [])
    {
        $this->checkAward();

        foreach ($options as $option) {
            $option['award_id'] = $this->award->id;
            $awardItemManualIds[] = $this->createAwardItemManual($this->db, $option);
        }
        $this->awardItemManuals = isset($awardItemManualIds) ? AwardHelper::loadManualItems($this->db, $awardItemManualIds) : [];

        return $this;
    }

    public function setAwardAchievements(array $options = [])
    {
        $this->checkAward();

        foreach ($options as $option) {
            $achievementIds[] = $this->createAwardAchievement(
                $this->db,
                $option['user_id'],
                $option['award_item_id'],
                $option['created'] ?? null
            );
        }
        $this->awardAchievements = isset($achievementIds) ? AwardHelper::loadAchievements($this->db, $achievementIds) : [];

        return $this;
    }

    public function setAwardEnrolments(array $options = [])
    {
        $this->checkAward();

        foreach ($options as $option) {
            $option['award_id'] = $this->award->id;
            $option['instance_id'] = $options['instance_id'] ?? $this->award->instance_id;
            $awardEnrolmentIds[] = $this->createAwardEnrolment($this->db, $option);
        }
        $this->awardEnrolments = isset($awardEnrolmentIds) ? AwardHelper::loadEnrolments($this->db, $awardEnrolmentIds) : [];

        return $this;
    }

    public function setPlans(PlanRepository $rPlan, array $options = [])
    {
        $this->checkAward();

        foreach ($options as $option) {
            $plan = Plan::create((object) [
                'user_id'      => $option['user_id'],
                'assigner_id'  => $option['assigner_id'] ?? null,
                'instance_id'  => $option['instance_id'] ?? $this->award->instance_id,
                'entity_type'  => PlanTypes::ENTITY_AWARD,
                'entity_id'    => $this->award->id,
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

    public function populateAwardItemEnrolments(Connection $go1)
    {
        if (!$this->award || !$this->awardItems || !$this->awardEnrolments) {
            return;
        }

        foreach ($this->awardEnrolments as $awardEnrolment) {
            foreach ($this->awardItems as $awardItem) {
                $itemIsAward = (AwardItemTypes::AWARD == $awardItem->type);

                if ($itemIsAward && is_null($awardItem->quantity)) {
                    $childAward = AwardHelper::load($this->db, $awardItem->entity_id);
                    $quantity = $childAward->quantity ?? 0;
                } else {
                    $quantity = $awardItem->quantity ?? 0;
                }

                if ($itemIsAward) {
                    $enrolment = AwardEnrolmentHelper::find($this->db, $awardItem->entity_id, $awardEnrolment->user_id, $awardEnrolment->instance_id);
                    $enrolment->pass = (AwardEnrolmentStatuses::COMPLETED == $enrolment->status) ? 1 : 0;
                    $enrolment->status = AwardEnrolmentStatuses::toString($enrolment->status);
                } else {
                    $user = UserHelper::load($go1, $awardEnrolment->user_id);
                    $enrolment = EnrolmentHelper::loadByLoProfileAndPortal($go1, $awardItem->entity_id, $user->profile_id, $awardEnrolment->instance_id);
                }

                $awardItemEnrolmentId = $this->createAwardItemEnrolment($this->db, [
                    'award_id'    => $this->award->id,
                    'user_id'     => $awardEnrolment->user_id,
                    'instance_id' => $awardEnrolment->instance_id,
                    'entity_id'   => $awardItem->entity_id,
                    'type'        => (AwardItemTypes::AWARD == $awardItem->type) ? AwardItemTypes::AWARD : AwardItemTypes::LO,
                    'status'      => $enrolment->status,
                    'pass'        => $enrolment->pass,
                    'quantity'    => $quantity,
                    'remote_id'   => $enrolment->id,
                ]);
                $this->awardItemEnrolments[] = AwardItemEnrolmentHelper::load($this->db, $awardItemEnrolmentId);
            }
        }
    }

    private function checkAward()
    {
        if (!$this->award) {
            $this->setAward();
        }
    }
}
