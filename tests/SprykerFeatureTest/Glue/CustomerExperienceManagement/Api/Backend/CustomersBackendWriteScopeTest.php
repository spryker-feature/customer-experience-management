<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Glue\CustomerExperienceManagement\Api\Backend;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use Codeception\Test\Unit;
use Generated\Api\Backend\CustomersBackendResource;
use ReflectionClass;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Auto-generated group annotations
 *
 * @group SprykerFeatureTest
 * @group Glue
 * @group CustomerExperienceManagement
 * @group Api
 * @group Backend
 * @group CustomersBackendWriteScopeTest
 * Add your own group annotations below this line
 */
class CustomersBackendWriteScopeTest extends Unit
{
    protected const string EMAIL = 'write.scope@spryker.com';

    /**
     * @var array<string, string|bool>
     */
    protected const array FULL_WRITE_PAYLOAD = [
        'email' => self::EMAIL,
        'salutation' => 'Ms',
        'firstName' => 'Sonia',
        'lastName' => 'Wagner',
        'gender' => 'Female',
        'dateOfBirth' => '1985-04-12',
        'phone' => '425-222-45-80',
        'company' => 'Spryker',
        'localeName' => 'de_DE',
        'storeName' => 'DE',
        'sendPasswordToken' => true,
        'sendRegistrationToken' => false,
    ];

    /**
     * @var array<string>
     */
    protected const array EXPECTED_CREATE_SCOPE = [
        'company',
        'dateOfBirth',
        'email',
        'firstName',
        'gender',
        'lastName',
        'localeName',
        'phone',
        'salutation',
        'sendPasswordToken',
        'sendRegistrationToken',
        'storeName',
    ];

    /**
     * @var array<string>
     */
    protected const array EXPECTED_UPDATE_SCOPE = [
        'company',
        'dateOfBirth',
        'email',
        'firstName',
        'gender',
        'lastName',
        'localeName',
        'phone',
        'salutation',
        'sendPasswordToken',
        'storeName',
    ];

    public function testCreateAcceptsSendRegistrationTokenBecauseItIsInTheCreateGroup(): void
    {
        // Act
        $resource = $this->denormalizeForOperation(Post::class);

        // Assert
        $this->assertFalse($resource->sendRegistrationToken);
    }

    public function testUpdateDropsSendRegistrationTokenButStillAppliesTheOtherAttributes(): void
    {
        // Act
        $resource = $this->denormalizeForOperation(Patch::class);

        // Assert
        $this->assertNull($resource->sendRegistrationToken);
        $this->assertSame(static::EMAIL, $resource->email);
    }

    public function testCreateAcceptsExactlyTheDocumentedWriteScope(): void
    {
        // Act
        $acceptedProperties = $this->resolveAcceptedProperties(Post::class);

        // Assert
        $this->assertSame(static::EXPECTED_CREATE_SCOPE, $acceptedProperties);
    }

    public function testUpdateAcceptsExactlyTheDocumentedWriteScope(): void
    {
        // Act
        $acceptedProperties = $this->resolveAcceptedProperties(Patch::class);

        // Assert
        $this->assertSame(static::EXPECTED_UPDATE_SCOPE, $acceptedProperties);
    }

    /**
     * @return array<string>
     */
    protected function resolveAcceptedProperties(string $operationClass): array
    {
        $resource = $this->denormalizeForOperation($operationClass);
        $acceptedProperties = [];

        foreach (static::FULL_WRITE_PAYLOAD as $propertyName => $submittedValue) {
            if (($resource->{$propertyName} ?? null) !== $submittedValue) {
                continue;
            }

            $acceptedProperties[] = $propertyName;
        }

        sort($acceptedProperties);

        return $acceptedProperties;
    }

    protected function denormalizeForOperation(string $operationClass): CustomersBackendResource
    {
        $serializer = new Serializer([new ObjectNormalizer(new ClassMetadataFactory(new AttributeLoader()))]);

        /** @var \Generated\Api\Backend\CustomersBackendResource $resource */
        $resource = $serializer->denormalize(
            static::FULL_WRITE_PAYLOAD,
            CustomersBackendResource::class,
            null,
            ['groups' => $this->resolveDenormalizationGroups($operationClass)],
        );

        return $resource;
    }

    /**
     * @return array<string>
     */
    protected function resolveDenormalizationGroups(string $operationClass): array
    {
        $apiResource = (new ReflectionClass(CustomersBackendResource::class))
            ->getAttributes(ApiResource::class)[0]
            ->newInstance();

        foreach ($apiResource->getOperations() ?? [] as $operation) {
            if (!$operation instanceof $operationClass) {
                continue;
            }

            return $operation->getDenormalizationContext()['groups'] ?? [];
        }

        $this->fail(sprintf('The customers resource declares no %s operation.', $operationClass));
    }
}
