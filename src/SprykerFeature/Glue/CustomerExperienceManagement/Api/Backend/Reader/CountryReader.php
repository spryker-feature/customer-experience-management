<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader;

use Generated\Shared\Transfer\CountryTransfer;
use Spryker\Zed\Country\Business\CountryFacadeInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception\CompanyBusinessUnitsBackendExceptionFactory;

class CountryReader implements CountryReaderInterface
{
    public function __construct(
        protected CountryFacadeInterface $countryFacade,
        protected CompanyBusinessUnitsBackendExceptionFactory $exceptionFactory,
    ) {
    }

    public function getCountryByIso2Code(string $iso2Code): CountryTransfer
    {
        if (!$this->countryFacade->hasCountry($iso2Code)) {
            throw $this->exceptionFactory->createUnknownCountryException($iso2Code);
        }

        return $this->countryFacade->getCountryByIso2Code($iso2Code);
    }
}
