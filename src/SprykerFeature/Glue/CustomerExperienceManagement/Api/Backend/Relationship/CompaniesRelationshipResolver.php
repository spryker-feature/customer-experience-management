<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Relationship;

use Generated\Api\Backend\CompaniesBackendResource;
use Generated\Shared\Transfer\CompanyTransfer;
use Spryker\Service\Serializer\SerializerServiceInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper\CompanyResourceMapperInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\CompanyReaderInterface;

class CompaniesRelationshipResolver extends AbstractPerItemRelationshipResolver
{
    protected const string PARENT_PROPERTY_COMPANY_UUID = 'companyUuid';

    public function __construct(
        RelationshipParentReaderInterface $relationshipParentReader,
        protected CompanyReaderInterface $companyReader,
        protected CompanyResourceMapperInterface $companyResourceMapper,
        protected SerializerServiceInterface $serializer,
    ) {
        parent::__construct($relationshipParentReader);
    }

    protected function getParentPropertyName(): string
    {
        return static::PARENT_PROPERTY_COMPANY_UUID;
    }

    /**
     * @param list<string> $uuids
     *
     * @return array<string, \Generated\Api\Backend\CompaniesBackendResource>
     */
    protected function getResourcesIndexedByUuid(array $uuids): array
    {
        $companyResources = [];

        foreach ($this->companyReader->getCompaniesIndexedByUuid($uuids) as $companyUuid => $companyTransfer) {
            $companyResources[$companyUuid] = $this->buildCompanyResource($companyTransfer);
        }

        return $companyResources;
    }

    protected function buildCompanyResource(CompanyTransfer $companyTransfer): CompaniesBackendResource
    {
        /** @var \Generated\Api\Backend\CompaniesBackendResource $companyResource */
        $companyResource = $this->serializer->denormalize(
            $this->companyResourceMapper->mapCompanyTransferToResourceData($companyTransfer),
            CompaniesBackendResource::class,
        );

        return $companyResource;
    }
}
