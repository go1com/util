<?php

namespace go1\util\graph\mock;

use go1\util\GraphEdgeTypes;
use GraphAware\Neo4j\Client\Client;
use GraphAware\Neo4j\Client\Stack;

trait GraphUserMockTrait
{
    protected function createGraphReact(Client $client, int $reaction, int $userId, int $liId, $liType = 'Video')
    {
        $hasReact = GraphEdgeTypes::HAS_REACT;

        $reaction = in_array($reaction, [0, 1, -1]) ? $reaction : -1;

        $client->run(
            "MERGE (u:User {id: $userId})"
          . " MERGE (li:$liType:Group {id: $liId, name: {liName}})"
          . " MERGE (u)-[r:$hasReact]->(li) SET r.reaction = $reaction",
            ['liName' => "lo:$liId"]
        );
    }

    protected function createGraphUser(Client $client, array $options = [])
    {
        static $autoUserId;

        $user = [
            'id'          => isset($options['id']) ? $options['id'] : ++$autoUserId,
            'name'        => isset($options['name']) ? $options['name'] : 'Phuc Nguyen',
            'mail'        => isset($options['mail']) ? $options['mail'] : 'phuc.nguyen@go1.com',
            'instance_id' => isset($options['instance_id']) ? $options['instance_id'] : 0,
            'status'      => isset($options['status']) ? $options['status'] : 1,
            'accounts'    => isset($options['accounts']) ? $options['accounts'] : [],
            'managers'    => isset($options['managers']) ? $options['managers'] : [],
            'roles'       => isset($options['roles']) ? $options['roles'] : [],
        ];

        $stack = $client->stack();
        $stack->push(
            "MERGE (n:User { id: {$user['id']} }) SET n += {data}",
            ['data' => [
                'name' => $user['name'],
                'mail' => $user['mail'],
            ]]
        );

        $this
            ->linkUserAccounts($stack, $user)
            ->linkUserManagers($stack, $user)
            ->linkUserPortal($stack, $user)
            ->linkUserPublicGroup($stack, $user)
            ->linkUserMarketplaceGroup($stack, $user)
            ->linkUserRoles($stack, $user);

        $client->runStack($stack);

        return $user['id'];
    }

    private function linkUserAccounts(Stack $stack, $user)
    {
        $hasAccount = GraphEdgeTypes::HAS_ACCOUNT;
        $hasRootAccount = GraphEdgeTypes::HAS_ROOT_ACCOUNT;

        if (!empty($user['accounts'])) {
            $stack->push(
                "MATCH (user:User { id: {$user['id']} })"
              . " MATCH (account:User)"
              . " MATCH (user)-[r:$hasAccount]->(account)"
              . " MATCH (account)-[rr:$hasRootAccount]->(user)"
              . " DELETE r, rr"
            );

            foreach ($user['accounts'] as $accountId) {
                $stack->push(
                    "MATCH (user:User { id: {$user['id']} })"
                  . " MERGE (account:User { id: {$accountId} })"
                  . " MERGE (user)-[:$hasAccount]->(account)"
                  . " MERGE (account)-[:$hasRootAccount]->(user)"
                );
            }
        }

        return $this;
    }

    private function linkUserManagers(Stack $stack, $user)
    {
        $hasManager = GraphEdgeTypes::HAS_MANAGER;
        $hasLearner = GraphEdgeTypes::HAS_LEARNER;

        if (!empty($user['managers'])) {
            $stack->push(
                "MATCH (user:User { id: {$user['id']} })"
              . " MATCH (account:User)"
              . " MATCH (user)-[r:$hasManager]->(manager)"
              . " MATCH (manager)-[rr:$hasLearner]->(user)"
              . " DELETE r, rr"
            );

            foreach ($user['managers'] as $managerId) {
                $stack->push(
                    "MATCH (user:User { id: {$user['id']} })"
                  . " MERGE (manager:User { id: {$managerId} })"
                  . " MERGE (user)-[:$hasManager]->(manager)"
                  . " MERGE (manager)-[:$hasLearner]->(user)"
                );
            }
        }

        return $this;
    }

    private function linkUserPortal(Stack $stack, $user)
    {
        $hasGroup = GraphEdgeTypes::HAS_GROUP;
        $hasMember = GraphEdgeTypes::HAS_MEMBER;

        if ($portalId = $user['instance_id']) {
            $stack->push(
                "MERGE (user:User { id: {$user['id']} })"
              . " MERGE (portal:Group { name: {portal} })"
              . " MERGE (user)-[:$hasGroup]->(portal)"
              . " MERGE (portal)-[:$hasMember]->(user)",
                ['portal' => "portal:$portalId"]
            );
        }

        return $this;
    }

    private function linkUserPublicGroup(Stack $stack, $user)
    {
        $hasGroup = GraphEdgeTypes::HAS_GROUP;
        $hasMember = GraphEdgeTypes::HAS_MEMBER;

        if ($user['status']) {
            $stack->push(
                "MERGE (user:User { id: {$user['id']} })"
              . " MERGE (public:Group { name: {public} })"
              . " MERGE (user)-[:$hasGroup]->(public)"
              . " MERGE (public)-[:$hasMember]->(user)",
                ['public' => 'public']
            );
        }

        return $this;
    }

    private function linkUserMarketplaceGroup(Stack $stack, $user)
    {
        $hasGroup = GraphEdgeTypes::HAS_GROUP;
        $hasMember = GraphEdgeTypes::HAS_MEMBER;

        if ($user['status']) {
            $stack->push(
                "MERGE (user:User { id: {$user['id']} })"
              . " MERGE (marketplace:Group { name: {marketplace} })"
              . " MERGE (user)-[:$hasGroup]->(marketplace)"
              . " MERGE (marketplace)-[:$hasMember]->(user)",
                ['marketplace' => 'marketplace']
            );
        }

        return $this;
    }

    private function linkUserRoles(Stack $stack, $user)
    {
        $hasGroup = GraphEdgeTypes::HAS_GROUP;
        $hasMember = GraphEdgeTypes::HAS_MEMBER;

        $stack->push(
            "MATCH (u:User {id: {$user['id']} })-[r:$hasGroup]-(role:Group)"
          . " WHERE role.name STARTS WITH 'role:'"
          . " DELETE r"
        );

        $stack->push(
            "MATCH (role:Group)-[r:$hasMember]->(u:User {id: {$user['id']} })"
          . " WHERE role.name STARTS WITH 'role:'"
          . " DELETE r"
        );

        foreach ($user['roles'] as $role) {
            $role = GraphEdgeTypes::role($role);

            if ($role) {
                $q = "MATCH (user:User { id: {$user['id']} })";
                if ($portalId = $user['instance_id']) {
                    $q .= ", (portal:Group { name: {portalName} })"
                        . " MERGE (role:Group { name: {role} })-[:$hasGroup]->(portal)";
                    $p = ['role' => "role:$role", 'portalName' => "portal:$portalId"];
                }
                else {
                    $q .= " MERGE (role:Group { name: {role} })";
                    $p = ['role' => "role:$role"];
                }
                $q .= " MERGE (user)-[:$hasGroup]->(role)"
                    . " MERGE (role)-[:$hasMember]->(user)";
                $stack->push($q, $p);
            }
        }

        return $this;
    }

}
