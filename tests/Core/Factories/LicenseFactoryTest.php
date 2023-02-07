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

use CycloneDX\Core\Factories\LicenseFactory;
use CycloneDX\Core\Models\License\LicenseExpression;
use CycloneDX\Core\Models\License\NamedLicense;
use CycloneDX\Core\Models\License\SpdxLicense;
use CycloneDX\Core\Spdx\LicenseValidator as SpdxLicenseValidator;
use DomainException;
use Exception;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

#[\PHPUnit\Framework\Attributes\CoversClass(\CycloneDX\Core\Factories\LicenseFactory::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(\CycloneDX\Core\Models\License\LicenseExpression::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(\CycloneDX\Core\Models\License\SpdxLicense::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(\CycloneDX\Core\Models\License\NamedLicense::class)]
class LicenseFactoryTest extends TestCase
{
    public function testConstructorWithValidator(): LicenseFactory
    {
        $spdxLicenseValidator = $this->createStub(SpdxLicenseValidator::class);

        $factory = new LicenseFactory($spdxLicenseValidator);
        self::assertSame($spdxLicenseValidator, $factory->getSpdxLicenseValidator());

        return $factory;
    }

    public function testConstructor(): LicenseFactory
    {
        $factory = new LicenseFactory();

        $exception = null;
        try {
            $factory->getSpdxLicenseValidator();
        } catch (Exception $exception) {
            // continue to assertions
        }

        self::assertInstanceOf(UnexpectedValueException::class, $exception);
        self::assertMatchesRegularExpression('/missing SpdxLicenseValidator/i', $exception->getMessage());

        return $factory;
    }

    /**
     * @depends testConstructor
     */
    public function testSetSpdxLicenseValidator(LicenseFactory $factory): void
    {
        $spdxLicenseValidator = $this->createStub(SpdxLicenseValidator::class);
        $factory->setSpdxLicenseValidator($spdxLicenseValidator);
        self::assertSame($spdxLicenseValidator, $factory->getSpdxLicenseValidator());
    }

    public function testMakeFromStringIsExpression(): void
    {
        $factory = $this->createPartialMock(LicenseFactory::class, ['makeExpression', 'makeDisjunctive']);
        $license = $this->createStub(LicenseExpression::class);

        $factory->expects(self::once())->method('makeExpression')
            ->with('FooBar')
            ->willReturn($license);
        $factory->expects(self::never())->method('makeDisjunctive');

        $got = $factory->makeFromString('FooBar');

        self::assertSame($license, $got);
    }

    public function testMakeFromStringIsDisjunctive(): void
    {
        $factory = $this->createPartialMock(LicenseFactory::class, ['makeExpression', 'makeDisjunctive']);
        $license = $this->createStub(NamedLicense::class);

        $factory->expects(self::once())->method('makeExpression')
            ->with('FooBar')
            ->willThrowException(new DomainException());
        $factory->expects(self::once())->method('makeDisjunctive')
            ->with('FooBar')
            ->willReturn($license);

        $got = $factory->makeFromString('FooBar');

        self::assertSame($license, $got);
    }
    public function testMakeExpression(): void
    {
        $factory = new LicenseFactory();

        $got = $factory->makeExpression('(A or B)');

        self::assertInstanceOf(LicenseExpression::class, $got);
        self::assertEquals('(A or B)', $got->getExpression());
    }

    public function testMakeDisjunctiveIsId(): void
    {
        $factory = $this->createPartialMock(LicenseFactory::class, ['makeSpdxLicense', 'makeNamedLicense']);
        $license = $this->createStub(SpdxLicense::class);

        $factory->expects(self::once())->method('makeSpdxLicense')
            ->with('FooBar')
            ->willReturn($license);
        $factory->expects(self::never())->method('makeNamedLicense');

        $got = $factory->makeDisjunctive('FooBar');

        self::assertSame($license, $got);
    }

    public function testMakeDisjunctiveIsName(): void
    {
        $factory = $this->createPartialMock(LicenseFactory::class, ['makeSpdxLicense', 'makeNamedLicense']);
        $license = $this->createStub(NamedLicense::class);

        $factory->expects(self::once())->method('makeSpdxLicense')
            ->with('FooBar')
            ->willThrowException(new DomainException());
        $factory->expects(self::once())->method('makeNamedLicense')
            ->with('FooBar')
            ->willReturn($license);

        $actual = $factory->makeDisjunctive('FooBar');

        self::assertSame($license, $actual);
    }

    public function testMakeSpdxLicense(): void
    {
        $spdxLicenseValidator = $this->createMock(SpdxLicenseValidator::class);
        $spdxLicenseValidator->method('getLicenses')
            ->willReturn(['FooBar']);
        $spdxLicenseValidator->method('validate')
            ->with('foobar')
            ->willReturnMap([['FooBar', true], ['foobar', true]]);
        $spdxLicenseValidator->method('getLicense')
            ->with('foobar')
            ->willReturn('FooBar');
        $factory = new LicenseFactory($spdxLicenseValidator);

        $got = $factory->makeSpdxLicense('foobar');

        self::assertSame('FooBar', $got->getId());
        self::assertNull($got->getUrl());
    }

    public function testMakeNamedLicense(): void
    {
        $factory = new LicenseFactory();

        $got = $factory->makeNamedLicense('foo and friends (c) 2342');

        self::assertSame('foo and friends (c) 2342', $got->getName());
        self::assertNull($got->getUrl());
    }
}
