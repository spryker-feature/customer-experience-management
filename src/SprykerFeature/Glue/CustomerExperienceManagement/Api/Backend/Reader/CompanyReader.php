<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader;

use Generated\Shared\Transfer\CompanyTransfer;
use Spryker\Zed\Company\Business\CompanyFacadeInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception\CompaniesBackendExceptionFactory;

class CompanyReader implements CompanyReaderInterface
{
    public function __construct(
        protected CompanyFacadeInterface $companyFacade,
        protected CompaniesBackendExceptionFactory $exceptionFactory,
    ) {
    }

    public function getCompanyByUuid(string $uuid): CompanyTransfer
    {
        $companyResponseTransfer = $this->companyFacade->findCompanyByUuid(
            (new CompanyTransfer())->setUuid($uuid),
        );

        if (!$companyResponseTransfer->getIsSuccessful()) {
            throw $this->exceptionFactory->createCompanyNotFoundException($uuid);
        }

        return $companyResponseTransfer->getCompanyTransferOrFail();
    }
}
