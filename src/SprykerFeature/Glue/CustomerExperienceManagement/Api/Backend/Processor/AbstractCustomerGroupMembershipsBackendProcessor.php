<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Processor;

use Spryker\ApiPlatform\State\Processor\AbstractBackendProcessor;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Exception\CustomerGroupsBackendExceptionFactory;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Reader\CustomerGroupReaderInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Resolver\CustomerGroupMembershipIdResolverInterface;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Trait\UuidUriVariableAwareTrait;
use SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Writer\CustomerGroupMembershipWriterInterface;

abstract class AbstractCustomerGroupMembershipsBackendProcessor extends AbstractBackendProcessor
{
    use UuidUriVariableAwareTrait;

    public function __construct(
        protected CustomerGroupsBackendExceptionFactory $customerGroupsBackendExceptionFactory,
        protected CustomerGroupReaderInterface $customerGroupReader,
        protected CustomerGroupMembershipIdResolverInterface $customerGroupMembershipIdResolver,
        protected CustomerGroupMembershipWriterInterface $customerGroupMembershipWriter,
    ) {
    }
}
