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

use CycloneDX\Core\Models\ExternalReference;
use CycloneDX\Core\Collections\ExternalReferenceRepository;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Collections\ExternalReferenceRepository
 */
class ExternalReferenceRepositoryTest extends TestCase
{
    public function testEmptyConstructor(): void
    {
        $repo = new ExternalReferenceRepository();

        self::assertCount(0, $repo);
        self::assertSame([], $repo->getItems());
    }

    public function testConstructAndGet(): void
    {
        $externalReference1 = $this->createStub(ExternalReference::class);
        $externalReference2 = $this->createStub(ExternalReference::class);

        $repo = new ExternalReferenceRepository(
            $externalReference1,
            $externalReference2,
            $externalReference1,
            $externalReference2
        );

        self::assertCount(2, $repo);
        self::assertCount(2, $repo->getItems());
        self::assertContains($externalReference1, $repo->getItems());
        self::assertContains($externalReference2, $repo->getItems());
    }

    public function testAddAndGetExternalReference(): void
    {
        $externalReference1 = $this->createStub(ExternalReference::class);
        $externalReference2 = $this->createStub(ExternalReference::class);
        $externalReference3 = $this->createStub(ExternalReference::class);
        $repo = new ExternalReferenceRepository($externalReference1, $externalReference2);

        $actual = $repo->addItems($externalReference2, $externalReference3, $externalReference3);

        self::assertSame($repo, $actual);
        self::assertCount(3, $repo);
        self::assertCount(3, $repo->getItems());
        self::assertContains($externalReference1, $repo->getItems());
        self::assertContains($externalReference2, $repo->getItems());
        self::assertContains($externalReference3, $repo->getItems());
    }
}
