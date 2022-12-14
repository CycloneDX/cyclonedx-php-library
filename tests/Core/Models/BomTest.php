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
use CycloneDX\Core\Models\Bom;
use CycloneDX\Core\Models\Metadata;
use DomainException;
use PHPUnit\Framework\TestCase;

/**
 * Class BomTest.
 *
 * @covers \CycloneDX\Core\Models\Bom
 *
 * @uses \CycloneDX\Core\Collections\ComponentRepository
 * @uses \CycloneDX\Core\Models\Metadata
 * @uses \CycloneDX\Core\Collections\ToolRepository
 * @uses \CycloneDX\Core\Collections\ExternalReferenceRepository
 * @uses \CycloneDX\Core\Collections\PropertyRepository
 */
class BomTest extends TestCase
{
    public function testConstruct(): Bom
    {
        $components = $this->createStub(ComponentRepository::class);

        $bom = new Bom($components);

        self::assertNull($bom->getSerialNumber());
        self::assertSame(1, $bom->getVersion());
        self::assertSame($components, $bom->getComponents());
        self::assertCount(0, $bom->getExternalReferences());
        self::assertEquals(new Metadata(), $bom->getMetadata());

        return $bom;
    }

    // region serialNumber setter&getter

    /**
     * @depends testConstruct
     */
    public function testSerialNumber(Bom $bom): Bom
    {
        $serialNumber = 'urn:uuid:3e671687-395b-41f5-a30f-a58921a69b79';
        $setOn = $bom->setSerialNumber($serialNumber);

        self::assertSame($bom, $setOn);
        self::assertSame($serialNumber, $bom->getSerialNumber());

        return $bom;
    }

    /**
     * @depends testSerialNumber
     */
    public function testSerialNumberEmptyString(Bom $bom): void
    {
        $setOn = $bom->setSerialNumber('');

        self::assertSame($bom, $setOn);
        self::assertNull($bom->getSerialNumber());
    }

    /**
     * @depends testSerialNumber
     */
    public function testSerialNumberEmptyStringInvalidValue(Bom $bom): void
    {
        $serialNumber = uniqid('invalid-value', true);
        $this->expectException(DomainException::class);
        $bom->setSerialNumber($serialNumber);
    }

    // endregion serialNumber setter&getter

    // region components setter&getter&modifiers

    /**
     * @depends testConstruct
     */
    public function testComponentsSetterGetter(Bom $bom): void
    {
        $components = $this->createStub(ComponentRepository::class);
        $actual = $bom->setComponents($components);
        self::assertSame($bom, $actual);
        self::assertSame($components, $bom->getComponents());
    }

    // endregion components setter&getter&modifiers

    // region version setter&getter

    /**
     * @depends testConstruct
     */
    public function testVersionSetterGetter(Bom $bom): void
    {
        $version = random_int(1, 255);
        $actual = $bom->setVersion($version);
        self::assertSame($bom, $actual);
        self::assertSame($version, $bom->getVersion());
    }

    /**
     * @depends testConstruct
     */
    public function testVersionSetterInvalidValue(Bom $bom): void
    {
        $version = 0 - random_int(1, 255);
        $this->expectException(DomainException::class);
        $bom->setVersion($version);
    }

    // endregion version setter&getter

    // region metadata setter&getter

    /**
     * @depends testConstruct
     */
    public function testMetadataSetterGetter(Bom $bom): void
    {
        $metadata = $this->createStub(Metadata::class);
        $actual = $bom->setMetadata($metadata);
        self::assertSame($bom, $actual);
        self::assertSame($metadata, $bom->getMetadata());
    }

    // endregion metadata setter&getter

    // region externalReferenceRepository setter&getter

    /**
     * @depends testConstruct
     */
    public function testExternalReferenceRepositorySetterGetter(Bom $bom): void
    {
        $extRefRepo = $this->createStub(ExternalReferenceRepository::class);
        $actual = $bom->setExternalReferences($extRefRepo);
        self::assertSame($bom, $actual);
        self::assertSame($extRefRepo, $bom->getExternalReferences());
    }

    // endregion externalReferenceRepository setter&getter
}
