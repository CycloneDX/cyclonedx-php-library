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

namespace CycloneDX\Tests\Core\Models\License;

use CycloneDX\Core\Enums\LicenseAcknowledgement;
use CycloneDX\Core\Models\License\LicenseExpression;
use DomainException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DependsUsingShallowClone;
use PHPUnit\Framework\TestCase;

#[CoversClass(LicenseExpression::class)]
class LicenseExpressionTest extends TestCase
{
    public function testConstructor(): LicenseExpression
    {
        $expression = uniqid('expression', true);
        $license = new LicenseExpression($expression);
        self::assertSame($expression, $license->getExpression());
        self::assertNull($license->getAcknowledgement());

        return $license;
    }

    public function testConstructThrowsOnEmptyExpression(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/expression must not be empty/i');

        new LicenseExpression('');
    }

    #[DependsUsingShallowClone('testConstructor')]
    public function testSetAndGetExpression(LicenseExpression $license): void
    {
        $expression = uniqid('expression', true);

        $got = $license->setExpression($expression);

        self::assertSame($license, $got);
        self::assertSame($expression, $license->getExpression());
    }

    #[DependsUsingShallowClone('testConstructor')]
    public function testSetThrowsOnEmptyExpression(LicenseExpression $license): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/expression must not be empty/i');

        $license->setExpression('');
    }

    #[DependsUsingShallowClone('testConstructor')]
    public function testSetAndGetAcknowledgment(LicenseExpression $license): void
    {
        $acknowledgment = LicenseAcknowledgement::Declared;

        $got = $license->setAcknowledgement($acknowledgment);

        self::assertSame($license, $got);
        self::assertSame($acknowledgment, $license->getAcknowledgement());
    }
}
