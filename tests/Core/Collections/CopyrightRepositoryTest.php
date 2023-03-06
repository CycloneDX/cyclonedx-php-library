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

use CycloneDX\Core\Collections\CopyrightRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CopyrightRepository::class)]
class CopyrightRepositoryTest extends TestCase
{
    public function testEmptyConstructor(): void
    {
        $repo = new CopyrightRepository();

        self::assertCount(0, $repo);
        self::assertSame([], $repo->getItems());
    }

    public function testConstructAndGet(): void
    {
        $copyright1 = 'foo';
        $copyright2 = 'bar';

        $repo = new CopyrightRepository($copyright1, $copyright2, '', $copyright1, $copyright2);

        self::assertCount(2, $repo);
        self::assertCount(2, $repo->getItems());
        self::assertContains($copyright1, $repo->getItems());
        self::assertContains($copyright2, $repo->getItems());
    }

    public function testAddAndGetItems(): void
    {
        $copyright1 = 'foo';
        $copyright2 = 'bar';
        $copyright3 = 'bazz';
        $repo = new CopyrightRepository($copyright1, $copyright2);

        $actual = $repo->addItems($copyright2, '', $copyright3, $copyright3);

        self::assertSame($repo, $actual);
        self::assertCount(3, $repo);
        self::assertCount(3, $repo->getItems());
        self::assertContains($copyright1, $repo->getItems());
        self::assertContains($copyright2, $repo->getItems());
        self::assertContains($copyright3, $repo->getItems());
    }
}
