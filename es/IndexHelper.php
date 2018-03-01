<?php
namespace go1\util\es;

class IndexHelper
{
    public static function portalIndex(int $portalId): string
    {
        return Schema::I_GO1 . '_portal_' . $portalId;
    }

    public static function indexName(): string
    {
        !defined('ES_INDEX') && define('ES_INDEX', getenv('ES_INDEX') ?: 'go1_dev');

        return ES_INDEX;
    }
}
