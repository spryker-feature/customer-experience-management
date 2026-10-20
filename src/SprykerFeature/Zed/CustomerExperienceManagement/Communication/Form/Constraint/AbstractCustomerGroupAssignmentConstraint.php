<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Communication\Form\Constraint;

use SprykerFeature\Zed\CustomerExperienceManagement\Persistence\CustomerExperienceManagementRepositoryInterface;
use Symfony\Component\Validator\Constraint;

abstract class AbstractCustomerGroupAssignmentConstraint extends Constraint
{
    public function __construct(
        protected CustomerExperienceManagementRepositoryInterface $customerExperienceManagementRepository,
        protected ?int $idCustomerGroup
    ) {
        parent::__construct();
    }

    /**
     * @return list<int>
     */
    abstract public function getIdsToAssign(mixed $assignment): array;

    /**
     * @return list<int>
     */
    abstract public function getIdsToDeAssign(mixed $assignment): array;

    /**
     * @param list<int> $ids
     *
     * @return list<int>
     */
    abstract public function getExistingIds(array $ids): array;

    /**
     * @param list<int> $ids
     *
     * @return list<int>
     */
    abstract public function getAssignedIds(int $idCustomerGroup, array $ids): array;

    abstract public function getUnknownIdsMessage(): string;

    abstract public function getIdsToAssignAndDeAssignMessage(): string;

    abstract public function getAlreadyAssignedIdsMessage(): string;

    abstract public function getNotAssignedIdsMessage(): string;

    public function getIdCustomerGroup(): ?int
    {
        return $this->idCustomerGroup;
    }

    public function validatedBy(): string
    {
        return CustomerGroupAssignmentConstraintValidator::class;
    }

    public function getTargets(): string
    {
        return static::CLASS_CONSTRAINT;
    }
}
