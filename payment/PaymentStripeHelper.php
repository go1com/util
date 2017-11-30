<?php

namespace go1\util\payment;

class PaymentStripeHelper
{
    const CURRENCY_ZERO_DECIMAL = 0;
    const CURRENCY_TWO_DECIMAL  = 2;


    public static function currencies() : array
    {
        return array_map(function ($currency) {
            return self::currencyFormat($currency);
        }, self::presentmentCurrencies());
    }

    public static function currencyFormat(array $currency) : array
    {
        $decimals = in_array($currency['currency'], self::zeroDecimalCurrencies()) ? self::CURRENCY_ZERO_DECIMAL : self::CURRENCY_TWO_DECIMAL;
        $minimumCharge = self::currenciesMinimumCharge()[$currency['currency']] ?? null;

        return [
            'name'     => $currency['name'],
            'decimals' => $decimals,
            'min'      => $minimumCharge,
        ];
    }

    /**
     * @see https://stripe.com/docs/currencies#charge-currencies
     * @return array
     */
    public static function presentmentCurrencies() : array
    {
        return [
            'AFN' => ['currency' => 'AFN', 'name' => 'Afghan Afghani'],
            'ALL' => ['currency' => 'ALL', 'name' => 'Albanian Lek'],
            'DZD' => ['currency' => 'DZD', 'name' => 'Algerian Dinar'],
            'AOA' => ['currency' => 'AOA', 'name' => 'Angolan Kwanza'],
            'ARS' => ['currency' => 'ARS', 'name' => 'Argentine Peso'],
            'AMD' => ['currency' => 'AMD', 'name' => 'Armenian Dram'],
            'AWG' => ['currency' => 'AWG', 'name' => 'Aruban Florin'],
            'AUD' => ['currency' => 'AUD', 'name' => 'Australian Dollar'],
            'AZN' => ['currency' => 'AZN', 'name' => 'Azerbaijani Manat'],
            'BSD' => ['currency' => 'BSD', 'name' => 'Bahamian Dollar'],
            'BDT' => ['currency' => 'BDT', 'name' => 'Bangladeshi Taka'],
            'BBD' => ['currency' => 'BBD', 'name' => 'Barbadian Dollar'],
            'BZD' => ['currency' => 'BZD', 'name' => 'Belize Dollar'],
            'BMD' => ['currency' => 'BMD', 'name' => 'Bermudian Dollar'],
            'BOB' => ['currency' => 'BOB', 'name' => 'Bolivian Boliviano'],
            'BAM' => ['currency' => 'BAM', 'name' => 'Bosnia &amp; Herzegovina Convertible Mark'],
            'BWP' => ['currency' => 'BWP', 'name' => 'Botswana Pula'],
            'BRL' => ['currency' => 'BRL', 'name' => 'Brazilian Real'],
            'GBP' => ['currency' => 'GBP', 'name' => 'British Pound'],
            'BND' => ['currency' => 'BND', 'name' => 'Brunei Dollar'],
            'BGN' => ['currency' => 'BGN', 'name' => 'Bulgarian Lev'],
            'BIF' => ['currency' => 'BIF', 'name' => 'Burundian Franc'],
            'KHR' => ['currency' => 'KHR', 'name' => 'Cambodian Riel'],
            'CAD' => ['currency' => 'CAD', 'name' => 'Canadian Dollar'],
            'CVE' => ['currency' => 'CVE', 'name' => 'Cape Verdean Escudo'],
            'KYD' => ['currency' => 'KYD', 'name' => 'Cayman Islands Dollar'],
            'XAF' => ['currency' => 'XAF', 'name' => 'Central African Cfa Franc'],
            'XPF' => ['currency' => 'XPF', 'name' => 'Cfp Franc'],
            'CLP' => ['currency' => 'CLP', 'name' => 'Chilean Peso'],
            'CNY' => ['currency' => 'CNY', 'name' => 'Chinese Renminbi Yuan'],
            'COP' => ['currency' => 'COP', 'name' => 'Colombian Peso'],
            'KMF' => ['currency' => 'KMF', 'name' => 'Comorian Franc'],
            'CDF' => ['currency' => 'CDF', 'name' => 'Congolese Franc'],
            'CRC' => ['currency' => 'CRC', 'name' => 'Costa Rican Colón'],
            'HRK' => ['currency' => 'HRK', 'name' => 'Croatian Kuna'],
            'CZK' => ['currency' => 'CZK', 'name' => 'Czech Koruna'],
            'DKK' => ['currency' => 'DKK', 'name' => 'Danish Krone'],
            'DJF' => ['currency' => 'DJF', 'name' => 'Djiboutian Franc'],
            'DOP' => ['currency' => 'DOP', 'name' => 'Dominican Peso'],
            'XCD' => ['currency' => 'XCD', 'name' => 'East Caribbean Dollar'],
            'EGP' => ['currency' => 'EGP', 'name' => 'Egyptian Pound'],
            'ETB' => ['currency' => 'ETB', 'name' => 'Ethiopian Birr'],
            'EUR' => ['currency' => 'EUR', 'name' => 'Euro'],
            'FKP' => ['currency' => 'FKP', 'name' => 'Falkland Islands Pound'],
            'FJD' => ['currency' => 'FJD', 'name' => 'Fijian Dollar'],
            'GMD' => ['currency' => 'GMD', 'name' => 'Gambian Dalasi'],
            'GEL' => ['currency' => 'GEL', 'name' => 'Georgian Lari'],
            'GIP' => ['currency' => 'GIP', 'name' => 'Gibraltar Pound'],
            'GTQ' => ['currency' => 'GTQ', 'name' => 'Guatemalan Quetzal'],
            'GNF' => ['currency' => 'GNF', 'name' => 'Guinean Franc'],
            'GYD' => ['currency' => 'GYD', 'name' => 'Guyanese Dollar'],
            'HTG' => ['currency' => 'HTG', 'name' => 'Haitian Gourde'],
            'HNL' => ['currency' => 'HNL', 'name' => 'Honduran Lempira'],
            'HKD' => ['currency' => 'HKD', 'name' => 'Hong Kong Dollar'],
            'HUF' => ['currency' => 'HUF', 'name' => 'Hungarian Forint'],
            'ISK' => ['currency' => 'ISK', 'name' => 'Icelandic Króna'],
            'INR' => ['currency' => 'INR', 'name' => 'Indian Rupee'],
            'IDR' => ['currency' => 'IDR', 'name' => 'Indonesian Rupiah'],
            'ILS' => ['currency' => 'ILS', 'name' => 'Israeli New Sheqel'],
            'JMD' => ['currency' => 'JMD', 'name' => 'Jamaican Dollar'],
            'JPY' => ['currency' => 'JPY', 'name' => 'Japanese Yen'],
            'KZT' => ['currency' => 'KZT', 'name' => 'Kazakhstani Tenge'],
            'KES' => ['currency' => 'KES', 'name' => 'Kenyan Shilling'],
            'KGS' => ['currency' => 'KGS', 'name' => 'Kyrgyzstani Som'],
            'LAK' => ['currency' => 'LAK', 'name' => 'Lao Kip'],
            'LBP' => ['currency' => 'LBP', 'name' => 'Lebanese Pound'],
            'LSL' => ['currency' => 'LSL', 'name' => 'Lesotho Loti'],
            'LRD' => ['currency' => 'LRD', 'name' => 'Liberian Dollar'],
            'MOP' => ['currency' => 'MOP', 'name' => 'Macanese Pataca'],
            'MKD' => ['currency' => 'MKD', 'name' => 'Macedonian Denar'],
            'MGA' => ['currency' => 'MGA', 'name' => 'Malagasy Ariary'],
            'MWK' => ['currency' => 'MWK', 'name' => 'Malawian Kwacha'],
            'MYR' => ['currency' => 'MYR', 'name' => 'Malaysian Ringgit'],
            'MVR' => ['currency' => 'MVR', 'name' => 'Maldivian Rufiyaa'],
            'MRO' => ['currency' => 'MRO', 'name' => 'Mauritanian Ouguiya'],
            'MUR' => ['currency' => 'MUR', 'name' => 'Mauritian Rupee'],
            'MXN' => ['currency' => 'MXN', 'name' => 'Mexican Peso'],
            'MDL' => ['currency' => 'MDL', 'name' => 'Moldovan Leu'],
            'MNT' => ['currency' => 'MNT', 'name' => 'Mongolian Tögrög'],
            'MAD' => ['currency' => 'MAD', 'name' => 'Moroccan Dirham'],
            'MZN' => ['currency' => 'MZN', 'name' => 'Mozambican Metical'],
            'MMK' => ['currency' => 'MMK', 'name' => 'Myanmar Kyat'],
            'NAD' => ['currency' => 'NAD', 'name' => 'Namibian Dollar'],
            'NPR' => ['currency' => 'NPR', 'name' => 'Nepalese Rupee'],
            'ANG' => ['currency' => 'ANG', 'name' => 'Netherlands Antillean Gulden'],
            'TWD' => ['currency' => 'TWD', 'name' => 'New Taiwan Dollar'],
            'NZD' => ['currency' => 'NZD', 'name' => 'New Zealand Dollar'],
            'NIO' => ['currency' => 'NIO', 'name' => 'Nicaraguan Córdoba'],
            'NGN' => ['currency' => 'NGN', 'name' => 'Nigerian Naira'],
            'NOK' => ['currency' => 'NOK', 'name' => 'Norwegian Krone'],
            'PKR' => ['currency' => 'PKR', 'name' => 'Pakistani Rupee'],
            'PAB' => ['currency' => 'PAB', 'name' => 'Panamanian Balboa'],
            'PGK' => ['currency' => 'PGK', 'name' => 'Papua New Guinean Kina'],
            'PYG' => ['currency' => 'PYG', 'name' => 'Paraguayan Guaran'],
            'PEN' => ['currency' => 'PEN', 'name' => 'Peruvian Nuevo Sol'],
            'PHP' => ['currency' => 'PHP', 'name' => 'Philippine Peso'],
            'PLN' => ['currency' => 'PLN', 'name' => 'Polish Złoty'],
            'QAR' => ['currency' => 'QAR', 'name' => 'Qatari Riyal'],
            'RON' => ['currency' => 'RON', 'name' => 'Romanian Leu'],
            'RUB' => ['currency' => 'RUB', 'name' => 'Russian Ruble'],
            'RWF' => ['currency' => 'RWF', 'name' => 'Rwandan Franc'],
            'STD' => ['currency' => 'STD', 'name' => 'São Tomé and Príncipe Dobra'],
            'SHP' => ['currency' => 'SHP', 'name' => 'Saint Helenian Pound'],
            'SVC' => ['currency' => 'SVC', 'name' => 'Salvadoran Colón'],
            'WST' => ['currency' => 'WST', 'name' => 'Samoan Tala'],
            'SAR' => ['currency' => 'SAR', 'name' => 'Saudi Riyal'],
            'RSD' => ['currency' => 'RSD', 'name' => 'Serbian Dinar'],
            'SCR' => ['currency' => 'SCR', 'name' => 'Seychellois Rupee'],
            'SLL' => ['currency' => 'SLL', 'name' => 'Sierra Leonean Leone'],
            'SGD' => ['currency' => 'SGD', 'name' => 'Singapore Dollar'],
            'SBD' => ['currency' => 'SBD', 'name' => 'Solomon Islands Dollar'],
            'SOS' => ['currency' => 'SOS', 'name' => 'Somali Shilling'],
            'ZAR' => ['currency' => 'ZAR', 'name' => 'South African Rand'],
            'KRW' => ['currency' => 'KRW', 'name' => 'South Korean Won'],
            'LKR' => ['currency' => 'LKR', 'name' => 'Sri Lankan Rupee'],
            'SRD' => ['currency' => 'SRD', 'name' => 'Surinamese Dollar'],
            'SZL' => ['currency' => 'SZL', 'name' => 'Swazi Lilangeni'],
            'SEK' => ['currency' => 'SEK', 'name' => 'Swedish Krona'],
            'CHF' => ['currency' => 'CHF', 'name' => 'Swiss Franc'],
            'TJS' => ['currency' => 'TJS', 'name' => 'Tajikistani Somoni'],
            'TZS' => ['currency' => 'TZS', 'name' => 'Tanzanian Shilling'],
            'THB' => ['currency' => 'THB', 'name' => 'Thai Baht'],
            'TOP' => ['currency' => 'TOP', 'name' => 'Tongan Paʻanga'],
            'TTD' => ['currency' => 'TTD', 'name' => 'Trinidad and Tobago Dollar'],
            'TRY' => ['currency' => 'TRY', 'name' => 'Turkish Lira'],
            'UGX' => ['currency' => 'UGX', 'name' => 'Ugandan Shilling'],
            'UAH' => ['currency' => 'UAH', 'name' => 'Ukrainian Hryvnia'],
            'AED' => ['currency' => 'AED', 'name' => 'United Arab Emirates Dirham'],
            'USD' => ['currency' => 'USD', 'name' => 'United States Dollar'],
            'UYU' => ['currency' => 'UYU', 'name' => 'Uruguayan Peso'],
            'UZS' => ['currency' => 'UZS', 'name' => 'Uzbekistani Som'],
            'VUV' => ['currency' => 'VUV', 'name' => 'Vanuatu Vatu'],
            'VND' => ['currency' => 'VND', 'name' => 'Vietnamese Đồng'],
            'XOF' => ['currency' => 'XOF', 'name' => 'West African Cfa Franc'],
            'YER' => ['currency' => 'YER', 'name' => 'Yemeni Rial'],
            'ZMW' => ['currency' => 'ZMW', 'name' => 'Zambian Kwacha'],
        ];
    }

    /**
     * @see https://stripe.com/docs/currencies#zero-decimal
     * @return array
     */
    public static function zeroDecimalCurrencies() : array
    {
        return ['BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'VND', 'VUV', 'XAF', 'XOF', 'XPF'];
    }

    /**
     * @see https://support.stripe.com/questions/what-is-the-minimum-amount-i-can-charge-with-stripe
     * @see https://stripe.com/docs/currencies#zero-decimal
     *
     * @return array
     */
    public static function currenciesMinimumCharge() : array
    {
        return [
            'USD' => 0.50,  # $0.50
            'AUD' => 0.50,  # $0.50
            'BRL' => 0.50,  # R$0.50
            'CAD' => 0.50,  # $0.50
            'CHF' => 0.50,  # 0.50 Fr
            'DKK' => 2.50,  # 2.50-kr
            'EUR' => 0.50,  # €0.50
            'GBP' => 0.30,  # £0.30
            'HKD' => 4.00,  # $4.00
            'JPY' => 50.00, # ¥50
            'MXN' => 10.00, # $10
            'NOK' => 3.00,  # 3.00-kr
            'NZD' => 0.50,  # $0.50
            'SEK' => 3.00,  # 3.00-kr
            'SGD' => 0.50,  # $0.50
        ];
    }
}
