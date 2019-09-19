<?php

namespace go1\util\customer;

use ReflectionClass;

class IndustryTypes
{
    const AGRICULTURE_FORESTRY_AND_FISHING               = 'Agriculture, Forestry and Fishing';
    const FINANCIAL_AND_INSURANCE_SERVICES               = 'Financial and Insurance Services';
    const CONSTRUCTION                                   = 'Construction';
    const PUBLIC_ADMINISTRATION_AND_SAFETY               = 'Public Administration and Safety';
    const EDUCATION_AND_TRAINING                         = 'Education and Training';
    const ARTS_AND_RECREATION_SERVICES                   = 'Arts and Recreation Services';
    const HEALTH_CARE_AND_SOCIAL_ASSISTANCE              = 'Health Care and Social Assistance';
    const ACCOMMODATION_AND_FOOD_SERVICES                = 'Accommodation and Food Services';
    const MANUFACTURING                                  = 'Manufacturing';
    const MINING                                         = 'Mining';
    const ELECTRICITY_GAS_WATER_AND_WASTE_SERVICES       = 'Electricity, Gas, Water and Waste Services';
    const RENTAL_HIRING_AND_REAL_ESTATE_SERVICES         = 'Rental, Hiring and Real Estate Services';
    const INFORMATION_MEDIA_AND_TELECOMMUNICATIONS       = 'Information Media and Telecommunications';
    const TRANSPORT_POSTAL_AND_WAREHOUSING               = 'Transport, Postal and Warehousing';
    const PROFESSIONAL_SCIENTIFIC_AND_TECHNICAL_SERVICES = 'Professional, Scientific and Technical Services';
    const ADMINISTRATIVE_AND_SUPPORT_SERVICES            = 'Administrative and Support Services';
    const RETAIL_AND_WHOLESALE_TRADE                     = 'Retail and Wholesale Trade';
    const OTHER_SERVICES                                 = 'Other Services';

    public static function getArray()
    {
        $rClass = new ReflectionClass(self::class);

        return $rClass->getConstants();
    }
}
