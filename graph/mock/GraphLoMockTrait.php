<?php

namespace go1\util\graph\mock;

use go1\util\edge\EdgeTypes;
use go1\util\GraphEdgeTypes;
use go1\util\lo\LoHelper;
use go1\util\lo\LoTypes;
use GraphAware\Neo4j\Client\Client;
use GraphAware\Neo4j\Client\Stack;

trait GraphLoMockTrait
{
    protected function createGraphTag(Client $client, $instanceId, $tag, $parentName = null)
    {
        $hasGroup = GraphEdgeTypes::HAS_GROUP;
        $hasMember = GraphEdgeTypes::HAS_MEMBER;
        $hasRoParent = GraphEdgeTypes::HAS_RO_PARENT;
        $hasRoTag = GraphEdgeTypes::HAS_RO_TAG;
        $hasRoChild = GraphEdgeTypes::HAS_RO_CHILD;
        $hasRo = GraphEdgeTypes::HAS_RO;
        $hasRoPortal = GraphEdgeTypes::HAS_RO_PORTAL;

        $q = "MERGE (portal:Group { name: {portalName} })"
            . " MERGE (tag:Tag { name: {tagName} })"
            . " MERGE (tag)-[:$hasGroup]->(portal)"
            . " MERGE (portal)-[:$hasMember]->(tag)";
        $p = [
            'portalName' => "portal:$instanceId",
            'tagName'    => $tag,
        ];
        if ($parentName) {
            $q .= " MERGE (parentTag:Tag { name: {parentName} })"
                . " MERGE (tag)-[:$hasRoParent]->(parentRo:Parent:RO)-[:$hasRoTag]->(tag)"
                . " MERGE (parentTag)-[:$hasRoChild]->(parentRo)-[:$hasRoTag]->(parentTag)"
                . " MERGE (portal)-[:$hasRo]->(parentRo)-[:$hasRoPortal]->(portal)";
            $p['parentName'] = $parentName;
        }

        $client->run($q, $p);
    }

    protected function createGraphCustomTag(Client $client, $tag, $loId, $instanceId)
    {
        $hasCustomTag = GraphEdgeTypes::HAS_CUSTOM_TAG;

        $client->run(
            "MATCH (lo:Group { id: $loId, name: {loName} })"
            . " MERGE (tag:Tag { name : {tagName} })"
            . " MERGE (lo)-[r:$hasCustomTag { portal_id: $instanceId }]->(tag)",
            [
                'loName'  => "lo:$loId",
                'tagName' => $tag,
            ]
        );
    }

    protected function createGraphLearningPathway(Client $client, array $options = [])
    {
        return $this
            ->createGraphCourse(
                $client,
                ['type' => GraphEdgeTypes::type('learning_pathway')] + $options
            );
    }

    protected function createGraphModule(Client $client, array $options = [])
    {
        return $this
            ->createGraphCourse(
                $client,
                ['type' => GraphEdgeTypes::type('module')] + $options
            );
    }

    protected function createGraphCourse(Client $client, array $options = [])
    {
        $hasGroup = GraphEdgeTypes::HAS_GROUP;
        $hasMember = GraphEdgeTypes::HAS_MEMBER;
        // tags has prop weight
        static $autoCourseId;

        $course = [
            'type'        => isset($options['type']) ? GraphEdgeTypes::type($options['type']) : 'Course',
            'id'          => isset($options['id']) ? $options['id'] : ++$autoCourseId,
            'title'       => isset($options['title']) ? $options['title'] : 'Example course',
            'instance_id' => $instanceId = isset($options['instance_id']) ? $options['instance_id'] : 0,
            'private'     => isset($options['private']) ? $options['private'] : 0,
            'published'   => isset($options['published']) ? $options['published'] : 1,
            'marketplace' => isset($options['marketplace']) ? $options['marketplace'] : 0,
            'privacy'     => isset($options['privacy']) ? $options['privacy'] : [],
            'tags'        => isset($options['tags']) ? $options['tags'] : [],
            'groups'      => isset($options['groups']) ? $options['groups'] : [],
            'assessors'   => isset($options['assessors']) ? $options['assessors'] : [],
            'authors'     => isset($options['authors']) ? $options['authors'] : [],
            'roles'       => isset($options['roles']) ? $options['roles'] : [],
            'event'       => isset($options['event']) ? $options['event'] : [],
            'parents'     => isset($options['parents']) ? $options['parents'] : [],
        ];
        if (!empty($options['price'])) {
            $course['pricing'] = [
                'price'    => $options['price']['price'],
                'currency' => $options['price']['currency'],
                'tax'      => $options['price']['tax'],
            ];
        }

        $stack = $client->stack();

        $loProps = ['title' => $course['title']];
        if ($course['type'] == GraphEdgeTypes::type(LoTypes::COURSE)) {
            $loProps['discussion'] = $options[LoHelper::DISCUSSION_ALLOW] ?? true;
        }
        $stack->push(
            "MERGE (lo:{$course['type']}:Group { id: {$course['id']}, name: {name} }) ON CREATE SET lo += {lo} ON MATCH SET lo += {lo}"
            . " MERGE (lo)-[:$hasGroup]->(lo)"
            . " MERGE (lo)-[:$hasMember]->(lo)",
            [
                'name' => "lo:{$course['id']}",
                'lo'   => $loProps,
            ]
        );

        $this
            ->linkCourseAuthors($stack, $course)
            ->linkCourseAssessors($stack, $course)
            ->linkCoursePortal($stack, $course)
            ->linkCourseRoles($stack, $course)
            ->linkCoursePublicGroup($stack, $course)
            ->linkCourseMarketplace($stack, $course)
            ->linkCoursePrivacy($stack, $course)
            ->linkCoursePricing($stack, $course)
            ->linkCourseTags($stack, $course)
            ->linkEvent($stack, $course)
            ->linkParent($stack, $course);

        $client->runStack($stack);

        return $course['id'];
    }

    private function linkCourseAuthors(Stack $stack, $course)
    {
        $hasAuthor = GraphEdgeTypes::HAS_AUTHOR;
        $hasLo = GraphEdgeTypes::HAS_LO;

        foreach ($course['authors'] as $authorId) {
            $stack->push(
                "MATCH (lo:{$course['type']} { id: {$course['id']} })"
                . " MERGE (author:User { id: $authorId })"
                . " MERGE (lo)-[:$hasAuthor]->(author)"
                . " MERGE (author)-[:$hasLo]->(lo)"
            );
        }

        return $this;
    }

    private function linkCourseAssessors(Stack $stack, $course)
    {
        $hasAssessor = GraphEdgeTypes::HAS_ASSESSOR;
        $hasLo = GraphEdgeTypes::HAS_LO;

        foreach ($course['assessors'] as $assessorId) {
            $stack->push(
                "MATCH (lo:{$course['type']} { id: {$course['id']} })"
                . " MERGE (assessor:User { id: $assessorId })"
                . " MERGE (lo)-[:$hasAssessor]->(assessor)"
                . " MERGE (assessor)-[:$hasLo]->(lo)"
            );
        }

        return $this;
    }

    private function linkCoursePortal(Stack $stack, $course)
    {
        $hasGroup = GraphEdgeTypes::HAS_GROUP;
        $hasMember = GraphEdgeTypes::HAS_MEMBER;

        if ($course['instance_id']) {
            $q = "MATCH (lo:{$course['type']} { id: {$course['id']} })"
                . " MERGE (portal:Group { name: {portal} })"
                . " MERGE (lo)-[:$hasGroup]->(portal)"
                . " MERGE (portal)-[:$hasMember]->(lo)";
            $stack->push($q, ['portal' => "portal:{$course['instance_id']}"]);

            if (!empty($course['roles']) || !empty($course['privacy'] || $course['private'])) {
                $q = "MATCH (portal:Group)-[r:$hasMember]->(lo:{$course['type']})"
                    . " WHERE portal.name = {portal} AND lo.id = {$course['id']}"
                    . " DELETE r";
                $stack->push($q, ['portal' => "portal:{$course['instance_id']}"]);
            }
        }

        return $this;
    }

    private function linkCourseRoles(Stack $stack, $course)
    {
        $hasGroup = GraphEdgeTypes::HAS_GROUP;
        $hasMember = GraphEdgeTypes::HAS_MEMBER;

        foreach ($course['roles'] as $role) {
            $q = "MATCH (lo:{$course['type']} {id: {$course['id']} })";
            if ($portalId = $course['instance_id']) {
                $q .= ", (portal:Group { name: {portalName} })"
                    . " MERGE (role:Group { name: {role} })-[:$hasGroup]->(portal)";
                $p = ['role' => "role:$role", 'portalName' => "portal:$portalId"];
            }
            else {
                $q .= " MERGE (role:Group { name: {role} })";
                $p = ['role' => "role:$role"];
            }
            $q .= " MERGE (lo)-[:$hasGroup]->(role)"
                . " MERGE (role)-[:$hasMember]->(lo)";
            $stack->push($q, $p);
        }

        return $this;
    }

    private function linkCoursePublicGroup(Stack $stack, $course)
    {
        $hasGroup = GraphEdgeTypes::HAS_GROUP;
        $hasMember = GraphEdgeTypes::HAS_MEMBER;

        if (!$course['private'] && $course['published'] && empty($course['roles'])) {
            $stack->push(
                "MATCH (lo:{$course['type']} {id: {$course['id']} })"
                . " MERGE (public:Group { name: 'public' })"
                . " MERGE (lo)-[:$hasGroup]->(public)"
                . " MERGE (public)-[:$hasMember]->(lo)"
            );
        }
        else {
            $stack->push(
                "MATCH (lo:{$course['type']} {id: {$course['id']} })"
                . " MATCH (public:Group { name: 'public' })"
                . " MATCH (lo)-[r:$hasGroup]->(public)"
                . " MATCH (public)-[rr:$hasMember]->(lo)"
                . " DELETE r, rr"
            );
        }

        return $this;
    }

    private function linkCourseMarketplace(Stack $stack, $course)
    {
        $hasGroup = GraphEdgeTypes::HAS_GROUP;
        $hasMember = GraphEdgeTypes::HAS_MEMBER;

        if (!$course['private'] && $course['published'] && $course['marketplace'] && empty($course['roles'])) {
            $stack->push(
                "MATCH (lo:{$course['type']} {id: {$course['id']} })"
                . " MERGE (marketplace:Group { name: 'marketplace' })"
                . " MERGE (lo)-[:$hasGroup]->(marketplace)"
                . " MERGE (marketplace)-[:$hasMember]->(lo)"
            );
        }
        else {
            $stack->push(
                "MATCH (lo:{$course['type']} {id: {$course['id']} })"
                . " MATCH (marketplace:Group { name: 'marketplace' })"
                . " MATCH (lo)-[r:$hasGroup]->(marketplace)"
                . " MATCH (marketplace)-[rr:$hasMember]->(lo)"
                . " DELETE r, rr"
            );
        }

        return $this;
    }

    private function linkCoursePrivacy(Stack $stack, $course)
    {
        $hasMember = GraphEdgeTypes::HAS_MEMBER;
        $hasSharedLo = GraphEdgeTypes::HAS_SHARED_LO;

        if ($course['privacy']) {
            foreach ($course['privacy'] as $privacy) {
                $weight = isset($privacy['weight']) ? $privacy['weight'] : 0;
                $targetId = isset($privacy['target_id']) ? $privacy['target_id'] : 0;
                $weight == 0 && $stack->push(
                    "MATCH (lo:{$course['type']} {id: {$course['id']} })"
                    . " MERGE (shareUser:User { id: {$targetId} })"
                    . " MERGE (lo)-[:$hasMember]->(shareUser)"
                    . " MERGE (shareUser)-[:$hasSharedLo]->(lo)"
                );
            }
        }
        else {
            $stack->push(
                "MATCH (lo:{$course['type']} {id: {$course['id']}})"
                . " MATCH (shareUser:User)"
                . " MATCH (shareUser)-[r:$hasSharedLo]->(lo)"
                . " MATCH (lo)-[rr:$hasMember]->(shareUser)"
                . " DELETE r, rr"
            );
        }

        return $this;
    }

    private function linkCoursePricing(Stack $stack, $course)
    {
        $hasProduct = GraphEdgeTypes::HAS_PRODUCT;

        if (isset($course['pricing']) && is_array($course['pricing'])) {
            $stack->push(
                "MATCH (lo:{$course['type']} { id: {$course['id']} })"
                . " MERGE (product:Product { id: {$course['id']} }) ON CREATE SET product += {product} ON MATCH SET product += {product}"
                . " MERGE (lo)-[:$hasProduct]->(product)",
                ['product' => $course['pricing']]
            );
        }
        else {
            $stack->push(
                "MATCH (product:Product {id: {$course['id']} }) DELETE product"
            );
        }

        return $this;
    }

    private function linkCourseTags(Stack $stack, $course)
    {
        $hasGroup = GraphEdgeTypes::HAS_GROUP;
        $hasMember = GraphEdgeTypes::HAS_MEMBER;
        $hasTag = GraphEdgeTypes::HAS_TAG;

        $stack->push("MATCH (lo:{$course['type']} { id: {$course['id']} })-[r:$hasTag]->() DELETE r");
        if (isset($course['tags']) && is_array($course['tags'])) {
            foreach ($course['tags'] as $tag) {
                if ($tag = trim($tag)) {
                    $stack->push(
                        "MATCH (lo:{$course['type']} { id: {$course['id']} })"
                        . " MERGE (portal:Group { name: {portal} })"
                        . " MERGE (tag:Tag { name: {name} })"
                        . " MERGE (tag)-[:$hasGroup]->(portal)"
                        . " MERGE (portal)-[:$hasMember]->(tag)"
                        . " MERGE (lo)-[:$hasTag]->(tag)",
                        ['name' => $tag, 'portal' => "portal:{$course['instance_id']}"]
                    );
                }
            }
        }

        // Delete orphan tag
        $stack->push(
            "MATCH (tag:Tag)"
            . " WHERE NOT (tag)<-[:$hasTag]-()"
            . " DETACH DELETE tag"
        );

        // Delete orphan RO
        // RO must have at least 6 connection (2 to portal, 4 to two related entities)
        $stack->push(
            "MATCH (ro:RO)-[r]-(o)"
            . " WITH ro, count(o) AS count, collect(o) as col"
            . " WHERE count < 6"
            . " DETACH DELETE ro"
        );

        return $this;
    }

    private function linkEvent(Stack $stack, $course)
    {
        $hasEvent = GraphEdgeTypes::HAS_EVENT;

        if ($course['event']) {
            $stack->push(
                "MATCH (lo:{$course['type']} { id: {$course['id']} })"
                . " MERGE (event:Event {id: {$course['id']}}) ON CREATE SET event += {event} ON MATCH SET event += {event}"
                . " MERGE (lo)-[:$hasEvent]->(event)",
                ['event' => (array)$course['event']]
            );
        }
        else {
            $stack->push("MATCH (event:Event {id: {$course['id']} }) DELETE event");
        }

        return $this;
    }

    private function linkParent(Stack $stack, $course)
    {
        $hasItem = GraphEdgeTypes::HAS_ITEM;

        $stack->push("MATCH (lo:{$course['type']} { id: {$course['id']} }) MATCH ()-[r:$hasItem]->(lo) DELETE r");
        foreach ($course['parents'] as $item) {
            $itemType = isset($item['type']) ? $item['type'] : 'Course';
            $itemType = GraphEdgeTypes::type($itemType);
            $elective = isset($item['edge_type'])
                ? in_array($item['edge_type'], [EdgeTypes::HAS_ELECTIVE_LO, EdgeTypes::HAS_ELECTIVE_LI])
                : false;
            $stack->push(
                "MATCH (lo:{$course['type']} { id: {$course['id']} })"
                . " MERGE (parent:{$itemType}:Group { id: {$item['id']}, name: {parentName} })"
                . " MERGE (parent)-[r:$hasItem]->(lo) SET r.elective = {elective}",
                [
                    'parentName' => "lo:{$item['id']}",
                    'elective'   => $elective,
                ]
            );
        }

        return $this;
    }
}
