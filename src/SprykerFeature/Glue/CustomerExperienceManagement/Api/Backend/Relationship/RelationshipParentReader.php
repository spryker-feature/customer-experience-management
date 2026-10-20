<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Relationship;

use ApiPlatform\Metadata\ApiProperty;
use ReflectionClass;
use ReflectionProperty;

class RelationshipParentReader implements RelationshipParentReaderInterface
{
    /**
     * @param array<object> $parentResources
     *
     * @return array<string, string|null>
     */
    public function getPropertyValuesIndexedByParentId(array $parentResources, string $propertyName): array
    {
        $identifierPropertyNames = [];
        $propertyValues = [];

        foreach ($parentResources as $parentResource) {
            $className = $parentResource::class;

            if (!array_key_exists($className, $identifierPropertyNames)) {
                $identifierPropertyNames[$className] = $this->findIdentifierPropertyName($className);
            }

            $parentId = $this->findPropertyValue($parentResource, $identifierPropertyNames[$className]);

            if ($parentId === null) {
                continue;
            }

            $propertyValues[$parentId] = $this->findPropertyValue($parentResource, $propertyName);
        }

        return $propertyValues;
    }

    protected function findPropertyValue(object $resource, ?string $propertyName): ?string
    {
        if ($propertyName === null) {
            return null;
        }

        $value = $resource->{$propertyName} ?? null;

        return $value === null ? null : (string)$value;
    }

    /**
     * @param class-string $className
     */
    protected function findIdentifierPropertyName(string $className): ?string
    {
        foreach ((new ReflectionClass($className))->getProperties(ReflectionProperty::IS_PUBLIC) as $reflectionProperty) {
            $apiPropertyAttribute = $reflectionProperty->getAttributes(ApiProperty::class)[0] ?? null;

            if ($apiPropertyAttribute !== null && $apiPropertyAttribute->newInstance()->isIdentifier()) {
                return $reflectionProperty->getName();
            }
        }

        return null;
    }
}
