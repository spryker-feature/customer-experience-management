<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Persistence\Propel\Mapper;

use DateTimeInterface;
use Generated\Shared\Transfer\CustomerGroupTransfer;
use Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroup;
use Propel\Runtime\Collection\Collection;

class CustomerGroupMapper
{
    /**
     * @uses \Spryker\Zed\CustomerGroup\Persistence\Propel\Mapper\CustomerGroupMapper::DATE_TIME_FORMAT
     */
    protected const string DATE_TIME_FORMAT = DateTimeInterface::ATOM;

    /**
     * @param \Propel\Runtime\Collection\Collection<\Orm\Zed\CustomerExperienceManagement\Persistence\SpyCustomerGroupToCompany> $customerGroupToCompanyEntities
     *
     * @return list<\Generated\Shared\Transfer\CustomerGroupTransfer>
     */
    public function mapCustomerGroupToCompanyEntitiesToCustomerGroupTransfers(Collection $customerGroupToCompanyEntities): array
    {
        $customerGroupTransfers = [];

        foreach ($customerGroupToCompanyEntities as $customerGroupToCompanyEntity) {
            $customerGroupTransfers[] = $this->mapCustomerGroupEntityToCustomerGroupTransfer(
                $customerGroupToCompanyEntity->getCustomerGroup(),
                new CustomerGroupTransfer(),
            );
        }

        return $customerGroupTransfers;
    }

    /**
     * @param \Propel\Runtime\Collection\Collection<\Orm\Zed\CustomerExperienceManagement\Persistence\SpyCustomerGroupToCompanyBusinessUnit> $customerGroupToCompanyBusinessUnitEntities
     * @param array<int, list<\Generated\Shared\Transfer\CustomerGroupTransfer>> $customerGroupTransfersByIdCompanyBusinessUnit
     *
     * @return array<int, list<\Generated\Shared\Transfer\CustomerGroupTransfer>>
     */
    public function mapCustomerGroupToCompanyBusinessUnitEntitiesToCustomerGroupTransfersByIdCompanyBusinessUnit(
        Collection $customerGroupToCompanyBusinessUnitEntities,
        array $customerGroupTransfersByIdCompanyBusinessUnit
    ): array {
        foreach ($customerGroupToCompanyBusinessUnitEntities as $customerGroupToCompanyBusinessUnitEntity) {
            $customerGroupTransfersByIdCompanyBusinessUnit[$customerGroupToCompanyBusinessUnitEntity->getFkCompanyBusinessUnit()][] =
                $this->mapCustomerGroupEntityToCustomerGroupTransfer(
                    $customerGroupToCompanyBusinessUnitEntity->getCustomerGroup(),
                    new CustomerGroupTransfer(),
                );
        }

        return $customerGroupTransfersByIdCompanyBusinessUnit;
    }

    public function mapCustomerGroupEntityToCustomerGroupTransfer(
        SpyCustomerGroup $customerGroupEntity,
        CustomerGroupTransfer $customerGroupTransfer
    ): CustomerGroupTransfer {
        $customerGroupTransfer->fromArray($customerGroupEntity->toArray(), true);

        $createdAt = $customerGroupEntity->getCreatedAt();

        return $customerGroupTransfer->setCreatedAt(
            $createdAt instanceof DateTimeInterface ? $createdAt->format(static::DATE_TIME_FORMAT) : null,
        );
    }
}
