<?php

namespace go1\util\graph\mock;

use go1\util\GraphEdgeTypes;
use go1\util\group\GroupItemStatus;
use go1\util\group\GroupStatus;
use go1\util\group\GroupTypes;
use GraphAware\Neo4j\Client\Client;

trait GraphSocialMockTrait
{
    protected function addGraphUserTag(Client $client, int $userId, array $tagNames)
    {
        $hasAccount = GraphEdgeTypes::HAS_ACCOUNT;
        $hasGroup = GraphEdgeTypes::HAS_GROUP;
        $hasMember = GraphEdgeTypes::HAS_MEMBER;
        $hasTag = GraphEdgeTypes::HAS_TAG;

        $stack = $client->stack();
        $stack->push(
            "MATCH (u:User { id: {$userId} })"
            . " MATCH (u)-[r:{$hasTag}]->(:Tag)"
            . " DELETE r"
        );

        $stack->push(
            "MATCH (u:User)-[:{$hasAccount}]->(o:User)-[:{$hasGroup}]->(p:Group)-[:{$hasMember}]->(t:Tag)"
            . " WHERE p.name STARTS WITH 'portal:' AND u.id = {$userId} AND t.name IN {tagNames}"
            . " MERGE (u)-[:{$hasTag}]->(t)",
            ['tagNames' => $tagNames]
        );

        $client->runStack($stack);
    }

    protected function followGraph(Client $client, int $sourceId, int $targetId)
    {
        $hasFollowing = GraphEdgeTypes::HAS_FOLLOWING;
        $hasFollower = GraphEdgeTypes::HAS_FOLLOWER;

        $client->run(
            "MATCH (A:User { id: {$sourceId} })"
            . " MATCH (B:User { id: {$targetId} })"
            . " MERGE (A)-[r:{$hasFollowing}]->(B)"
            . " MERGE (B)-[rr:{$hasFollower}]->(A)"
        );
    }

    protected function createGraphGroup(Client $client, array $option)
    {
        $hasMember = GraphEdgeTypes::HAS_MEMBER;
        $hasGroupOwn = GraphEdgeTypes::HAS_GROUP_OWN;
        static $autoGroupId;

        $group = [
            'id'              => $option['id'] ?? ++$autoGroupId,
            'title'           => $option['title'] ?? uniqid('group'),
            'created'         => $option['created'] ?? time(),
            'visibility'      => $option['visibility'] ?? GroupStatus::PUBLIC,
            'type'            => $option['type'] ?? GroupTypes::DEFAULT,
            'instance_id'     => $option['instance_id'] ?? 0,
            'account_id'      => $option['account_id'] ?? 0
        ];

        $label = GroupTypes::graphLabel($group['type']);
        $stack = $client->stack();
        $stack->push("MERGE (g:Group:{$label} { id: {$group['id']}, name: {name} }) SET g += {data}",
            [
                'name' => "group:{$group['id']}",
                'data' => [
                    'title'            => $group['title'],
                    'created'          => $group['created'],
                    'visibility'       => $group['visibility']
                ],
            ]
        );

        $group['instance_id'] && $stack->push(
            " MATCH (g:Group { id: {$group['id']}, name: {groupName} })"
            . "MERGE (p:Group { name: {portalName} })"
            . " MERGE (p)-[:{$hasGroupOwn}]->(g)"
            . " MERGE (g)-[:{$hasMember}]->(p)",
            ['portalName' => "portal:{$group['instance_id']}", 'groupName' => "group:{$group['id']}"]
        );

        $group['account_id'] && $stack->push(
            " MATCH (g:Group { id: {$group['id']}, name: {groupName}})"
            . "MERGE (account:User { id: {$group['account_id']} })"
            . " MERGE (account)-[:{$hasGroupOwn}]->(g)"
            . " MERGE (g)-[:{$hasMember}]->(account)",
            ['groupName' => "group:{$group['id']}"]
        );

        $client->runStack($stack);

        return $autoGroupId;
    }

    protected function addGraphUserGroup(Client $client, int $accountId, int $groupId, int $status = GroupItemStatus::ACTIVE)
    {
        $hasGroup = GraphEdgeTypes::HAS_GROUP;
        $hasMember = GraphEdgeTypes::HAS_MEMBER;

        $client->run(
            "MATCH (g:Group { id: {$groupId}, name: {groupName} })"
            . " MERGE (acc:User { id: {$accountId} })"
            . " MERGE (acc)-[r:{$hasGroup}]->(g) SET r = {data}"
            . " MERGE (g)-[:{$hasMember}]->(acc)",
            [
                'groupName' => "group:{$groupId}",
                'data'      => [
                    'status' => $status,
                ],
            ]
        );
    }

    protected function addGraphPortalGroup(Client $client, int $portalId, int $groupId)
    {
        $hasGroup = GraphEdgeTypes::HAS_GROUP;
        $hasMember = GraphEdgeTypes::HAS_MEMBER;

        $client->run(
            " MATCH (g:Group { id: {$groupId}, name: {groupName} })"
            . "MERGE (p:Group { name: {portalName} })"
            . " MERGE (p)-[:{$hasGroup}]->(g)"
            . " MERGE (g)-[:{$hasMember}]->(p)",
            [
                'portalName' => "portal:{$portalId}",
                'groupName'  => "group:{$groupId}",
            ]
        );
    }

    protected function addGraphLoGroup(Client $client, int $loId, int $groupId)
    {
        $hasSharedGroup = GraphEdgeTypes::HAS_SHARED_GROUP;
        $hasSharedLo = GraphEdgeTypes::HAS_SHARED_LO;

        $client->run(
            " MATCH (g:Group { id: {$groupId}, name: {groupName} })"
            . "MERGE (lo:Group { name: {loName} })"
            . " MERGE (lo)-[:{$hasSharedGroup}]->(g)"
            . " MERGE (g)-[:{$hasSharedLo}]->(lo)",
            [
                'loName'    => "lo:{$loId}",
                'groupName' => "group:{$groupId}",
            ]
        );
    }

    protected function addGraphNoteGroup(Client $client, string $uuid, int $groupId)
    {
        $hasGroup = GraphEdgeTypes::HAS_GROUP;
        $hasMember = GraphEdgeTypes::HAS_MEMBER;

        $client->run(
            " MATCH (g:Group { id: {$groupId}, name: {groupName} })"
            . "MERGE (n:Note { uuid: {uuid} })"
            . " MERGE (n)-[:{$hasGroup}]->(g)"
            . " MERGE (g)-[:{$hasMember}]->(n)",
            [
                'uuid'      => "{$uuid}",
                'groupName' => "group:{$groupId}",
            ]
        );
    }

    protected function addGraphGroupGroup(Client $client, string $firstGroupId, int $secondGroupId)
    {
        $hasGroup = GraphEdgeTypes::HAS_GROUP;
        $hasMember = GraphEdgeTypes::HAS_MEMBER;

        $client->run(
            " MATCH (g:Group { id: {$firstGroupId}, name: {firstGroupName} })"
            . " MERGE (gg:Group { id: {$secondGroupId}, name: {secondGroupName} })"
            . " MERGE (g)-[:{$hasGroup}]->(gg)"
            . " MERGE (gg)-[:{$hasMember}]->(g)",
            [
                'firstGroupName'  => "group:{$firstGroupId}",
                'secondGroupName' => "group:{$secondGroupId}",
            ]
        );
    }
}
