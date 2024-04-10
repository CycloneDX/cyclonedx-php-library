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

namespace CycloneDX\Tests\Core\Spec;

use CycloneDX\Core\Spdx\LicenseIdentifiers;
use CycloneDX\Core\Spec\_Spec;
use CycloneDX\Core\Spec\SpecFactory;
use CycloneDX\Core\Spec\Version;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SpecFactory::class)]
#[UsesClass(_Spec::class)]
#[UsesClass(LicenseIdentifiers::class)]
class SpecFactoryTest extends TestCase
{
    public function test11(): void
    {
        $spec = SpecFactory::make1dot1();
        self::assertSame(Version::v1dot1, $spec->getVersion());
        self::assertEquals($spec, SpecFactory::makeForVersion(Version::v1dot1));
    }

    public function test12(): void
    {
        $spec = SpecFactory::make1dot2();
        self::assertSame(Version::v1dot2, $spec->getVersion());
        self::assertEquals($spec, SpecFactory::makeForVersion(Version::v1dot2));
    }

    public function test13(): void
    {
        $spec = SpecFactory::make1dot3();
        self::assertSame(Version::v1dot3, $spec->getVersion());
        self::assertEquals($spec, SpecFactory::makeForVersion(Version::v1dot3));
    }

    public function test14(): void
    {
        $spec = SpecFactory::make1dot4();
        self::assertSame(Version::v1dot4, $spec->getVersion());
        self::assertEquals($spec, SpecFactory::makeForVersion(Version::v1dot4));
    }

    public function test15(): void
    {
        $spec = SpecFactory::make1dot5();
        self::assertSame(Version::v1dot5, $spec->getVersion());
        self::assertEquals($spec, SpecFactory::makeForVersion(Version::v1dot5));
    }

    public function test16(): void
    {
        $spec = SpecFactory::make1dot6();
        self::assertSame(Version::v1dot6, $spec->getVersion());
        self::assertEquals($spec, SpecFactory::makeForVersion(Version::v1dot6));
    }
}
