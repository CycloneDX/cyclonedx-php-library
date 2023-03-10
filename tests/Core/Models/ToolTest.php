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

use CycloneDX\Core\Collections\ExternalReferenceRepository;
use CycloneDX\Core\Collections\HashDictionary;
use CycloneDX\Core\Models\Tool;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DependsUsingShallowClone;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Tool::class)]
#[UsesClass(HashDictionary::class)]
#[UsesClass(ExternalReferenceRepository::class)]
class ToolTest extends TestCase
{
    public function testConstruct(): Tool
    {
        $tool = new Tool();

        self::assertNull($tool->getVendor());
        self::assertNull($tool->getName());
        self::assertNull($tool->getVersion());
        self::assertCount(0, $tool->getHashes());
        self::assertCount(0, $tool->getExternalReferences());

        return $tool;
    }

    #[DependsUsingShallowClone('testConstruct')]
    public function testSetterGetterVersion(Tool $tool): void
    {
        $version = 'v1.2.3';
        self::assertNotSame($version, $tool->getVersion());
        $actual = $tool->setVersion($version);
        self::assertSame($actual, $tool);
        self::assertSame($version, $tool->getVersion());
    }

    #[DependsUsingShallowClone('testConstruct')]
    public function testSetterGetterVendor(Tool $tool): void
    {
        $vendor = 'myVendor';
        self::assertNotSame($vendor, $tool->getVendor());
        $actual = $tool->setVendor($vendor);
        self::assertSame($actual, $tool);
        self::assertSame($vendor, $tool->getVendor());
    }

    #[DependsUsingShallowClone('testConstruct')]
    public function testSetterGetterName(Tool $tool): void
    {
        $name = 'myName';
        self::assertNotSame($name, $tool->getName());
        $actual = $tool->setName($name);
        self::assertSame($actual, $tool);
        self::assertSame($name, $tool->getName());
    }

    #[DependsUsingShallowClone('testConstruct')]
    public function testSetterGetterHashDictionary(Tool $tool): void
    {
        $hashes = $this->createMock(HashDictionary::class);
        self::assertNotSame($hashes, $tool->getHashes());
        $actual = $tool->setHashes($hashes);
        self::assertSame($actual, $tool);
        self::assertSame($hashes, $tool->getHashes());
    }

    #[DependsUsingShallowClone('testConstruct')]
    public function testSetterGetterExternalReferenceRepository(Tool $tool): void
    {
        $extRefs = $this->createMock(ExternalReferenceRepository::class);
        self::assertNotSame($extRefs, $tool->getExternalReferences());
        $actual = $tool->setExternalReferences($extRefs);
        self::assertSame($actual, $tool);
        self::assertSame($extRefs, $tool->getExternalReferences());
    }
}
