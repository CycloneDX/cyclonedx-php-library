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

namespace CycloneDX\Tests\Core\Serialization;

use CycloneDX\Core\Models\BomRef;
use CycloneDX\Core\Serialization\BomRefDiscriminator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BomRefDiscriminator::class)]
#[UsesClass(BomRef::class)]
class BomRefDiscriminatorTest extends TestCase
{
    public function testDiscriminate(): void
    {
        $bomRefNullA = new BomRef(null);
        $bomRefNullB = new BomRef(null);
        $bomRefFooA = new BomRef('Foo');
        $bomRefFooB = new BomRef('Foo');
        $bomRefBar = new BomRef('Bar');
        $discriminator = new BomRefDiscriminator(
            $bomRefNullA, $bomRefNullB,
            $bomRefFooA, $bomRefFooB,
            $bomRefBar, $bomRefBar // this one twice, for testing sake
        );

        $discriminator->discriminate();

        self::assertNotNull($bomRefNullA->getValue());
        self::assertNotNull($bomRefNullB->getValue());
        self::assertNotNull($bomRefFooA->getValue());
        self::assertNotNull($bomRefFooB->getValue());
        self::assertNotNull($bomRefBar->getValue());
        self::assertNotSame($bomRefFooA->getValue(), $bomRefFooB->getValue());
        self::assertSame('Bar', $bomRefBar->getValue());
        self::assertCount(5, array_unique([
            $bomRefNullA->getValue(),
            $bomRefNullB->getValue(),
            $bomRefFooA->getValue(),
            $bomRefFooB->getValue(),
            $bomRefBar->getValue(),
        ]));
    }

    public function testReset(): void
    {
        $bomRefNullA = new BomRef(null);
        $bomRefNullB = new BomRef(null);
        $bomRefFooA = new BomRef('Foo');
        $bomRefFooB = new BomRef('Foo');
        $bomRefBar = new BomRef('Bar');
        $discriminator = new BomRefDiscriminator(
            $bomRefNullA, $bomRefNullB,
            $bomRefFooA, $bomRefFooB,
            $bomRefBar
        );

        $bomRefNullA->setValue('Baz');
        $bomRefNullB->setValue('Baz');
        $bomRefFooA->setValue('Baz');
        $bomRefFooB->setValue('Baz');
        $bomRefBar->setValue('Baz');

        self::assertSame('Baz', $bomRefNullA->getValue());
        self::assertSame('Baz', $bomRefNullB->getValue());
        self::assertSame('Baz', $bomRefFooA->getValue());
        self::assertSame('Baz', $bomRefFooB->getValue());
        self::assertSame('Baz', $bomRefBar->getValue());

        $discriminator->reset();

        self::assertNull($bomRefNullA->getValue());
        self::assertNull($bomRefNullB->getValue());
        self::assertSame('Foo', $bomRefFooA->getValue());
        self::assertSame('Foo', $bomRefFooB->getValue());
        self::assertSame('Bar', $bomRefBar->getValue());
    }
}
