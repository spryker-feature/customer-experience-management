<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Glue\CustomerExperienceManagement\Api\Backend\Trait;

/**
 * Resolves the `uuid` URI variable for resources identified by it.
 *
 * The request attribute is a fallback: API Platform forwards URI variables to the
 * operation for item operations, but on write operations validated before routing
 * completes the variable is only present on the request attributes.
 *
 * Requires the host class to provide `findUriVariable()` (via
 * {@see \Spryker\ApiPlatform\State\Trait\UriVariableAwareTrait}) and `getRequest()`,
 * both available on AbstractBackendProvider and AbstractBackendProcessor.
 */
trait UuidUriVariableAwareTrait
{
    protected const string URI_VARIABLE_UUID = 'uuid';

    protected function getUuidUriVariable(): string
    {
        return (string)($this->findUriVariable(static::URI_VARIABLE_UUID)
            ?? $this->getRequest()->attributes->get(static::URI_VARIABLE_UUID));
    }
}
