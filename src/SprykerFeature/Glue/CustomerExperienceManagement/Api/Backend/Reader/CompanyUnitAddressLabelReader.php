<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader;

use Generated\Shared\Transfer\CompanyUnitAddressLabelCollectionTransfer;
use Generated\Shared\Transfer\CompanyUnitAddressLabelConditionsTransfer;
use Generated\Shared\Transfer\CompanyUnitAddressLabelCriteriaTransfer;
use Spryker\Zed\CompanyUnitAddressLabel\Business\CompanyUnitAddressLabelFacadeInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception\CompanyBusinessUnitsBackendExceptionFactory;

class CompanyUnitAddressLabelReader implements CompanyUnitAddressLabelReaderInterface
{
    public function __construct(
        protected CompanyUnitAddressLabelFacadeInterface $companyUnitAddressLabelFacade,
        protected CompanyBusinessUnitsBackendExceptionFactory $exceptionFactory,
    ) {
    }

    /**
     * @param array<int, string> $labelNames
     *
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException When a name matches no configured label.
     */
    public function getLabelCollectionByNames(array $labelNames): CompanyUnitAddressLabelCollectionTransfer
    {
        $companyUnitAddressLabelCollectionTransfer = new CompanyUnitAddressLabelCollectionTransfer();

        if ($labelNames === []) {
            return $companyUnitAddressLabelCollectionTransfer;
        }

        $matchedLabelsByName = $this->indexLabelsByName($labelNames);

        foreach ($labelNames as $labelName) {
            if (!isset($matchedLabelsByName[$labelName])) {
                throw $this->exceptionFactory->createUnknownCompanyUnitAddressLabelException(
                    $labelName,
                    $this->getConfiguredLabelNames(),
                );
            }

            $companyUnitAddressLabelCollectionTransfer->addLabels($matchedLabelsByName[$labelName]);
        }

        return $companyUnitAddressLabelCollectionTransfer;
    }

    /**
     * @return array<int, string>
     */
    protected function getConfiguredLabelNames(): array
    {
        return array_keys($this->indexLabelsByName());
    }

    /**
     * @param array<int, string> $labelNames
     *
     * @return array<string, \Generated\Shared\Transfer\SpyCompanyUnitAddressLabelEntityTransfer>
     */
    protected function indexLabelsByName(array $labelNames = []): array
    {
        $companyUnitAddressLabelCollectionTransfer = $this->companyUnitAddressLabelFacade->getCompanyUnitAddressLabelCollection(
            (new CompanyUnitAddressLabelCriteriaTransfer())->setCompanyUnitAddressLabelConditions(
                (new CompanyUnitAddressLabelConditionsTransfer())->setNames($labelNames),
            ),
        );

        $labelsByName = [];

        foreach ($companyUnitAddressLabelCollectionTransfer->getLabels() as $labelEntityTransfer) {
            $labelsByName[$labelEntityTransfer->getNameOrFail()] = $labelEntityTransfer;
        }

        return $labelsByName;
    }
}
