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

use CycloneDX\Core\Collections\ToolRepository;
use CycloneDX\Core\Models\Component;
use CycloneDX\Core\Models\Metadata;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Models\Metadata
 */
class MetaDataTest extends TestCase
{
    public function testConstructor(): Metadata
    {
        $metaData = new Metadata();

        self::assertCount(0, $metaData->getTools());
        self::assertNull($metaData->getComponent());

        return $metaData;
    }

    /**
     * @depends testConstructor
     */
    public function testGetterSetterTools(Metadata $metaData): void
    {
        $tools = $this->createStub(ToolRepository::class);
        $metaData->setTools($tools);
        self::assertSame($tools, $metaData->getTools());
    }

    /**
     * @depends testConstructor
     */
    public function testSetterSetterComponent(Metadata $metaData): void
    {
        $component = $this->createStub(Component::class);
        $metaData->setComponent($component);
        self::assertSame($component, $metaData->getComponent());
    }
}
