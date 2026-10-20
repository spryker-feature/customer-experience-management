<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Relationship;

use Generated\Api\Backend\CompanyBusinessUnitsBackendResource;
use Generated\Shared\Transfer\CompanyBusinessUnitTransfer;
use Spryker\Service\Serializer\SerializerServiceInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper\CompanyBusinessUnitResourceMapperInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\CompanyBusinessUnitReaderInterface;

class CompanyBusinessUnitsRelationshipResolver extends AbstractPerItemRelationshipResolver
{
    protected const string PARENT_PROPERTY_COMPANY_BUSINESS_UNIT_UUID = 'companyBusinessUnitUuid';

    public function __construct(
        RelationshipParentReaderInterface $relationshipParentReader,
        protected CompanyBusinessUnitReaderInterface $companyBusinessUnitReader,
        protected CompanyBusinessUnitResourceMapperInterface $companyBusinessUnitResourceMapper,
        protected SerializerServiceInterface $serializer,
    ) {
        parent::__construct($relationshipParentReader);
    }

    protected function getParentPropertyName(): string
    {
        return static::PARENT_PROPERTY_COMPANY_BUSINESS_UNIT_UUID;
    }

    /**
     * @param list<string> $uuids
     *
     * @return array<string, \Generated\Api\Backend\CompanyBusinessUnitsBackendResource>
     */
    protected function getResourcesIndexedByUuid(array $uuids): array
    {
        $companyBusinessUnitResources = [];

        foreach ($this->companyBusinessUnitReader->getCompanyBusinessUnitsIndexedByUuid($uuids) as $companyBusinessUnitUuid => $companyBusinessUnitTransfer) {
            $companyBusinessUnitResources[$companyBusinessUnitUuid] = $this->buildCompanyBusinessUnitResource($companyBusinessUnitTransfer);
        }

        return $companyBusinessUnitResources;
    }

    protected function buildCompanyBusinessUnitResource(
        CompanyBusinessUnitTransfer $companyBusinessUnitTransfer
    ): CompanyBusinessUnitsBackendResource {
        /** @var \Generated\Api\Backend\CompanyBusinessUnitsBackendResource $companyBusinessUnitResource */
        $companyBusinessUnitResource = $this->serializer->denormalize(
            $this->companyBusinessUnitResourceMapper->mapCompanyBusinessUnitTransferToResourceData($companyBusinessUnitTransfer),
            CompanyBusinessUnitsBackendResource::class,
        );

        return $companyBusinessUnitResource;
    }
}
