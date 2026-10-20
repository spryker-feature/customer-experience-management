<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Business;

use Generated\Shared\Transfer\CustomerGroupCompanyBusinessUnitCollectionTransfer;
use Generated\Shared\Transfer\CustomerGroupCompanyBusinessUnitCriteriaTransfer;
use Generated\Shared\Transfer\CustomerGroupCompanyCollectionTransfer;
use Generated\Shared\Transfer\CustomerGroupCompanyCriteriaTransfer;

interface CustomerExperienceManagementFacadeInterface
{
    /**
     * Specification:
     * - Returns the company memberships of customer groups, with `idCustomerGroup`, `idCompany`, `companyUuid` and `companyName`
     *   per membership.
     * - Filters by `CustomerGroupCompanyConditions.customerGroupIds` and `.companyUuids`, and by `.searchTerm` as a
     *   case-insensitive partial match on the company name. The conditions are combined with AND.
     * - Sorts by `CustomerGroupCompanyCriteria.sortCollection` with the fields of
     *   `CustomerExperienceManagementConfig::getCustomerGroupCompanyCollectionSortableFieldMap()` and ignores other fields.
     * - Orders by company ID last, in the direction of the last applied sort, or ascending without one.
     * - Applies the `offset` and `limit` of `CustomerGroupCompanyCriteria.pagination`.
     * - Returns at most `CustomerExperienceManagementConfig::getCustomerGroupMembershipCollectionMaxLimit()` memberships. This
     *   maximum is the limit when the criteria carry no pagination or no limit, and it replaces a larger limit.
     * - Returns the applied pagination, with its `limit` and with `nbResults` set to the number of matches, so the caller
     *   can tell when matches were left out.
     *
     * @api
     */
    public function getCustomerGroupCompanyCollection(
        CustomerGroupCompanyCriteriaTransfer $customerGroupCompanyCriteriaTransfer
    ): CustomerGroupCompanyCollectionTransfer;

    /**
     * Specification:
     * - Returns the business unit memberships of customer groups, with `idCustomerGroup`, `idCompanyBusinessUnit`,
     *   `companyBusinessUnitUuid`, `companyBusinessUnitName`, and the `companyUuid` and `companyName` of the business unit's
     *   company per membership.
     * - Filters by `CustomerGroupCompanyBusinessUnitConditions.customerGroupIds`, `.companyBusinessUnitUuids` and
     *   `.companyUuids` (the business unit's company), and by `.searchTerm` as a case-insensitive partial match on the
     *   business unit name. The conditions are combined with AND.
     * - Sorts by `CustomerGroupCompanyBusinessUnitCriteria.sortCollection` with the fields of
     *   `CustomerExperienceManagementConfig::getCustomerGroupCompanyBusinessUnitCollectionSortableFieldMap()` and ignores
     *   other fields.
     * - Orders by business unit ID last, in the direction of the last applied sort, or ascending without one.
     * - Applies the `offset` and `limit` of `CustomerGroupCompanyBusinessUnitCriteria.pagination`.
     * - Returns at most `CustomerExperienceManagementConfig::getCustomerGroupMembershipCollectionMaxLimit()` memberships. This
     *   maximum is the limit when the criteria carry no pagination or no limit, and it replaces a larger limit.
     * - Returns the applied pagination, with its `limit` and with `nbResults` set to the number of matches, so the caller
     *   can tell when matches were left out.
     *
     * @api
     */
    public function getCustomerGroupCompanyBusinessUnitCollection(
        CustomerGroupCompanyBusinessUnitCriteriaTransfer $customerGroupCompanyBusinessUnitCriteriaTransfer
    ): CustomerGroupCompanyBusinessUnitCollectionTransfer;
}
