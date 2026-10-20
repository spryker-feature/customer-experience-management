<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Zed\CustomerExperienceManagement;

use Codeception\Actor;
use Generated\Shared\Transfer\CompanyBusinessUnitTransfer;
use Generated\Shared\Transfer\CompanyTransfer;
use SprykerFeature\Zed\CustomerExperienceManagement\Business\CustomerExperienceManagementFacade;
use SprykerFeature\Zed\CustomerExperienceManagement\Business\CustomerExperienceManagementFacadeInterface;
use SprykerFeature\Zed\CustomerExperienceManagement\Persistence\CustomerExperienceManagementPersistenceFactory;
use SprykerFeature\Zed\CustomerExperienceManagement\Persistence\CustomerExperienceManagementRepository;

/**
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = null)
 * @method \SprykerFeature\Zed\CustomerExperienceManagement\Business\CustomerExperienceManagementFacadeInterface getFacade()
 *
 * @SuppressWarnings(PHPMD)
 */
class CustomerExperienceManagementBusinessTester extends Actor
{
    use _generated\CustomerExperienceManagementBusinessTesterActions;

    public function haveCompanyNamed(string $name): CompanyTransfer
    {
        return $this->haveCompany([CompanyTransfer::NAME => $name]);
    }

    public function haveCompanyBusinessUnitNamed(CompanyTransfer $companyTransfer, string $name): CompanyBusinessUnitTransfer
    {
        return $this->haveCompanyBusinessUnit([
            CompanyBusinessUnitTransfer::FK_COMPANY => $companyTransfer->getIdCompanyOrFail(),
            CompanyBusinessUnitTransfer::NAME => $name,
        ]);
    }

    public function getFacadeWithCustomerGroupMembershipCollectionMaxLimit(
        int $customerGroupMembershipCollectionMaxLimit
    ): CustomerExperienceManagementFacadeInterface {
        /** @var \SprykerFeature\Zed\CustomerExperienceManagement\CustomerExperienceManagementConfig $customerExperienceManagementConfig */
        $customerExperienceManagementConfig = $this->mockConfigMethod(
            'getCustomerGroupMembershipCollectionMaxLimit',
            $customerGroupMembershipCollectionMaxLimit,
        );
        $customerExperienceManagementRepository = (new CustomerExperienceManagementRepository())
            ->setFactory((new CustomerExperienceManagementPersistenceFactory())->setConfig($customerExperienceManagementConfig));

        return (new CustomerExperienceManagementFacade())->setRepository($customerExperienceManagementRepository);
    }
}
