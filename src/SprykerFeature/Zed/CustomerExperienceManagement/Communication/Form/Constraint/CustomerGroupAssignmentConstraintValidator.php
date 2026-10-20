<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Communication\Form\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class CustomerGroupAssignmentConstraintValidator extends ConstraintValidator
{
    protected const string PARAMETER_IDS = '%ids%';

    protected const string IDS_SEPARATOR = ', ';

    /**
     * @throws \Symfony\Component\Validator\Exception\UnexpectedTypeException
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof AbstractCustomerGroupAssignmentConstraint) {
            throw new UnexpectedTypeException($constraint, AbstractCustomerGroupAssignmentConstraint::class);
        }

        $idsToAssign = $constraint->getIdsToAssign($value);
        $idsToDeAssign = $constraint->getIdsToDeAssign($value);
        $ids = array_values(array_unique(array_merge($idsToAssign, $idsToDeAssign)));

        if ($ids === []) {
            return;
        }

        $idsToAssignAndDeAssign = array_values(array_intersect($idsToAssign, $idsToDeAssign));
        $unknownIds = array_values(array_diff($ids, $constraint->getExistingIds($ids)));
        $idsToCheckAgainstMembers = array_values(array_diff($ids, $idsToAssignAndDeAssign, $unknownIds));
        $assignedIds = $this->getAssignedIds($constraint, $idsToCheckAgainstMembers);

        $this->addIdsViolation($constraint->getIdsToAssignAndDeAssignMessage(), $idsToAssignAndDeAssign);
        $this->addIdsViolation($constraint->getUnknownIdsMessage(), $unknownIds);
        $this->addIdsViolation(
            $constraint->getAlreadyAssignedIdsMessage(),
            array_intersect($idsToAssign, $idsToCheckAgainstMembers, $assignedIds),
        );
        $this->addIdsViolation(
            $constraint->getNotAssignedIdsMessage(),
            array_diff(array_intersect($idsToDeAssign, $idsToCheckAgainstMembers), $assignedIds),
        );
    }

    /**
     * @param list<int> $ids
     *
     * @return list<int>
     */
    protected function getAssignedIds(AbstractCustomerGroupAssignmentConstraint $constraint, array $ids): array
    {
        $idCustomerGroup = $constraint->getIdCustomerGroup();

        if ($idCustomerGroup === null || $ids === []) {
            return [];
        }

        return $constraint->getAssignedIds($idCustomerGroup, $ids);
    }

    /**
     * @param array<int> $ids
     */
    protected function addIdsViolation(string $message, array $ids): void
    {
        if ($ids === []) {
            return;
        }

        $this->context
            ->buildViolation($message)
            ->setParameter(static::PARAMETER_IDS, implode(static::IDS_SEPARATOR, $ids))
            ->addViolation();
    }
}
