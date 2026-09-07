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
    protected const int READER_FALLBACK_LIMIT = 10;

    protected const int OPERATION_ITEMS_PER_PAGE = 25;

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

    public function testGetPaginationTransferFallsBackToItsOwnLimitWhenTheOperationDeclaresNone(): void
    {
        // Arrange
        $collectionQueryReader = $this->createCollectionQueryReader();

        // Act
        $paginationTransfer = $collectionQueryReader->getPaginationTransfer(new Request());

        // Assert
        $this->assertSame(1, $paginationTransfer->getPage());
        $this->assertSame(static::READER_FALLBACK_LIMIT, $paginationTransfer->getMaxPerPage());
    }

    public function testGetPaginationTransferPrefersTheRequestLimitOverTheOperationDefault(): void
    {
        // Arrange
        $collectionQueryReader = $this->createCollectionQueryReader();

        // Act
        $paginationTransfer = $collectionQueryReader->getPaginationTransfer(
            new Request(['page' => ['limit' => '3']]),
            50,
        );

        // Assert
        $this->assertSame(3, $paginationTransfer->getMaxPerPage());
    }

    /**
     * @dataProvider offsetToPageDataProvider
     */
    public function testGetPaginationTransferConvertsTheOffsetToTheContainingPage(
        int $limit,
        int $offset,
        int $expectedPage
    ): void {
        // Arrange
        $collectionQueryReader = $this->createCollectionQueryReader();

        // Act
        $paginationTransfer = $collectionQueryReader->getPaginationTransfer(
            new Request(['page' => ['limit' => (string)$limit, 'offset' => (string)$offset]]),
        );

        // Assert
        $this->assertSame($expectedPage, $paginationTransfer->getPage());
        $this->assertSame($limit, $paginationTransfer->getMaxPerPage());
    }

    /**
     * @return array<string, array{int, int, int}>
     */
    protected function offsetToPageDataProvider(): array
    {
        return [
            'first page' => [5, 0, 1],
            'exact page boundary' => [5, 5, 2],
            'inside the second page' => [5, 7, 2],
            'last item of the second page' => [5, 9, 2],
            'third page' => [5, 10, 3],
            'negative offset clamps to the first page' => [5, -10, 1],
        ];
    }

    /**
     * @dataProvider scalarPageDataProvider
     */
    public function testGetPaginationTransferReadsAScalarPageAsAPageNumber(
        string $page,
        int $expectedPage
    ): void {
        // Arrange
        $collectionQueryReader = $this->createCollectionQueryReader();

        // Act
        $paginationTransfer = $collectionQueryReader->getPaginationTransfer(
            new Request(['page' => $page]),
            static::OPERATION_ITEMS_PER_PAGE,
        );

        // Assert
        $this->assertSame($expectedPage, $paginationTransfer->getPage());
        $this->assertSame(static::OPERATION_ITEMS_PER_PAGE, $paginationTransfer->getMaxPerPage());
    }

    /**
     * @return array<string, array{string, int}>
     */
    protected function scalarPageDataProvider(): array
    {
        return [
            'the page number API Platform documents' => ['3', 3],
            'first page' => ['1', 1],
            'zero clamps to the first page' => ['0', 1],
            'negative clamps to the first page' => ['-2', 1],
            'not a number resolves to the first page' => ['abc', 1],
            'empty string resolves to the first page' => ['', 1],
        ];
    }

    /**
     * @dataProvider nonPositiveLimitDataProvider
     */
    public function testGetPaginationTransferFallsBackOnANonPositiveLimit(string $limit): void
    {
        // Arrange
        $collectionQueryReader = $this->createCollectionQueryReader();

        // Act
        $paginationTransfer = $collectionQueryReader->getPaginationTransfer(
            new Request(['page' => ['limit' => $limit]]),
        );

        // Assert
        $this->assertSame(static::READER_FALLBACK_LIMIT, $paginationTransfer->getMaxPerPage());
        $this->assertSame(1, $paginationTransfer->getPage());
    }

    /**
     * @return array<string, array{string}>
     */
    protected function nonPositiveLimitDataProvider(): array
    {
        return [
            'zero' => ['0'],
            'negative' => ['-5'],
            'not a number' => ['all'],
        ];
    }

    protected function createCollectionQueryReader(): CollectionQueryReader
    {
        return new CollectionQueryReader(new CollectionQueryExceptionFactory());
    }
}
