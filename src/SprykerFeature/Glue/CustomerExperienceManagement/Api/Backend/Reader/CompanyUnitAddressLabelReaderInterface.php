<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader;

use Generated\Shared\Transfer\CompanyUnitAddressLabelCollectionTransfer;

interface CompanyUnitAddressLabelReaderInterface
{
    /**
     * @param array<int, string> $labelNames
     *
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException When a name matches no configured label.
     */
    public function getLabelCollectionByNames(array $labelNames): CompanyUnitAddressLabelCollectionTransfer;
}
