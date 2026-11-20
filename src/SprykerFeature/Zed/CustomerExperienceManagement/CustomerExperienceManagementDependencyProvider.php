<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement;

use Orm\Zed\Company\Persistence\SpyCompanyQuery;
use Orm\Zed\CompanyBusinessUnit\Persistence\SpyCompanyBusinessUnitQuery;
use Orm\Zed\CompanyUser\Persistence\SpyCompanyUserQuery;
use Orm\Zed\CustomerGroup\Persistence\SpyCustomerGroupToCustomerQuery;
use Spryker\Zed\Company\Business\CompanyFacadeInterface;
use Spryker\Zed\CompanyBusinessUnit\Business\CompanyBusinessUnitFacadeInterface;
use Spryker\Zed\Customer\Business\CustomerFacadeInterface;
use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Container;

class CustomerExperienceManagementDependencyProvider extends AbstractBundleDependencyProvider
{
    public const string PROPEL_QUERY_COMPANY = 'PROPEL_QUERY_COMPANY';

    public const string PROPEL_QUERY_COMPANY_BUSINESS_UNIT = 'PROPEL_QUERY_COMPANY_BUSINESS_UNIT';

    public const string PROPEL_QUERY_CUSTOMER_GROUP_TO_CUSTOMER = 'PROPEL_QUERY_CUSTOMER_GROUP_TO_CUSTOMER';

    public const string PROPEL_QUERY_COMPANY_USER = 'PROPEL_QUERY_COMPANY_USER';

    public const string FACADE_CUSTOMER = 'FACADE_CUSTOMER';

    public const string FACADE_COMPANY = 'FACADE_COMPANY';

    public const string FACADE_COMPANY_BUSINESS_UNIT = 'FACADE_COMPANY_BUSINESS_UNIT';

    public function provideBusinessLayerDependencies(Container $container): Container
    {
        $container = parent::provideBusinessLayerDependencies($container);

        $container = $this->addCustomerFacade($container);
        $container = $this->addCompanyFacade($container);
        $container = $this->addCompanyBusinessUnitFacade($container);

        return $container;
    }

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
        $container = $this->addCompanyUserPropelQuery($container);

        return $container;
    }

    protected function addCustomerFacade(Container $container): Container
    {
        $container->set(static::FACADE_CUSTOMER, static function (Container $container): CustomerFacadeInterface {
            return $container->getLocator()->customer()->facade();
        });

        return $container;
    }

    protected function addCompanyFacade(Container $container): Container
    {
        $container->set(static::FACADE_COMPANY, static function (Container $container): CompanyFacadeInterface {
            return $container->getLocator()->company()->facade();
        });

        return $container;
    }

    protected function addCompanyBusinessUnitFacade(Container $container): Container
    {
        $container->set(static::FACADE_COMPANY_BUSINESS_UNIT, static function (Container $container): CompanyBusinessUnitFacadeInterface {
            return $container->getLocator()->companyBusinessUnit()->facade();
        });

        return $container;
    }

    protected function addCompanyUserPropelQuery(Container $container): Container
    {
        $container->set(static::PROPEL_QUERY_COMPANY_USER, $container->factory(function (): SpyCompanyUserQuery {
            return SpyCompanyUserQuery::create();
        }));

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
