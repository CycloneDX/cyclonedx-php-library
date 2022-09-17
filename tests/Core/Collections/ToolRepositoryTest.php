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

use CycloneDX\Core\Models\Tool;
use CycloneDX\Core\Collections\ToolRepository;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Collections\ToolRepository
 */
class ToolRepositoryTest extends TestCase
{
    public function testEmptyConstructor(): void
    {
        $repo = new ToolRepository();

        self::assertCount(0, $repo);
        self::assertSame([], $repo->getItems());
    }

    public function testConstructAndGet(): void
    {
        $tool1 = $this->createStub(Tool::class);
        $tool2 = $this->createStub(Tool::class);

        $repo = new ToolRepository($tool1, $tool2, $tool1, $tool2);

        self::assertCount(2, $repo);
        self::assertCount(2, $repo->getItems());
        self::assertContains($tool1, $repo->getItems());
        self::assertContains($tool2, $repo->getItems());
    }

    public function testAddAndGetTool(): void
    {
        $tool1 = $this->createStub(Tool::class);
        $tool2 = $this->createStub(Tool::class);
        $tool3 = $this->createStub(Tool::class);
        $repo = new ToolRepository($tool1, $tool2);

        $actual = $repo->addItems($tool2, $tool3, $tool3);

        self::assertSame($repo, $actual);
        self::assertCount(3, $repo);
        self::assertCount(3, $repo->getItems());
        self::assertContains($tool1, $repo->getItems());
        self::assertContains($tool2, $repo->getItems());
        self::assertContains($tool3, $repo->getItems());
    }
}
