<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader;

use Generated\Shared\Transfer\CustomerTransfer;
use Spryker\Zed\Customer\Business\CustomerFacadeInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception\CustomersBackendExceptionFactory;

class CustomerReader implements CustomerReaderInterface
{
    public function __construct(
        protected CustomerFacadeInterface $customerFacade,
        protected CustomersBackendExceptionFactory $exceptionFactory,
    ) {
    }

    public function getCustomerByReference(string $customerReference): CustomerTransfer
    {
        $customerResponseTransfer = $this->customerFacade->findCustomerByReference($customerReference);

        if (!$customerResponseTransfer->getHasCustomer()) {
            throw $this->exceptionFactory->createCustomerNotFoundException($customerReference);
        }

        return $customerResponseTransfer->getCustomerTransferOrFail();
    }
}
