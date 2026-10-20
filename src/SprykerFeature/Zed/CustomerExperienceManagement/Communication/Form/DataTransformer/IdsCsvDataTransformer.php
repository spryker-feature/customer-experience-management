<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Communication\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @implements \Symfony\Component\Form\DataTransformerInterface<list<int>|null, string>
 */
class IdsCsvDataTransformer implements DataTransformerInterface
{
    /**
     * @var non-empty-string
     */
    protected const string SEPARATOR = ',';

    protected const string PATTERN_ID = '/^[1-9][0-9]*$/';

    protected const string MESSAGE_INVALID_ID = 'The value "%s" is not a valid ID.';

    protected const string MESSAGE_TOO_MANY_IDS = 'The value has %d IDs, more than the limit of %d.';

    protected const string MESSAGE_TOO_MANY_IDS_INVALID = 'At most %limit% entries can be selected at a time.';

    protected const string PARAMETER_LIMIT = '%limit%';

    public function __construct(protected int $idsLimit)
    {
    }

    /**
     * @param list<int>|null $value
     */
    public function transform(mixed $value): string
    {
        if (!is_array($value)) {
            return '';
        }

        return implode(static::SEPARATOR, $value);
    }

    /**
     * @return list<int>
     */
    public function reverseTransform(mixed $value): array
    {
        if (!is_string($value) || trim($value) === '') {
            return [];
        }

        $this->assertIdCountIsWithinLimit($value);

        $ids = [];

        foreach (explode(static::SEPARATOR, $value) as $id) {
            $ids[] = $this->mapId(trim($id));
        }

        return array_values(array_unique($ids));
    }

    /**
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException
     */
    protected function assertIdCountIsWithinLimit(string $value): void
    {
        $idCount = substr_count($value, static::SEPARATOR) + 1;

        if ($idCount <= $this->idsLimit) {
            return;
        }

        throw new TransformationFailedException(
            sprintf(static::MESSAGE_TOO_MANY_IDS, $idCount, $this->idsLimit),
            0,
            null,
            static::MESSAGE_TOO_MANY_IDS_INVALID,
            [static::PARAMETER_LIMIT => $this->idsLimit],
        );
    }

    /**
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException
     */
    protected function mapId(string $id): int
    {
        if (preg_match(static::PATTERN_ID, $id) !== 1) {
            throw new TransformationFailedException(sprintf(static::MESSAGE_INVALID_ID, $id));
        }

        return (int)$id;
    }
}
