<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Glue\CustomerExperienceManagement\Api\Backend;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\AddressTransfer;
use Spryker\ApiPlatform\Exception\GlueApiException;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception\CollectionQueryExceptionFactory;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Request\CollectionQueryReader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Auto-generated group annotations
 *
 * @group SprykerFeatureTest
 * @group Glue
 * @group CustomerExperienceManagement
 * @group Api
 * @group Backend
 * @group CollectionQueryReaderTest
 * Add your own group annotations below this line
 */
class CollectionQueryReaderTest extends Unit
{
 /**
  * @var list<string>
  */
    protected const array SORTABLE_FIELDS = [AddressTransfer::CITY, AddressTransfer::ZIP_CODE];

    /**
     * @dataProvider rejectedSortValueDataProvider
     */
    public function testGetSortCollectionRejectsAFieldOutsideTheAllowList(string $sortValue): void
    {
        // Arrange
        $collectionQueryReader = $this->createCollectionQueryReader();

        // Act
        try {
            $collectionQueryReader->getSortCollection(new Request(['sort' => $sortValue]), static::SORTABLE_FIELDS);
            $this->fail('Expected a GlueApiException to be thrown.');
        } catch (GlueApiException $glueApiException) {
            // Assert
            $this->assertSame(Response::HTTP_BAD_REQUEST, $glueApiException->getStatusCode());
        }
    }

    /**
     * @return array<string, array{string}>
     */
    protected function rejectedSortValueDataProvider(): array
    {
        return [
            'unknown field' => ['unknownField'],
            'sql fragment' => ['city; DROP TABLE spy_customer_address; --'],
            'known field with a trailing fragment' => ['city zipCode'],
            'one unknown field among known ones' => ['city,unknownField'],
        ];
    }

    /**
     * @dataProvider emptySortValueDataProvider
     */
    public function testGetSortCollectionIgnoresValuesThatNameNoField(string $sortValue): void
    {
        // Arrange
        $collectionQueryReader = $this->createCollectionQueryReader();

        // Act
        $sortTransfers = $collectionQueryReader->getSortCollection(
            new Request(['sort' => $sortValue]),
            static::SORTABLE_FIELDS,
        );

        // Assert
        $this->assertSame([], $sortTransfers);
    }

    /**
     * @return array<string, array{string}>
     */
    protected function emptySortValueDataProvider(): array
    {
        return [
            'empty string' => [''],
            'separators only' => [',,'],
            'whitespace between separators' => [' , '],
        ];
    }

    public function testGetSortCollectionReturnsNoSortWhenTheParameterIsAbsent(): void
    {
        // Arrange
        $collectionQueryReader = $this->createCollectionQueryReader();

        // Act
        $sortTransfers = $collectionQueryReader->getSortCollection(new Request(), static::SORTABLE_FIELDS);

        // Assert
        $this->assertSame([], $sortTransfers);
    }

    public function testGetSortCollectionTrimsSurroundingWhitespaceAroundAField(): void
    {
        // Arrange
        $collectionQueryReader = $this->createCollectionQueryReader();

        // Act
        $sortTransfers = $collectionQueryReader->getSortCollection(
            new Request(['sort' => ' city , -zipCode ']),
            static::SORTABLE_FIELDS,
        );

        // Assert
        $this->assertCount(2, $sortTransfers);
        $this->assertSame(AddressTransfer::CITY, $sortTransfers[0]->getField());
        $this->assertTrue($sortTransfers[0]->getIsAscending());
        $this->assertSame(AddressTransfer::ZIP_CODE, $sortTransfers[1]->getField());
        $this->assertFalse($sortTransfers[1]->getIsAscending());
    }

    public function testGetSortCollectionRejectsEverySortWhenTheAllowListIsEmpty(): void
    {
        // Arrange
        $collectionQueryReader = $this->createCollectionQueryReader();

        // Act & Assert
        $this->expectException(GlueApiException::class);

        $collectionQueryReader->getSortCollection(new Request(['sort' => AddressTransfer::CITY]), []);
    }

    protected function createCollectionQueryReader(): CollectionQueryReader
    {
        return new CollectionQueryReader(new CollectionQueryExceptionFactory());
    }
}
