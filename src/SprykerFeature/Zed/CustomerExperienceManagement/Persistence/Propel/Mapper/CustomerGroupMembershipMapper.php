<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Persistence\Propel\Mapper;

use Generated\Shared\Transfer\CustomerGroupCompanyBusinessUnitCollectionTransfer;
use Generated\Shared\Transfer\CustomerGroupCompanyBusinessUnitTransfer;
use Generated\Shared\Transfer\CustomerGroupCompanyCollectionTransfer;
use Generated\Shared\Transfer\CustomerGroupCompanyTransfer;

class CustomerGroupMembershipMapper
{
    public const string COLUMN_ID_CUSTOMER_GROUP = 'id_customer_group';

    public const string COLUMN_ID_COMPANY = 'id_company';

    public const string COLUMN_ID_COMPANY_BUSINESS_UNIT = 'id_company_business_unit';

    public const string COLUMN_COMPANY_UUID = 'company_uuid';

    public const string COLUMN_COMPANY_BUSINESS_UNIT_UUID = 'company_business_unit_uuid';

    public const string COLUMN_COMPANY_NAME = 'company_name';

    public const string COLUMN_COMPANY_BUSINESS_UNIT_NAME = 'company_business_unit_name';

    /**
     * @param array<array<string, mixed>> $customerGroupCompanyRows
     */
    public function mapCustomerGroupCompanyRowsToCustomerGroupCompanyCollectionTransfer(
        array $customerGroupCompanyRows,
        CustomerGroupCompanyCollectionTransfer $customerGroupCompanyCollectionTransfer
    ): CustomerGroupCompanyCollectionTransfer {
        foreach ($customerGroupCompanyRows as $customerGroupCompanyRow) {
            $customerGroupCompanyCollectionTransfer->addCustomerGroupCompany(
                (new CustomerGroupCompanyTransfer())
                    ->setIdCustomerGroup((int)$customerGroupCompanyRow[static::COLUMN_ID_CUSTOMER_GROUP])
                    ->setIdCompany((int)$customerGroupCompanyRow[static::COLUMN_ID_COMPANY])
                    ->setCompanyUuid((string)$customerGroupCompanyRow[static::COLUMN_COMPANY_UUID])
                    ->setCompanyName((string)$customerGroupCompanyRow[static::COLUMN_COMPANY_NAME]),
            );
        }

        return $customerGroupCompanyCollectionTransfer;
    }

    /**
     * @param array<array<string, mixed>> $customerGroupCompanyBusinessUnitRows
     */
    public function mapCustomerGroupCompanyBusinessUnitRowsToCustomerGroupCompanyBusinessUnitCollectionTransfer(
        array $customerGroupCompanyBusinessUnitRows,
        CustomerGroupCompanyBusinessUnitCollectionTransfer $customerGroupCompanyBusinessUnitCollectionTransfer
    ): CustomerGroupCompanyBusinessUnitCollectionTransfer {
        foreach ($customerGroupCompanyBusinessUnitRows as $customerGroupCompanyBusinessUnitRow) {
            $customerGroupCompanyBusinessUnitCollectionTransfer->addCustomerGroupCompanyBusinessUnit(
                (new CustomerGroupCompanyBusinessUnitTransfer())
                    ->setIdCustomerGroup((int)$customerGroupCompanyBusinessUnitRow[static::COLUMN_ID_CUSTOMER_GROUP])
                    ->setIdCompanyBusinessUnit((int)$customerGroupCompanyBusinessUnitRow[static::COLUMN_ID_COMPANY_BUSINESS_UNIT])
                    ->setCompanyBusinessUnitUuid((string)$customerGroupCompanyBusinessUnitRow[static::COLUMN_COMPANY_BUSINESS_UNIT_UUID])
                    ->setCompanyUuid((string)$customerGroupCompanyBusinessUnitRow[static::COLUMN_COMPANY_UUID])
                    ->setCompanyBusinessUnitName((string)$customerGroupCompanyBusinessUnitRow[static::COLUMN_COMPANY_BUSINESS_UNIT_NAME])
                    ->setCompanyName((string)$customerGroupCompanyBusinessUnitRow[static::COLUMN_COMPANY_NAME]),
            );
        }

        return $customerGroupCompanyBusinessUnitCollectionTransfer;
    }
}
