<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Glue\CustomerExperienceManagement\Api\Backend;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\PaginationTransfer;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Mapper\PaginationResourceMapper;

/**
 * Auto-generated group annotations
 *
 * @group SprykerFeatureTest
 * @group Glue
 * @group CustomerExperienceManagement
 * @group Api
 * @group Backend
 * @group PaginationResourceMapperTest
 * Add your own group annotations below this line
 */
class PaginationResourceMapperTest extends Unit
{
    public function testMapsTheResultTotalsOntoTheSharedPaginationObject(): void
    {
        // Arrange
        $paginationTransfer = (new PaginationTransfer())
            ->setNbResults(7)
            ->setPage(2)
            ->setLastPage(4)
            ->setMaxPerPage(2);

        // Act
        $pagination = (new PaginationResourceMapper())->mapPaginationTransferToPagination($paginationTransfer);

        // Assert
        $this->assertSame(7, $pagination->getNumFound());
        $this->assertSame(2, $pagination->getCurrentPage());
        $this->assertSame(4, $pagination->getMaxPage());
        $this->assertSame(2, $pagination->getCurrentItemsPerPage());
    }

    public function testFallsBackToADefinedPaginationWhenTheTotalsAreUnset(): void
    {
        // Act
        $pagination = (new PaginationResourceMapper())->mapPaginationTransferToPagination(new PaginationTransfer());

        // Assert
        $this->assertSame(0, $pagination->getNumFound());
        $this->assertSame(1, $pagination->getCurrentPage());
        $this->assertSame(1, $pagination->getMaxPage());
        $this->assertSame(0, $pagination->getCurrentItemsPerPage());
    }
}
