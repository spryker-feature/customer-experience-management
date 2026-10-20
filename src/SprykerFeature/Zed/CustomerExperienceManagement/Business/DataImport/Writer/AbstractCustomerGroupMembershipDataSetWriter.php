<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\CustomerExperienceManagement\Business\DataImport\Writer;

use Exception;
use Spryker\Zed\DataImport\Business\Exception\DataSetBrokerTransactionFailedException;
use Spryker\Zed\DataImport\Business\Model\DataSet\DataSetInterface;
use Spryker\Zed\DataImport\Business\Model\DataSet\DataSetWriterInterface;
use Spryker\Zed\Kernel\Persistence\EntityManager\TransactionTrait;
use SprykerFeature\Zed\CustomerExperienceManagement\Business\DataImport\DataSet\CustomerGroupMembershipDataSetInterface;
use SprykerFeature\Zed\CustomerExperienceManagement\CustomerExperienceManagementConfig;
use SprykerFeature\Zed\CustomerExperienceManagement\Persistence\CustomerExperienceManagementEntityManagerInterface;

abstract class AbstractCustomerGroupMembershipDataSetWriter implements DataSetWriterInterface
{
    use TransactionTrait;

    protected const string ERROR_MESSAGE_CHUNK_REFUSED = 'Saving the %d memberships of the last %d accepted rows failed, none of them is saved: %s';

    /**
     * @var array<int, array<int, int>>
     */
    protected array $memberIdsByIdCustomerGroup = [];

    protected int $bufferedMembershipCount = 0;

    protected int $bufferedDataSetCount = 0;

    public function __construct(
        protected CustomerExperienceManagementEntityManagerInterface $customerExperienceManagementEntityManager,
        protected CustomerExperienceManagementConfig $customerExperienceManagementConfig
    ) {
    }

    abstract protected function getMemberIdDataSetKey(): string;

    /**
     * @param array<int, array<int>> $memberIdsByIdCustomerGroup
     */
    abstract protected function createMemberships(array $memberIdsByIdCustomerGroup): void;

    public function write(DataSetInterface $dataSet): void
    {
        $idCustomerGroup = (int)$dataSet[CustomerGroupMembershipDataSetInterface::KEY_ID_CUSTOMER_GROUP];
        $idMember = (int)$dataSet[$this->getMemberIdDataSetKey()];

        $this->bufferedDataSetCount++;

        if (isset($this->memberIdsByIdCustomerGroup[$idCustomerGroup][$idMember])) {
            return;
        }

        $this->memberIdsByIdCustomerGroup[$idCustomerGroup][$idMember] = $idMember;
        $this->bufferedMembershipCount++;

        if ($this->bufferedMembershipCount < $this->customerExperienceManagementConfig->getCustomerGroupRelationChunkSize()) {
            return;
        }

        $this->writeChunk($this->bufferedDataSetCount - 1);
    }

    public function flush(): void
    {
        $this->writeChunk($this->bufferedDataSetCount);
    }

    /**
     * @throws \Spryker\Zed\DataImport\Business\Exception\DataSetBrokerTransactionFailedException
     */
    protected function writeChunk(int $countedDataSetCount): void
    {
        if ($this->memberIdsByIdCustomerGroup === []) {
            return;
        }

        $memberIdsByIdCustomerGroup = $this->memberIdsByIdCustomerGroup;
        $bufferedMembershipCount = $this->bufferedMembershipCount;
        $bufferedDataSetCount = $this->bufferedDataSetCount;

        $this->memberIdsByIdCustomerGroup = [];
        $this->bufferedMembershipCount = 0;
        $this->bufferedDataSetCount = 0;

        try {
            $this->getTransactionHandler()->handleTransaction(function () use ($memberIdsByIdCustomerGroup): void {
                $this->createMemberships($memberIdsByIdCustomerGroup);
            });
        } catch (Exception $exception) {
            throw new DataSetBrokerTransactionFailedException(
                $countedDataSetCount,
                sprintf(static::ERROR_MESSAGE_CHUNK_REFUSED, $bufferedMembershipCount, $bufferedDataSetCount, $exception->getMessage()),
                0,
                $exception,
            );
        }
    }
}
