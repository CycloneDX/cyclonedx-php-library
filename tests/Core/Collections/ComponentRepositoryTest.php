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

use CycloneDX\Core\Collections\ComponentRepository;
use CycloneDX\Core\Enums\ComponentType;
use CycloneDX\Core\Models\Component;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ComponentRepository::class)]
#[UsesClass(Component::class)]
class ComponentRepositoryTest extends TestCase
{
    public function testEmptyConstructor(): void
    {
        $repo = new ComponentRepository();

        self::assertSame([], $repo->getItems());
        self::assertCount(0, $repo);
    }

    public function testConstructor(): void
    {
        $component1 = $this->createStub(Component::class);
        $component2 = $this->createStub(Component::class);

        $repo = new ComponentRepository($component1, $component2, $component1, $component2);

        self::assertCount(2, $repo);
        self::assertCount(2, $repo->getItems());
        self::assertContains($component1, $repo->getItems());
        self::assertContains($component2, $repo->getItems());
    }

    public function testAddAndGetItems(): void
    {
        $component1 = $this->createStub(Component::class);
        $component2 = $this->createStub(Component::class);
        $component3 = $this->createStub(Component::class);
        $repo = new ComponentRepository($component1, $component3);

        $actual = $repo->addItems($component2, $component3, $component2);

        self::assertSame($repo, $actual);
        self::assertCount(3, $actual);
        self::assertCount(3, $repo->getItems());
        self::assertContains($component1, $repo->getItems());
        self::assertContains($component2, $repo->getItems());
        self::assertContains($component3, $repo->getItems());
    }

    /**
     * @param Component[] $expectedFindings
     */
    #[DataProvider('dpFindComponents')]
    public function testFindItem(
        ComponentRepository $repo,
        string $findName,
        ?string $findGroup,
        array $expectedFindings,
    ): void {
        $actual = $repo->findItem($findName, $findGroup);

        self::assertSameSize($expectedFindings, $actual);
        foreach ($expectedFindings as $expectedFinding) {
            self::assertContains($expectedFinding, $actual);
        }
    }

    public function dpFindComponents(): Generator
    {
        yield 'nothing in empty' => [
            new ComponentRepository(),
            'foo',
            'bar',
            [],
        ];

        $component1 = new Component(ComponentType::Library, 'foo');
        $component2 = (new Component(ComponentType::Library, 'foo'))->setGroup('bar');
        $component3 = (new Component(ComponentType::Library, 'foo'))->setGroup('bar');
        $components = new ComponentRepository($component1, $component2, $component3);
        yield 'single empty group' => [
            $components,
            'foo',
            '',
            [$component1],
        ];

        yield 'single no group' => [
            $components,
            'foo',
            null,
            [$component1],
        ];

        yield 'multiple' => [
            $components,
            'foo',
            'bar',
            [$component2, $component3],
        ];
    }
}
