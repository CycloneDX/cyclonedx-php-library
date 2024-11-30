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

use CycloneDX\Core\Collections\BomRefRepository;
use CycloneDX\Core\Models\BomRef;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(BomRefRepository::class)]
class BomRefRepositoryTest extends TestCase
{
    public function testEmptyConstructor(): void
    {
        $repo = new BomRefRepository();

        self::assertSame([], $repo->getItems());
        self::assertCount(0, $repo);
    }

    /**
     * @param BomRef[] $bomRefs
     * @param BomRef[] $expectedContains
     */
    #[DataProvider('dpNonEmptyConstructor')]
    public function testNonEmptyConstructor(array $bomRefs, array $expectedContains): void
    {
        $repo = new BomRefRepository(...$bomRefs);

        self::assertSameSize($expectedContains, $repo);
        self::assertSameSize($expectedContains, $repo->getItems());
        foreach ($expectedContains as $expectedContain) {
            self::assertContains($expectedContain, $repo->getItems());
        }
    }

    public static function dpNonEmptyConstructor(): \Generator
    {
        $r1 = new BomRef();
        $r2 = new BomRef();
        $r3 = new BomRef('foo');
        $r4 = new BomRef('foo');
        $r5 = new BomRef('bar');

        yield 'identical' => [
            [$r1, $r1],
            [$r1],
        ];

        yield 'different' => [
            [$r1, $r2, $r3, $r4, $r5, $r1, $r2, $r3, $r4, $r5],
            [$r1, $r2, $r3, $r4, $r5],
        ];
    }

    /**
     * @param BomRef[] $initial
     * @param BomRef[] $add
     * @param BomRef[] $expectedContains
     */
    #[DataProvider('dpAddBomRef')]
    public function testAddAndGetItems(array $initial, array $add, array $expectedContains): void
    {
        $repo = new BomRefRepository(...$initial);

        $actual = $repo->addItems(...$add);

        self::assertSame($actual, $repo);
        self::assertSameSize($expectedContains, $repo);
        self::assertSameSize($expectedContains, $repo->getItems());
        foreach ($expectedContains as $expectedContain) {
            self::assertContains($expectedContain, $repo->getItems());
        }
    }

    public static function dpAddBomRef(): \Generator
    {
        $r1 = new BomRef();
        $r2 = new BomRef();
        $r3 = new BomRef('foo');
        $r4 = new BomRef('foo');
        $r5 = new BomRef('bar');

        yield 'identical' => [
            [$r1],
            [$r1],
            [$r1],
        ];

        yield 'different' => [
            [$r1, $r2],
            [$r2, $r3, $r4, $r5],
            [$r1, $r2, $r3, $r4, $r5],
        ];
    }
}
