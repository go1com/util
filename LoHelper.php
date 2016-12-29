<?php

namespace go1\util;

use Doctrine\DBAL\Connection;

class LoHelper
{
    public static function load(Connection $db, int $id)
    {
        return ($learningObjects = static::loadMultiple($db, [$id]))
            ? $learningObjects[0]
            : false;
    }

    /**
     * @param Connection $db
     * @param  []int      $ids
     * @return []stdClass
     */
    public static function loadMultiple(Connection $db, array $ids): array
    {
        $learningObjects = !$ids ? [] : $db
            ->executeQuery(
                'SELECT lo.*, pricing.price, pricing.currency, pricing.tax'
                . ' FROM gc_lo lo'
                . ' LEFT JOIN gc_lo_pricing pricing ON lo.id = pricing.id'
                . ' WHERE lo.id IN (?)',
                [$ids],
                [DB::INTEGERS]
            )
            ->fetchAll(DB::OBJ);

        foreach ($learningObjects as &$lo) {
            if (!$lo->data = json_decode($lo->data)) {
                unset($lo->data);
            }

            $lo->pricing = (object) [
                'price'    => $lo->price ? (float) $lo->price : 0.00,
                'currency' => $lo->currency ?: 'USD',
                'tax'      => $lo->tax ? (float) $lo->tax : 0.00,
            ];
            unset($lo->price, $lo->currency, $lo->tax);

            $lo->event = empty($lo->event) ? (object) [] : json_decode($lo->event);
        }

        return $learningObjects;
    }

    public static function findIds(array &$items, array &$ids = [])
    {
        foreach ($items as &$item) {
            $ids[] = $item['id'];

            if (!empty($item['items'])) {
                static::findIds($item['items'], $ids);
            }
        }
    }
}
