<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Persistence;

use Orm\Zed\Company\Persistence\SpyCompanyQuery;
use Orm\Zed\CompanyBusinessUnit\Persistence\SpyCompanyBusinessUnitQuery;
use Orm\Zed\CustomerExperienceManagement\Persistence\SpyCustomerGroupToCompanyBusinessUnitQuery;
use Orm\Zed\CustomerExperienceManagement\Persistence\SpyCustomerGroupToCompanyQuery;
use Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroupToCustomerQuery;
use Spryker\Zed\Kernel\Persistence\AbstractPersistenceFactory;
use SprykerFeature\Zed\CustomerExperienceManagement\CustomerExperienceManagementDependencyProvider;
use SprykerFeature\Zed\CustomerExperienceManagement\Persistence\Propel\Mapper\CustomerGroupMembershipMapper;

/**
 * @method \SprykerFeature\Zed\CustomerExperienceManagement\Persistence\CustomerExperienceManagementEntityManagerInterface getEntityManager()
 * @method \SprykerFeature\Zed\CustomerExperienceManagement\Persistence\CustomerExperienceManagementRepositoryInterface getRepository()
 * @method \SprykerFeature\Zed\CustomerExperienceManagement\CustomerExperienceManagementConfig getConfig()
 */
class CustomerExperienceManagementPersistenceFactory extends AbstractPersistenceFactory
{
    public function createCustomerGroupToCompanyQuery(): SpyCustomerGroupToCompanyQuery
    {
        return SpyCustomerGroupToCompanyQuery::create();
    }

    public function createCustomerGroupToCompanyBusinessUnitQuery(): SpyCustomerGroupToCompanyBusinessUnitQuery
    {
        return SpyCustomerGroupToCompanyBusinessUnitQuery::create();
    }

    public function createCustomerGroupMembershipMapper(): CustomerGroupMembershipMapper
    {
        return new CustomerGroupMembershipMapper();
    }

    public function getCompanyPropelQuery(): SpyCompanyQuery
    {
        return $this->getProvidedDependency(CustomerExperienceManagementDependencyProvider::PROPEL_QUERY_COMPANY);
    }

    public function getCompanyBusinessUnitPropelQuery(): SpyCompanyBusinessUnitQuery
    {
        return $this->getProvidedDependency(CustomerExperienceManagementDependencyProvider::PROPEL_QUERY_COMPANY_BUSINESS_UNIT);
    }

    public function getCustomerGroupToCustomerPropelQuery(): SpyCustomerGroupToCustomerQuery
    {
        return $this->getProvidedDependency(CustomerExperienceManagementDependencyProvider::PROPEL_QUERY_CUSTOMER_GROUP_TO_CUSTOMER);
    }
}
