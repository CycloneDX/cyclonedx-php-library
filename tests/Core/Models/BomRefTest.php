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

use CycloneDX\Core\Models\BomRef;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DependsUsingShallowClone;
use PHPUnit\Framework\TestCase;

#[CoversClass(BomRef::class)]
class BomRefTest extends TestCase
{
    public function testConstructValueDefaultsToNull(): BomRef
    {
        $bomRef = new BomRef();

        self::assertNull($bomRef->getValue());

        return $bomRef;
    }

    public function testConstructSetsValue(): BomRef
    {
        $bomRef = new BomRef('foobar');

        self::assertSame('foobar', $bomRef->getValue());

        return $bomRef;
    }

    #[DependsUsingShallowClone('testConstructSetsValue')]
    public function testSetValueNull(BomRef $bomRef): void
    {
        $bomRef->setValue(null);
        self::assertNull($bomRef->getValue());
    }

    #[DependsUsingShallowClone('testConstructSetsValue')]
    public function testSetValueEmptyIsNull(BomRef $bomRef): void
    {
        $bomRef->setValue('');
        self::assertNull($bomRef->getValue());
    }

    #[DependsUsingShallowClone('testConstructValueDefaultsToNull')]
    public function testSetValue(BomRef $bomRef): void
    {
        $bomRef->setValue('asdewqe');
        self::assertSame('asdewqe', $bomRef->getValue());
    }
}
