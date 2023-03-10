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

use CycloneDX\Core\Collections\PropertyRepository;
use CycloneDX\Core\Models\Property;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PropertyRepository::class)]
class PropertyRepositoryTest extends TestCase
{
    public function testEmptyConstructor(): void
    {
        $repo = new PropertyRepository();

        self::assertCount(0, $repo);
        self::assertSame([], $repo->getItems());
    }

    public function testConstructAndGet(): void
    {
        $property1 = $this->createMock(Property::class);
        $property2 = $this->createMock(Property::class);

        $repo = new PropertyRepository($property1, $property2, $property1, $property2);

        self::assertCount(2, $repo);
        self::assertCount(2, $repo->getItems());
        self::assertContains($property1, $repo->getItems());
        self::assertContains($property2, $repo->getItems());
    }

    public function testAddAndGetItems(): void
    {
        $property1 = $this->createMock(Property::class);
        $property2 = $this->createMock(Property::class);
        $property3 = $this->createMock(Property::class);
        $repo = new PropertyRepository($property1, $property2);

        $actual = $repo->addItems($property2, $property3, $property3);

        self::assertSame($repo, $actual);
        self::assertCount(3, $repo);
        self::assertCount(3, $repo->getItems());
        self::assertContains($property1, $repo->getItems());
        self::assertContains($property2, $repo->getItems());
        self::assertContains($property3, $repo->getItems());
    }
}
