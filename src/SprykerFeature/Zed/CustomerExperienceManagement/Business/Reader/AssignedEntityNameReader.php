<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Business\Reader;

use Generated\Shared\Transfer\CustomerCriteriaFilterTransfer;
use Spryker\Zed\Company\Business\CompanyFacadeInterface;
use Spryker\Zed\CompanyBusinessUnit\Business\CompanyBusinessUnitFacadeInterface;
use Spryker\Zed\Customer\Business\CustomerFacadeInterface;

class AssignedEntityNameReader implements AssignedEntityNameReaderInterface
{
    /**
     * @uses \Spryker\Zed\Customer\Communication\Reader\CustomerSuggestionReader::CUSTOMER_NAME_PATTERN
     */
    protected const string CUSTOMER_NAME_PATTERN = '%s (%s %s, %s)';

    /**
     * @uses \Spryker\Zed\CompanyGui\Communication\Formatter\CompanyNameFormatter::formatName()
     */
    protected const string COMPANY_NAME_PATTERN = '%s (id: %s)';

    /**
     * @uses \Spryker\Zed\CompanyBusinessUnitGui\Communication\Generator\CompanyBusinessUnitNameGenerator::NAME_PATTERN
     */
    protected const string COMPANY_BUSINESS_UNIT_NAME_PATTERN = '%s (id: %s) | %s';

    public function __construct(
        protected CustomerFacadeInterface $customerFacade,
        protected CompanyFacadeInterface $companyFacade,
        protected CompanyBusinessUnitFacadeInterface $companyBusinessUnitFacade
    ) {
    }

    public function findCustomerName(int $idCustomer): ?string
    {
        $customerCriteriaFilterTransfer = (new CustomerCriteriaFilterTransfer())
            ->addIdCustomer($idCustomer)
            ->setLimit(1);

        $customerTransfers = $this->customerFacade->getCustomerCollectionByCriteria($customerCriteriaFilterTransfer)->getCustomers();

        if ($customerTransfers->count() === 0) {
            return null;
        }

        $customerTransfer = $customerTransfers->offsetGet(0);

        return sprintf(
            static::CUSTOMER_NAME_PATTERN,
            $customerTransfer->getEmail(),
            $customerTransfer->getFirstName(),
            $customerTransfer->getLastName(),
            $customerTransfer->getCustomerReference(),
        );
    }

    public function findCompanyName(int $idCompany): ?string
    {
        $companyTransfer = $this->companyFacade->findCompanyById($idCompany);

        if ($companyTransfer === null) {
            return null;
        }

        return sprintf(static::COMPANY_NAME_PATTERN, $companyTransfer->getName(), $companyTransfer->getIdCompany());
    }

    public function findCompanyBusinessUnitName(int $idCompanyBusinessUnit): ?string
    {
        $companyBusinessUnitTransfer = $this->companyBusinessUnitFacade->findCompanyBusinessUnitById($idCompanyBusinessUnit);

        if ($companyBusinessUnitTransfer === null) {
            return null;
        }

        return sprintf(
            static::COMPANY_BUSINESS_UNIT_NAME_PATTERN,
            $companyBusinessUnitTransfer->getName(),
            $companyBusinessUnitTransfer->getIdCompanyBusinessUnit(),
            $companyBusinessUnitTransfer->getCompanyOrFail()->getName(),
        );
    }
}
