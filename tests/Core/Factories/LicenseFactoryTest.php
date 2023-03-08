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

namespace CycloneDX\Tests\Core\Factories;

use Composer\Spdx\SpdxLicenses;
use CycloneDX\Core\Factories\LicenseFactory;
use CycloneDX\Core\Models\License\LicenseExpression;
use CycloneDX\Core\Models\License\NamedLicense;
use CycloneDX\Core\Models\License\SpdxLicense;
use CycloneDX\Core\Spdx\LicenseIdentifiers;
use DomainException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LicenseFactory::class)]
#[UsesClass(NamedLicense::class)]
#[UsesClass(SpdxLicense::class)]
#[UsesClass(LicenseExpression::class)]
class LicenseFactoryTest extends TestCase
{
    private LicenseIdentifiers&\PHPUnit\Framework\MockObject\MockObject $licenseIdentifiers;
    private SpdxLicenses&\PHPUnit\Framework\MockObject\MockObject $spdxLicenses;
    private LicenseFactory $factory;

    protected function setUp(): void
    {
        $this->licenseIdentifiers = $this->createMock(LicenseIdentifiers::class);
        $this->spdxLicenses = $this->createMock(SpdxLicenses::class);

        $this->factory = new LicenseFactory(
            $this->licenseIdentifiers,
            $this->spdxLicenses,
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->licenseIdentifiers,
            $this->spdxLicenses,
            $this->factory
        );
    }

    public function testMakeNamedLicense(): void
    {
        $license = uniqid('license', true);
        $actual = $this->factory->makeNamedLicense($license);
        self::assertEquals(new NamedLicense($license), $actual);
    }

    // region makeSpdxLicense

    public function testMakeSpdxLicenseExact(): void
    {
        $license = uniqid('license', true);
        $this->licenseIdentifiers->method('fixLicense')
            ->with($license)->willReturn($license);
        $this->licenseIdentifiers->method('isKnownLicense')
            ->with($license)->willReturn(true);
        $actual = $this->factory->makeSpdxLicense($license);
        self::assertEquals(new SpdxLicense($license), $actual);
    }

    public function testMakeSpdxLicenseFixed(): void
    {
        $license = uniqid('initial', true);
        $fixed = uniqid('fixed', true);
        $this->licenseIdentifiers->method('fixLicense')
            ->with($license)->willReturn($fixed);
        $this->licenseIdentifiers->method('isKnownLicense')
            ->willReturnMap([
               [$license, false],
               [$fixed, true],
            ]);
        $actual = $this->factory->makeSpdxLicense($license);
        self::assertEquals(new SpdxLicense($fixed), $actual);
    }

    public function testMakeSpdxLicenseUnknownThrows(): void
    {
        $license = uniqid('initial', true);
        $this->licenseIdentifiers->method('fixLicense')
            ->with($license)->willReturn(null);
        $this->licenseIdentifiers->method('isKnownLicense')
            ->with($license)->willReturn(false);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/unknown SPDX license/i');

        $this->factory->makeSpdxLicense($license);
    }

    // endregion makeSpdxLicense

    // region makeExpression

    public function testMakeExpression(): void
    {
        $expression = uniqid('expression', true);
        $this->spdxLicenses->method('validate')
            ->with($expression)->willReturn(true);
        $actual = $this->factory->makeExpression($expression);
        self::assertEquals(new LicenseExpression($expression), $actual);
    }

    public function testMakeExpressionInvalidValueThrows(): void
    {
        $expression = uniqid('expression', true);
        $this->spdxLicenses->method('validate')
            ->with($expression)->willReturn(false);
        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/invalid SPDX license expressions/i');
        $this->factory->makeExpression($expression);
    }

    public function testMakeExpressionInvalidArgumentThrows(): void
    {
        $expression = uniqid('expression', true);
        $this->spdxLicenses->method('validate')
            ->with($expression)->willThrowException(new \InvalidArgumentException());
        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/invalid SPDX license expressions/i');
        $this->factory->makeExpression($expression);
    }

    // endregion makeExpression

    // region makeDisjunctive

    public function testMakeDisjunctiveSpdxLicense(): void
    {
        $license = uniqid('license', true);
        $expected = $this->createStub(SpdxLicense::class);
        $factory = $this->createPartialMock(LicenseFactory::class, ['makeSpdxLicense']);
        $factory->method('makeSpdxLicense')
            ->with($license)->willReturn($expected);
        $actual = $factory->makeDisjunctive($license);
        self::assertSame($expected, $actual);
    }

    public function testMakeDisjunctiveNamedLicense(): void
    {
        $license = uniqid('license', true);
        $expected = $this->createStub(NamedLicense::class);
        $factory = $this->createPartialMock(LicenseFactory::class, ['makeSpdxLicense', 'makeNamedLicense']);
        $factory->method('makeSpdxLicense')
            ->with($license)->willThrowException(new DomainException());
        $factory->method('makeNamedLicense')
            ->with($license)->willReturn($expected);
        $actual = $factory->makeDisjunctive($license);
        self::assertSame($expected, $actual);
    }

    // endregion makeDisjunctive

    // region makeFromString

    public function testMakeFromStringSpdxLicense(): void
    {
        $license = uniqid('license', true);
        $expected = $this->createStub(SpdxLicense::class);
        $factory = $this->createPartialMock(LicenseFactory::class, ['makeSpdxLicense']);
        $factory->method('makeSpdxLicense')
            ->with($license)->willReturn($expected);
        $actual = $factory->makeFromString($license);
        self::assertSame($expected, $actual);
    }

    public function testMakeFromStringLicenseExpression(): void
    {
        $license = uniqid('license', true);
        $expected = $this->createStub(LicenseExpression::class);
        $factory = $this->createPartialMock(LicenseFactory::class, ['makeSpdxLicense', 'makeExpression']);
        $factory->method('makeSpdxLicense')
            ->with($license)->willThrowException(new DomainException());
        $factory->method('makeExpression')
            ->with($license)->willReturn($expected);
        $actual = $factory->makeFromString($license);
        self::assertSame($expected, $actual);
    }

    public function testMakeFromStringNamedLicense(): void
    {
        $license = uniqid('license', true);
        $expected = $this->createStub(NamedLicense::class);
        $factory = $this->createPartialMock(LicenseFactory::class, ['makeSpdxLicense', 'makeExpression', 'makeNamedLicense']);
        $factory->method('makeSpdxLicense')
            ->with($license)->willThrowException(new DomainException());
        $factory->method('makeExpression')
            ->with($license)->willThrowException(new DomainException());
        $factory->method('makeNamedLicense')
            ->with($license)->willReturn($expected);
        $actual = $factory->makeFromString($license);
        self::assertSame($expected, $actual);
    }

    // endregion makeFromString
}
