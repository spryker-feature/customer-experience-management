<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement;

use Generated\Shared\Transfer\DataImporterDataSourceConfigurationTransfer;
use Orm\Zed\Company\Persistence\Map\SpyCompanyTableMap;
use Orm\Zed\CompanyBusinessUnit\Persistence\Map\SpyCompanyBusinessUnitTableMap;
use Spryker\Zed\Kernel\AbstractBundleConfig;

class CustomerExperienceManagementConfig extends AbstractBundleConfig
{
    public const string IMPORT_TYPE_CUSTOMER_GROUP = 'customer-group';

    public const string IMPORT_TYPE_CUSTOMER_GROUP_COMPANY = 'customer-group-company';

    public const string IMPORT_TYPE_CUSTOMER_GROUP_COMPANY_BUSINESS_UNIT = 'customer-group-company-business-unit';

    public const string IMPORT_TYPE_CUSTOMER_GROUP_CUSTOMER = 'customer-group-customer';

    /**
     * @var int<1, max>
     */
    protected const int CUSTOMER_GROUP_RELATION_CHUNK_SIZE = 500;

    /**
     * @var int<1, max>
     */
    protected const int CUSTOMER_GROUP_ASSIGNMENT_IDS_LIMIT = 1000;

    /**
     * @var int<1, max>
     */
    protected const int CUSTOMER_GROUP_MEMBERSHIP_COLLECTION_MAX_LIMIT = 1000;

    protected const string MODULE_NAME = 'CustomerExperienceManagement';

    protected const string IMPORT_FILE_NAME_CUSTOMER_GROUP = 'customer_group.csv';

    protected const string IMPORT_FILE_NAME_CUSTOMER_GROUP_COMPANY = 'customer_group_to_company.csv';

    protected const string IMPORT_FILE_NAME_CUSTOMER_GROUP_COMPANY_BUSINESS_UNIT = 'customer_group_to_company_business_unit.csv';

    protected const string IMPORT_FILE_NAME_CUSTOMER_GROUP_CUSTOMER = 'customer_group_to_customer.csv';

    protected const string SORT_FIELD_NAME = 'name';

    /**
     * Specification:
     * - Returns the number of company or business unit IDs assigned to or removed from a customer group per batch.
     * - Returns the number of memberships the membership imports collect, for any customer groups, before they write them in one transaction.
     * - Lower values reduce the size of each query at the cost of more queries.
     *
     * @api
     *
     * @return int<1, max>
     */
    public function getCustomerGroupRelationChunkSize(): int
    {
        return static::CUSTOMER_GROUP_RELATION_CHUNK_SIZE;
    }

    /**
     * Specification:
     * - Returns the maximum number of IDs one field of the Back Office company or business unit assignment
     *   of a customer group accepts in one save.
     * - A save with more IDs in a field fails the form.
     *
     * @api
     *
     * @return int<1, max>
     */
    public function getCustomerGroupAssignmentIdsLimit(): int
    {
        return static::CUSTOMER_GROUP_ASSIGNMENT_IDS_LIMIT;
    }

    /**
     * Specification:
     * - Returns the maximum number of memberships one call of `CustomerExperienceManagementFacade::getCustomerGroupCompanyCollection()`
     *   or `::getCustomerGroupCompanyBusinessUnitCollection()` returns.
     * - It is the limit of a call whose criteria carry no pagination or no limit, and it replaces a larger limit.
     *
     * @api
     *
     * @return int<1, max>
     */
    public function getCustomerGroupMembershipCollectionMaxLimit(): int
    {
        return static::CUSTOMER_GROUP_MEMBERSHIP_COLLECTION_MAX_LIMIT;
    }

    /**
     * Specification:
     * - Returns the sortable fields of a customer group's company memberships, mapping each accepted field name to the
     *   column that `CustomerGroupCompanyCriteriaTransfer.sortCollection` orders by.
     *
     * @api
     *
     * @module Company
     *
     * @return array<string, string>
     */
    public function getCustomerGroupCompanyCollectionSortableFieldMap(): array
    {
        return [
            static::SORT_FIELD_NAME => SpyCompanyTableMap::COL_NAME,
        ];
    }

    /**
     * Specification:
     * - Returns the sortable fields of a customer group's business unit memberships, mapping each accepted field name to
     *   the column that `CustomerGroupCompanyBusinessUnitCriteriaTransfer.sortCollection` orders by.
     *
     * @api
     *
     * @module CompanyBusinessUnit
     *
     * @return array<string, string>
     */
    public function getCustomerGroupCompanyBusinessUnitCollectionSortableFieldMap(): array
    {
        return [
            static::SORT_FIELD_NAME => SpyCompanyBusinessUnitTableMap::COL_NAME,
        ];
    }

    /**
     * Specification:
     * - Returns the data importer configuration of the customer groups.
     * - The source is the module's `customer_group.csv`; the `source` of a data import YAML entry replaces it.
     *
     * @api
     */
    public function getCustomerGroupDataImporterConfiguration(): DataImporterDataSourceConfigurationTransfer
    {
        return (new DataImporterDataSourceConfigurationTransfer())
            ->setImportType(static::IMPORT_TYPE_CUSTOMER_GROUP)
            ->setFileName(static::IMPORT_FILE_NAME_CUSTOMER_GROUP)
            ->setModuleName(static::MODULE_NAME);
    }

    /**
     * Specification:
     * - Returns the data importer configuration of the customer group to company memberships.
     * - The source is the module's `customer_group_to_company.csv`; the `source` of a data import YAML entry replaces it.
     *
     * @api
     */
    public function getCustomerGroupCompanyDataImporterConfiguration(): DataImporterDataSourceConfigurationTransfer
    {
        return (new DataImporterDataSourceConfigurationTransfer())
            ->setImportType(static::IMPORT_TYPE_CUSTOMER_GROUP_COMPANY)
            ->setFileName(static::IMPORT_FILE_NAME_CUSTOMER_GROUP_COMPANY)
            ->setModuleName(static::MODULE_NAME);
    }

    /**
     * Specification:
     * - Returns the data importer configuration of the customer group to business unit memberships.
     * - The source is the module's `customer_group_to_company_business_unit.csv`; the `source` of a data import YAML entry replaces it.
     *
     * @api
     */
    public function getCustomerGroupCompanyBusinessUnitDataImporterConfiguration(): DataImporterDataSourceConfigurationTransfer
    {
        return (new DataImporterDataSourceConfigurationTransfer())
            ->setImportType(static::IMPORT_TYPE_CUSTOMER_GROUP_COMPANY_BUSINESS_UNIT)
            ->setFileName(static::IMPORT_FILE_NAME_CUSTOMER_GROUP_COMPANY_BUSINESS_UNIT)
            ->setModuleName(static::MODULE_NAME);
    }

    /**
     * Specification:
     * - Returns the data importer configuration of the customer group to customer memberships.
     * - The source is the module's `customer_group_to_customer.csv`; the `source` of a data import YAML entry replaces it.
     *
     * @api
     */
    public function getCustomerGroupCustomerDataImporterConfiguration(): DataImporterDataSourceConfigurationTransfer
    {
        return (new DataImporterDataSourceConfigurationTransfer())
            ->setImportType(static::IMPORT_TYPE_CUSTOMER_GROUP_CUSTOMER)
            ->setFileName(static::IMPORT_FILE_NAME_CUSTOMER_GROUP_CUSTOMER)
            ->setModuleName(static::MODULE_NAME);
    }
}
