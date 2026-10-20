<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Relationship;

use Spryker\ApiPlatform\Relationship\PerItemRelationshipResolverInterface;

abstract class AbstractPerItemRelationshipResolver implements PerItemRelationshipResolverInterface
{
    public function __construct(protected RelationshipParentReaderInterface $relationshipParentReader)
    {
    }

    abstract protected function getParentPropertyName(): string;

    /**
     * @param list<string> $uuids
     *
     * @return array<string, object> Keyed by uuid.
     */
    abstract protected function getResourcesIndexedByUuid(array $uuids): array;

    /**
     * @param array<object> $parentResources
     * @param array<string, mixed> $context
     *
     * @return array<object>
     */
    public function resolve(array $parentResources, array $context): array
    {
        $resources = [];

        foreach ($this->resolvePerItem($parentResources, $context) as $parentRelatedResources) {
            foreach ($parentRelatedResources as $resource) {
                $resources[spl_object_id($resource)] = $resource;
            }
        }

        return array_values($resources);
    }

    /**
     * @param array<object> $parentResources
     * @param array<string, mixed> $context
     *
     * @return array<string, array<object>>
     */
    public function resolvePerItem(array $parentResources, array $context): array
    {
        $uuidsByParentId = $this->relationshipParentReader->getPropertyValuesIndexedByParentId(
            $parentResources,
            $this->getParentPropertyName(),
        );
        $resourcesByUuid = $this->getResourcesIndexedByUuid(array_values(array_unique(array_filter($uuidsByParentId))));

        $resourcesByParentId = [];

        foreach ($uuidsByParentId as $parentId => $uuid) {
            $resourcesByParentId[$parentId] = $uuid !== null && isset($resourcesByUuid[$uuid]) ? [$resourcesByUuid[$uuid]] : [];
        }

        return $resourcesByParentId;
    }
}
