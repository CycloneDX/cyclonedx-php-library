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

use CycloneDX\Core\Collections\PropertyRepository;
use CycloneDX\Core\Collections\ToolRepository;
use CycloneDX\Core\Models\Component;
use CycloneDX\Core\Models\Metadata;
use DateTime;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\CycloneDX\Core\Models\Metadata::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(\CycloneDX\Core\Collections\ToolRepository::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(\CycloneDX\Core\Collections\PropertyRepository::class)]
class MetadataTest extends TestCase
{
    public function testConstructor(): Metadata
    {
        $metadata = new Metadata();

        self::assertNull($metadata->getTimestamp());
        self::assertCount(0, $metadata->getTools());
        self::assertNull($metadata->getComponent());
        self::assertCount(0, $metadata->getProperties());

        return $metadata;
    }

    #[\PHPUnit\Framework\Attributes\DependsUsingShallowClone('testConstructor')]
    public function testGetterSetterTimestamp(Metadata $metadata): void
    {
        $timestamp = $this->createStub(DateTime::class);
        self::assertNotSame($timestamp, $metadata->getTimestamp());
        $actual = $metadata->setTimestamp($timestamp);
        self::assertSame($actual, $metadata);
        self::assertSame($timestamp, $metadata->getTimestamp());
    }

    #[\PHPUnit\Framework\Attributes\DependsUsingShallowClone('testConstructor')]
    public function testGetterSetterTools(Metadata $metadata): void
    {
        $tools = $this->createStub(ToolRepository::class);
        $actual = $metadata->setTools($tools);
        self::assertSame($actual, $metadata);
        self::assertSame($tools, $metadata->getTools());
    }

    #[\PHPUnit\Framework\Attributes\DependsUsingShallowClone('testConstructor')]
    public function testGetterSetterComponent(Metadata $metadata): void
    {
        $component = $this->createStub(Component::class);
        self::assertNotSame($component, $metadata->getComponent());
        $actual = $metadata->setComponent($component);
        self::assertSame($actual, $metadata);
        self::assertSame($component, $metadata->getComponent());
    }

    #[\PHPUnit\Framework\Attributes\DependsUsingShallowClone('testConstructor')]
    public function testGetterSetterProperties(Metadata $metadata): void
    {
        $properties = $this->createStub(PropertyRepository::class);
        self::assertNotSame($properties, $metadata->getProperties());
        $actual = $metadata->setProperties($properties);
        self::assertSame($actual, $metadata);
        self::assertSame($properties, $metadata->getProperties());
    }
}
