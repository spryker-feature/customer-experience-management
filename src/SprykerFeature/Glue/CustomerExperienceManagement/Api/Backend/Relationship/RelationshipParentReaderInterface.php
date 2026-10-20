<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Relationship;

interface RelationshipParentReaderInterface
{
    /**
     * @param array<object> $parentResources
     *
     * @return array<string, string|null>
     */
    public function getPropertyValuesIndexedByParentId(array $parentResources, string $propertyName): array;
}
