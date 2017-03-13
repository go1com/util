<?php

namespace go1\util\graph\mock;

use go1\util\GraphEdgeTypes;
use go1\util\GroupStatus;
use GraphAware\Neo4j\Client\Client;

trait GraphNoteMockTrait
{
    private $hasNote = GraphEdgeTypes::HAS_NOTE;
    private $hasMember = GraphEdgeTypes::HAS_MEMBER;
    private $hasSharedNote = GraphEdgeTypes::HAS_SHARED_NOTE;
    private $hasGroup = GraphEdgeTypes::HAS_GROUP;

    protected function createGraphNote(Client $client, array $data)
    {
        $stack = $client->stack();
        $id = isset($data['id']) ? $data['id'] : 0;
        $uuid = isset($data['uuid']) ? $data['uuid'] : 'NOTE_UUID';
        $entityType = isset($data['entity_type']) ? $data['entity_type'] : 'lo';

        $stack->push("MERGE (n:Note { uuid: {uuid} }) SET n += {data}",
            [
                'uuid' => $uuid,
                'data' => [
                    'id'            => (int) $id,
                    'created'       => isset($data['created']) ? (int) $data['created'] : time(),
                    'entity_type'   => $entityType,
                ],
            ]
        );

        // Add entity_id direction
        $entityId = isset($data['entity_id']) ? $data['entity_id'] : 0;
        if ($entityId) {
            list($label, $prop, $propValue) = GraphEdgeTypes::getEntityGraphData($entityType, $entityId);
            $entityId && $stack->push(
                "MATCH (n:Note { uuid: {uuid} })"
                . " MERGE (entity:$label { $prop: {entityPropValue} })"
                . " MERGE (entity)-[:{$this->hasNote}]->(n)"
                . " MERGE (n)-[:{$this->hasMember}]->(entity)",
                ['uuid' => $uuid, 'entityPropValue' => $propValue]
            );
        }

        // Delete original user_id
        $userId = isset($data['user_id']) ? $data['user_id'] : 0;
        if (isset($data['original']['user_id'])) {
            $originalUserId = $data['original']['user_id'];
            if ($originalUserId != $userId) {
                $stack->push(
                    "MATCH (u:User { id: {$originalUserId} })"
                    . " MATCH (n:Note { uuid: {uuid} })"
                    . " MATCH (u)-[r:{$this->hasNote}]->(n)-[rr:{$this->hasMember}]->(u)"
                    . " DELETE r, rr",
                    ['uuid' => $uuid]
                );
            }
        }

        $userId && $stack->push(
            "MATCH (n:Note { uuid: {uuid} })"
            . " MERGE (u:User { id: {$userId} })"
            . " MERGE (u)-[:{$this->hasNote}]->(n)"
            . " MERGE (n)-[:{$this->hasMember}]->(u)",
            ['uuid' => $uuid]
        );

        $client->runStack($stack);
    }

    protected function createGraphUserNote(Client $client, int $userId, string $uuid)
    {
        $client->run(
            "MERGE (u:User { id: {$userId} })"
            . " MERGE (n:Note { uuid: {uuid} })"
            . " MERGE (u)-[:{$this->hasSharedNote}]->(n)"
            . " MERGE (n)-[:{$this->hasMember}]->(u)",
            ['uuid' => $uuid]
        );
    }

    protected function createGraphGroupNote(Client $client, int $groupId, string $uuid)
    {
        $client->run(
            "MERGE (g:Group { name: {groupName} })"
            . " MERGE (n:Note { uuid: {uuid} })"
            . " MERGE (e)-[r:{$this->hasGroup}]->(g)"
            . " MERGE (g)-[:{$this->hasMember}]->(e)",
            ['groupName' => "group:$groupId", 'uuid' => $uuid]
        );
    }

    protected function createGraphPortalNote(Client $client, int $portalId, string $uuid)
    {
        $client->run(
            "MERGE (p:Group { name: {portalName} })"
            . " MERGE (n:Note { uuid: {uuid} })"
            . " MERGE (p)-[:{$this->hasSharedNote}]->(n)"
            . " MERGE (n)-[:{$this->hasMember}]->(p)",
            ['portalName' => "portal:$portalId", 'uuid' => $uuid]
        );
    }
}
