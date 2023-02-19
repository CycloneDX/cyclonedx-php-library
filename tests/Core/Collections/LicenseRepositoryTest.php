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

namespace CycloneDX\Tests\Core\Collections;

use CycloneDX\Core\Collections\LicenseRepository;
use CycloneDX\Core\Models\License\NamedLicense;
use CycloneDX\Core\Models\License\SpdxLicense;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LicenseRepository::class)]
class LicenseRepositoryTest extends TestCase
{
    public function testEmptyConstructor(): void
    {
        $repo = new LicenseRepository();

        self::assertCount(0, $repo);
        self::assertSame([], $repo->getItems());
    }

    public function testNonEmptyConstruct(): void
    {
        $license1 = $this->createStub(SpdxLicense::class);
        $license2 = $this->createStub(NamedLicense::class);

        $repo = new LicenseRepository($license1, $license2, $license1, $license2);

        self::assertCount(2, $repo);
        self::assertCount(2, $repo->getItems());
        self::assertContains($license1, $repo->getItems());
        self::assertContains($license2, $repo->getItems());
    }

    public function testAddAndGetItems(): void
    {
        $license1 = $this->createStub(NamedLicense::class);
        $license2 = $this->createStub(SpdxLicense::class);
        $license3 = $this->createStub(NamedLicense::class);
        $repo = new LicenseRepository($license1, $license2);

        $actual = $repo->addItems($license2, $license3, $license3);

        self::assertSame($repo, $actual);
        self::assertCount(3, $repo);
        self::assertCount(3, $repo->getItems());
        self::assertContains($license1, $repo->getItems());
        self::assertContains($license2, $repo->getItems());
        self::assertContains($license3, $repo->getItems());
    }
}
