<?php

namespace go1\util\graph\mock;

use go1\util\enrolment\EnrolmentStatuses;
use go1\util\GraphEdgeTypes;
use go1\util\note\NoteEntityType;
use go1\util\note\NoteStatus;
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
        $entityId = isset($data['entity_id']) ? (int) $data['entity_id'] : 0;
        $entityType = isset($data['entity_type']) ? $data['entity_type'] : NoteEntityType::TYPE_LO;
        $private = isset($data['private']) ? (int) $data['private'] : 0;

        static $created = 1000000;

        $stack->push("MERGE (n:Note { uuid: {uuid} }) SET n += {data}",
            [
                'uuid' => $uuid,
                'data' => [
                    'id'            => (int) $id,
                    'created'       => isset($data['created']) ? (int) $data['created'] : $created++,
                    'profile_id'    => isset($data['profile_id']) ? (int) $data['profile_id'] : 0,
                    'entity_type'   => $entityType,
                    'entity_id'     => $entityId,
                    'private'       => $private
                ]
            ]
        );

        // Add entity_id direction
        if ($entityId) {
            if (in_array($entityType, ['lo', 'portal'])) {
                list($label, $prop, $propValue) = GraphEdgeTypes::getEntityGraphData($entityType, $entityId);

                $context = $data['context'] ?? [];
                $context += [
                    'status'    => isset($data['lo_status']) ? (int)$data['lo_status'] : NoteStatus::ENTITY_STATUS_ENROLLED,
                    'enrolment' => $data['enrolment'] ?? EnrolmentStatuses::IN_PROGRESS
                ];

                $stack->push(
                    "MATCH (n:Note { uuid: {uuid} })"
                    . " MERGE (entity:$label { $prop: {entityPropValue} })"
                    . " MERGE (entity)-[:{$this->hasNote}]->(n)"
                    . " MERGE (n)-[r:{$this->hasMember}]->(entity)"
                    . " SET r = {context}",
                    [
                        'uuid'              => $uuid,
                        'entityPropValue'   => $propValue,
                        'context'           => $context
                    ]
                );
            }
            else if ($entityType == 'custom') {
                $stack->push(
                    "MATCH (n:Note { uuid: {uuid} })"
                    . " MERGE (e:Group { id: {$entityId}, name: {customName} })"
                    . " MERGE (e)-[:{$this->hasNote}]->(n)"
                    . " MERGE (n)-[:{$this->hasMember}]->(e)",
                    ['uuid' => $uuid, 'customName' => "customLo:{$entityId}"]
                );
            }
            else if (!in_array($entityType, ['custom', 'lo', 'portal', 'group'])) {
                $label = ucwords($entityType);
                $context = $data['context'] ?? [];
                $stack->push(
                    "MATCH (n:Note { uuid: {uuid} })"
                    . " MERGE (e:Other:$label { id: {$entityId} })"
                    . " MERGE (e)-[:{$this->hasNote}]->(n)"
                    . " MERGE (n)-[r:{$this->hasMember}]->(e)"
                    . " SET r = {context}",
                    ['uuid' => $uuid, 'context' => $context]
                );
            }
        }

        $accountId = isset($data['account_id']) ? $data['account_id'] : 0;
        $accountId && $stack->push(
            "MATCH (n:Note { uuid: {uuid} })"
            . " MERGE (u:User { id: {$accountId} })"
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
            "MERGE (g:Group {name: {groupName} })"
            . " MERGE (n:Note { uuid: {uuid} })"
            . " MERGE (n)-[r:{$this->hasGroup}]->(g)"
            . " MERGE (g)-[:{$this->hasMember}]->(n)",
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
