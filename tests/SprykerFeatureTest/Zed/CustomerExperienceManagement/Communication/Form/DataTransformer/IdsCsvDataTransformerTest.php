<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Zed\CustomerExperienceManagement\Communication\Form\DataTransformer;

use Codeception\Test\Unit;
use SprykerFeature\Zed\CustomerExperienceManagement\Communication\Form\DataTransformer\IdsCsvDataTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Auto-generated group annotations
 *
 * @group SprykerFeatureTest
 * @group Zed
 * @group CustomerExperienceManagement
 * @group Communication
 * @group Form
 * @group DataTransformer
 * @group IdsCsvDataTransformerTest
 * Add your own group annotations below this line
 */
class IdsCsvDataTransformerTest extends Unit
{
    protected const int IDS_LIMIT = 3;

    protected const string MESSAGE_TOO_MANY_IDS = 'At most %limit% entries can be selected at a time.';

    public function testTransformReturnsAnEmptyStringForMissingIds(): void
    {
        // Act
        $value = $this->createIdsCsvDataTransformer()->transform(null);

        // Assert
        $this->assertSame('', $value);
    }

    public function testTransformJoinsTheIdsWithCommas(): void
    {
        // Act
        $value = $this->createIdsCsvDataTransformer()->transform([3, 1]);

        // Assert
        $this->assertSame('3,1', $value);
    }

    public function testReverseTransformReturnsNoIdsForAnEmptyValue(): void
    {
        // Act
        $ids = $this->createIdsCsvDataTransformer()->reverseTransform(' ');

        // Assert
        $this->assertSame([], $ids);
    }

    public function testReverseTransformReturnsNoIdsForAMissingValue(): void
    {
        // Act
        $ids = $this->createIdsCsvDataTransformer()->reverseTransform(null);

        // Assert
        $this->assertSame([], $ids);
    }

    public function testReverseTransformReturnsTheUniqueIdsInTheirOrder(): void
    {
        // Act
        $ids = $this->createIdsCsvDataTransformer()->reverseTransform('3, 1,3');

        // Assert
        $this->assertSame([3, 1], $ids);
    }

    /**
     * @dataProvider provideInvalidValues
     */
    public function testReverseTransformRejectsAValueThatIsNotAListOfIds(string $value): void
    {
        // Expect
        $this->expectException(TransformationFailedException::class);

        // Act
        $this->createIdsCsvDataTransformer()->reverseTransform($value);
    }

    public function testReverseTransformAcceptsAsManyIdsAsTheLimit(): void
    {
        // Act
        $ids = $this->createIdsCsvDataTransformer()->reverseTransform('1,2,3');

        // Assert
        $this->assertSame([1, 2, 3], $ids);
    }

    public function testReverseTransformRejectsMoreIdsThanTheLimitBeforeReadingThem(): void
    {
        // Arrange
        $transformationFailedException = null;

        // Act
        try {
            $this->createIdsCsvDataTransformer()->reverseTransform('1,2,3,abc');
        } catch (TransformationFailedException $exception) {
            $transformationFailedException = $exception;
        }

        // Assert
        $this->assertNotNull($transformationFailedException);
        $this->assertSame(static::MESSAGE_TOO_MANY_IDS, $transformationFailedException->getInvalidMessage());
        $this->assertSame(['%limit%' => static::IDS_LIMIT], $transformationFailedException->getInvalidMessageParameters());
    }

    /**
     * @return array<string, array<string>>
     */
    public function provideInvalidValues(): array
    {
        return [
            'non-numeric ID' => ['1,abc'],
            'zero' => ['0'],
            'negative ID' => ['-5'],
            'empty item' => ['1,,2'],
            'decimal ID' => ['1.5'],
        ];
    }

    protected function createIdsCsvDataTransformer(): IdsCsvDataTransformer
    {
        return new IdsCsvDataTransformer(static::IDS_LIMIT);
    }
}
