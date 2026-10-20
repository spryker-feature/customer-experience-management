<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement;

use Orm\Zed\Company\Persistence\SpyCompanyQuery;
use Orm\Zed\CompanyBusinessUnit\Persistence\SpyCompanyBusinessUnitQuery;
use Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroupToCustomerQuery;
use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Container;

class CustomerExperienceManagementDependencyProvider extends AbstractBundleDependencyProvider
{
    public const string PROPEL_QUERY_COMPANY = 'PROPEL_QUERY_COMPANY';

    public const string PROPEL_QUERY_COMPANY_BUSINESS_UNIT = 'PROPEL_QUERY_COMPANY_BUSINESS_UNIT';

    public const string PROPEL_QUERY_CUSTOMER_GROUP_TO_CUSTOMER = 'PROPEL_QUERY_CUSTOMER_GROUP_TO_CUSTOMER';

    public function provideCommunicationLayerDependencies(Container $container): Container
    {
        $container = parent::provideCommunicationLayerDependencies($container);

        $container = $this->addCompanyPropelQuery($container);
        $container = $this->addCompanyBusinessUnitPropelQuery($container);

        return $container;
    }

    public function providePersistenceLayerDependencies(Container $container): Container
    {
        $container = parent::providePersistenceLayerDependencies($container);

        $container = $this->addCompanyPropelQuery($container);
        $container = $this->addCompanyBusinessUnitPropelQuery($container);
        $container = $this->addCustomerGroupToCustomerPropelQuery($container);

        return $container;
    }

    protected function addCompanyPropelQuery(Container $container): Container
    {
        $container->set(static::PROPEL_QUERY_COMPANY, $container->factory(function (): SpyCompanyQuery {
            return SpyCompanyQuery::create();
        }));

        return $container;
    }

    protected function addCompanyBusinessUnitPropelQuery(Container $container): Container
    {
        $container->set(static::PROPEL_QUERY_COMPANY_BUSINESS_UNIT, $container->factory(function (): SpyCompanyBusinessUnitQuery {
            return SpyCompanyBusinessUnitQuery::create();
        }));

        return $container;
    }

    protected function addCustomerGroupToCustomerPropelQuery(Container $container): Container
    {
        $container->set(static::PROPEL_QUERY_CUSTOMER_GROUP_TO_CUSTOMER, $container->factory(function (): SpyCustomerGroupToCustomerQuery {
            return SpyCustomerGroupToCustomerQuery::create();
        }));

        return $container;
    }
}
