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
 * Copyright (c) Steve Springett. All Rights Reserved.
 */

namespace CycloneDX\Tests\Core\Models;

use CycloneDX\Core\Models\Bom;
use CycloneDX\Core\Models\MetaData;
use CycloneDX\Core\Repositories\ComponentRepository;
use CycloneDX\Core\Repositories\ExternalReferenceRepository;
use PHPUnit\Framework\TestCase;

/**
 * Class BomTest.
 *
 * @covers \CycloneDX\Core\Models\Bom
 */
class BomTest extends TestCase
{
    /** @psalm-var Bom */
    private $bom;

    protected function setUp(): void
    {
        $this->bom = new Bom($this->createStub(ComponentRepository::class));
    }

    // region components setter&getter&modifiers

    public function testComponentsSetterGetter(): void
    {
        $components = $this->createStub(ComponentRepository::class);
        $bom = $this->bom->setComponentRepository($components);
        self::assertSame($this->bom, $bom);
        self::assertSame($components, $this->bom->getComponentRepository());
    }

    // endregion components setter&getter&modifiers

    // region version setter&getter

    public function testVersionSetterGetter(): void
    {
        $version = random_int(1, 255);
        $bom = $this->bom->setVersion($version);
        self::assertSame($this->bom, $bom);
        self::assertSame($version, $this->bom->getVersion());
    }

    public function testVersionSetterInvalidValue(): void
    {
        $version = 0 - random_int(1, 255);
        $this->expectException(\DomainException::class);
        $this->bom->setVersion($version);
    }

    // endregion version setter&getter

    // region metaData setter&getter

    public function testMetaDataSetterGetter(): void
    {
        $metaData = $this->createStub(MetaData::class);
        $bom = $this->bom->setMetaData($metaData);
        self::assertSame($this->bom, $bom);
        self::assertSame($metaData, $this->bom->getMetaData());
    }

    // endregion metaData setter&getter

    // region externalReferenceRepository setter&getter

    public function testExternalReferenceRepositorySetterGetter(): void
    {
        $extRefRepo = $this->createStub(ExternalReferenceRepository::class);
        $bom = $this->bom->setExternalReferenceRepository($extRefRepo);
        self::assertSame($this->bom, $bom);
        self::assertSame($extRefRepo, $this->bom->getExternalReferenceRepository());
    }

    // endregion externalReferenceRepository setter&getter
}
