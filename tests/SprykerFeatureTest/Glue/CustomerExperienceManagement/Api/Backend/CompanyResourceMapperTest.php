<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Glue\CustomerExperienceManagement\Api\Backend;

use Generated\Api\Backend\CompaniesBackendResource;
use Generated\Shared\Transfer\CompanyTransfer;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper\CompanyResourceMapper;
use SprykerTest\ApiPlatform\Test\BackendApiTestCase;

/**
 * Auto-generated group annotations
 *
 * @group SprykerFeatureTest
 * @group Glue
 * @group CustomerExperienceManagement
 * @group Api
 * @group Backend
 * @group CompanyResourceMapperTest
 * Add your own group annotations below this line
 */
class CompanyResourceMapperTest extends BackendApiTestCase
{
    protected const string UUID = '0818f408-cc84-575d-ad54-92118a0e4273';

    protected const int ID_COMPANY = 41;

    protected const string NAME = 'Acme Corporation';

    protected const string RENAMED = 'Acme Holdings';

    protected const string STATUS_APPROVED = 'approved';

    protected const string STATUS_DENIED = 'denied';

    public function testMapCompanyTransferToResourceDataMapsEveryReadableField(): void
    {
        // Act
        $resourceData = (new CompanyResourceMapper())->mapCompanyTransferToResourceData(
            $this->createCompanyTransfer(),
        );

        // Assert
        $this->assertSame(static::UUID, $resourceData[CompanyTransfer::UUID]);
        $this->assertSame(static::NAME, $resourceData[CompanyTransfer::NAME]);
        $this->assertSame(static::STATUS_APPROVED, $resourceData[CompanyTransfer::STATUS]);
        $this->assertTrue($resourceData[CompanyTransfer::IS_ACTIVE]);
    }

    public function testMapResourceToCompanyTransferAppliesEverySuppliedProperty(): void
    {
        // Arrange
        $resource = new CompaniesBackendResource();
        $resource->name = static::RENAMED;
        $resource->status = static::STATUS_DENIED;
        $resource->isActive = false;

        // Act
        $companyTransfer = (new CompanyResourceMapper())
            ->mapResourceToCompanyTransfer($resource, $this->createCompanyTransfer());

        // Assert
        $this->assertSame(static::RENAMED, $companyTransfer->getName());
        $this->assertSame(static::STATUS_DENIED, $companyTransfer->getStatus());
        $this->assertFalse($companyTransfer->getIsActive());
    }

    public function testMapResourceToCompanyTransferLeavesAbsentPropertiesUntouched(): void
    {
        // Arrange
        $resource = new CompaniesBackendResource();
        $resource->name = static::RENAMED;

        // Act
        $companyTransfer = (new CompanyResourceMapper())
            ->mapResourceToCompanyTransfer($resource, $this->createCompanyTransfer());

        // Assert
        $this->assertSame(static::RENAMED, $companyTransfer->getName());
        $this->assertSame(
            static::STATUS_APPROVED,
            $companyTransfer->getStatus(),
            'A name-only update must not reset the status.',
        );
        $this->assertTrue(
            $companyTransfer->getIsActive(),
            'A name-only update must not reset the active state.',
        );
    }

    /**
     * `false` is falsy, so a truthiness check in place of the explicit null check would drop this
     * update and silently leave the company active.
     */
    public function testMapResourceToCompanyTransferAppliesIsActiveFalse(): void
    {
        // Arrange
        $resource = new CompaniesBackendResource();
        $resource->isActive = false;

        // Act
        $companyTransfer = (new CompanyResourceMapper())
            ->mapResourceToCompanyTransfer($resource, $this->createCompanyTransfer());

        // Assert
        $this->assertFalse($companyTransfer->getIsActive());
        $this->assertSame(static::NAME, $companyTransfer->getName());
    }

    public function testMapResourceToCompanyTransferKeepsEverythingWhenNothingIsSupplied(): void
    {
        // Act
        $companyTransfer = (new CompanyResourceMapper())
            ->mapResourceToCompanyTransfer(new CompaniesBackendResource(), $this->createCompanyTransfer());

        // Assert
        $this->assertSame(static::NAME, $companyTransfer->getName());
        $this->assertSame(static::STATUS_APPROVED, $companyTransfer->getStatus());
        $this->assertTrue($companyTransfer->getIsActive());
    }

    protected function createCompanyTransfer(): CompanyTransfer
    {
        return (new CompanyTransfer())
            ->setIdCompany(static::ID_COMPANY)
            ->setUuid(static::UUID)
            ->setName(static::NAME)
            ->setStatus(static::STATUS_APPROVED)
            ->setIsActive(true);
    }
}
