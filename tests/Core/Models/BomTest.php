<?php

declare(strict_types=1);

/*
 * This file is part of CycloneDX PHP Library.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * SPDX-License-Identifier: Apache-2.0
 * Copyright (c) OWASP Foundation. All Rights Reserved.
 */

namespace CycloneDX\Tests\Core\Models;

use CycloneDX\Core\Collections\ComponentRepository;
use CycloneDX\Core\Collections\ExternalReferenceRepository;
use CycloneDX\Core\Collections\PropertyRepository;
use CycloneDX\Core\Collections\ToolRepository;
use CycloneDX\Core\Models\Bom;
use CycloneDX\Core\Models\Metadata;
use DomainException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DependsUsingShallowClone;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Bom::class)]
#[UsesClass(ComponentRepository::class)]
#[UsesClass(Metadata::class)]
#[UsesClass(ToolRepository::class)]
#[UsesClass(ExternalReferenceRepository::class)]
#[UsesClass(PropertyRepository::class)]
class BomTest extends TestCase
{
    public function testConstruct(): Bom
    {
        $bom = new Bom();

        self::assertNull($bom->getSerialNumber());
        self::assertSame(1, $bom->getVersion());
        self::assertCount(0, $bom->getComponents());
        self::assertCount(0, $bom->getExternalReferences());
        self::assertEquals(new Metadata(), $bom->getMetadata());
        self::assertCount(0, $bom->getProperties());

        return $bom;
    }

    // region serialNumber setter&getter

    #[DependsUsingShallowClone('testConstruct')]
    public function testSerialNumber(Bom $bom): Bom
    {
        $serialNumber = 'urn:uuid:3e671687-395b-41f5-a30f-a58921a69b79';
        $setOn = $bom->setSerialNumber($serialNumber);

        self::assertSame($bom, $setOn);
        self::assertSame($serialNumber, $bom->getSerialNumber());

        return $bom;
    }

    #[DependsUsingShallowClone('testSerialNumber')]
    public function testSerialNumberEmptyString(Bom $bom): void
    {
        $setOn = $bom->setSerialNumber('');

        self::assertSame($bom, $setOn);
        self::assertNull($bom->getSerialNumber());
    }

    #[DependsUsingShallowClone('testSerialNumber')]
    public function testSerialNumberEmptyStringInvalidValue(Bom $bom): void
    {
        $serialNumber = uniqid('invalid-value', true);
        $this->expectException(DomainException::class);
        $bom->setSerialNumber($serialNumber);
    }

    // endregion serialNumber setter&getter

    // region components setter&getter&modifiers

    #[DependsUsingShallowClone('testConstruct')]
    public function testComponentsSetterGetter(Bom $bom): void
    {
        $components = $this->createMock(ComponentRepository::class);
        $actual = $bom->setComponents($components);
        self::assertSame($bom, $actual);
        self::assertSame($components, $bom->getComponents());
    }

    // endregion components setter&getter&modifiers

    // region version setter&getter

    #[DependsUsingShallowClone('testConstruct')]
    public function testVersionSetterGetter(Bom $bom): void
    {
        $version = random_int(1, 255);
        $actual = $bom->setVersion($version);
        self::assertSame($bom, $actual);
        self::assertSame($version, $bom->getVersion());
    }

    #[DependsUsingShallowClone('testConstruct')]
    public function testVersionSetterInvalidValue(Bom $bom): void
    {
        $version = 0 - random_int(1, 255);
        $this->expectException(DomainException::class);
        $bom->setVersion($version);
    }

    // endregion version setter&getter

    // region metadata setter&getter

    #[DependsUsingShallowClone('testConstruct')]
    public function testMetadataSetterGetter(Bom $bom): void
    {
        $metadata = $this->createMock(Metadata::class);
        $actual = $bom->setMetadata($metadata);
        self::assertSame($bom, $actual);
        self::assertSame($metadata, $bom->getMetadata());
    }

    // endregion metadata setter&getter

    // region externalReferenceRepository setter&getter

    #[DependsUsingShallowClone('testConstruct')]
    public function testExternalReferenceRepositorySetterGetter(Bom $bom): void
    {
        $extRefRepo = $this->createMock(ExternalReferenceRepository::class);
        $actual = $bom->setExternalReferences($extRefRepo);
        self::assertSame($bom, $actual);
        self::assertSame($extRefRepo, $bom->getExternalReferences());
    }

    // endregion externalReferenceRepository setter&getter

    // region externalReferenceRepository setter&getter

    #[DependsUsingShallowClone('testConstruct')]
    public function testPropertiesSetterGetter(Bom $bom): void
    {
        $repo = $this->createMock(PropertyRepository::class);
        $actual = $bom->setProperties($repo);
        self::assertSame($bom, $actual);
        self::assertSame($repo, $bom->getProperties());
    }

    // endregion externalReferenceRepository setter&getter
}
