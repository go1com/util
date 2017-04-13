<?php

namespace go1\util\portal;

class PortalPricing
{
    CONST USER_LICENSES_MULTIPLY_RATE = 2; // @see GO1P-7405

    private $validUserPlan = [10, 15, 25, 50, 100, 1000];
    private $currencies    = ['AUD', 'CAD', 'EUR', 'GBP', 'USD'];

    // The key is active number licenses and the value is the annual price
    private $prices = [
        10   => 10,
        15   => 90,
        25   => 150,
        50   => 250,
        100  => 450,
        1000 => 1350,
    ];

    public function validPlan($userPlan)
    {
        list($currency, $userLicenses,) = $this->getUserPlan($userPlan);

        return $userLicenses && in_array($userLicenses, $this->validUserPlan) && in_array($currency, $this->currencies);
    }

    /**
     * Format: (currency)(number)(a/m)
     * Examples: USD100m, GBP100a
     */
    public function getUserPlan(string $userPlan): array
    {
        preg_match_all('/(^[A-Z]+)(\d+)(m|a)$/', $userPlan, $matches);
        if (!empty($matches[3][0])) {
            $currency = $matches[1][0];
            $userLicenses = $matches[2][0];
            $interval = $matches[3][0];

            return [$currency, $userLicenses, $interval];
        }

        return [null, null, null];
    }

    public function getContract($userPlan, $instance)
    {
        list(, $userLicenses, $interval) = $this->getUserPlan($userPlan);

        return [
            "{$userLicenses} user licenses for {$instance}",
            $userLicenses,
            $this->getPrice($interval, $userLicenses),
        ];
    }

    public function getPrice($interval, $userLicenses)
    {
        return ($interval == 'm') ? $this->prices[$userLicenses] * 1.1 : $this->prices[$userLicenses] * 12;
    }

    public function getUserLicenses($portal)
    {
        PortalHelper::parseConfig($portal);

        return !empty($portal->data->user_plan->license) ? $portal->data->user_plan->license : PortalHelper::DEFAULT_USERS_LICENSES;
    }

    public function getUserLimitationNumber($portal)
    {
        $userLicenses = self::getUserLicenses($portal);
        // System default user: user.0, user.1, portal author
        $systemUsersNumber = 3;

        return $userLicenses * self::USER_LICENSES_MULTIPLY_RATE - $systemUsersNumber;
    }
}
