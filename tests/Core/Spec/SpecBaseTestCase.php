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

namespace CycloneDX\Tests\Core\Spec;

use CycloneDX\Core\Enums\ComponentType;
use CycloneDX\Core\Enums\ExternalReferenceType;
use CycloneDX\Core\Enums\HashAlgorithm;
use CycloneDX\Core\Spec\Format;
use CycloneDX\Core\Spec\Spec;
use CycloneDX\Core\Spec\Version;
use CycloneDX\Tests\_data\BomSpecData;
use Generator;
use PHPUnit\Framework\TestCase;

abstract class SpecBaseTestCase extends TestCase
{
    abstract protected static function getSpec(): Spec;

    abstract protected static function getSpecVersion(): Version;

    final public function testVersionMatches(): void
    {
        $version = static::getSpec()->getVersion();
        self::assertSame(static::getSpecVersion(), $version);
    }

    abstract protected static function shouldSupportFormats(): array;

    final public function testKnownFormats(): array
    {
        $formats = static::shouldSupportFormats();

        self::assertIsArray($formats);
        self::assertNotEmpty($formats);

        return $formats;
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsSupportsFormat')]
    final public function testIsSupportedFormat(Format $format, bool $expected): void
    {
        $isSupported = static::getSpec()->isSupportedFormat($format);
        self::assertSame($expected, $isSupported);
    }

    final public static function dpIsSupportsFormat(): Generator
    {
        $should = static::shouldSupportFormats();
        foreach (Format::cases() as $format) {
            yield $format->name => [$format, \in_array($format, $should, true)];
        }
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsSupportedComponentType')]
    final public function testIsSupportedComponentType(ComponentType $value, bool $expected): void
    {
        $isSupported = static::getSpec()->isSupportedComponentType($value);
        self::assertSame($expected, $isSupported);
    }

    final public static function dpIsSupportedComponentType(): Generator
    {
        $known = BomSpecData::getClassificationEnumForVersion(static::getSpecVersion()->value);
        $values = ComponentType::cases();
        foreach ($values as $value) {
            yield $value->name => [$value, \in_array($value->value, $known, true)];
        }
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsSupportedHashAlgorithm')]
    final public function testIsSupportedHashAlgorithm(HashAlgorithm $value, bool $expected): void
    {
        $isSupported = static::getSpec()->isSupportedHashAlgorithm($value);
        self::assertSame($expected, $isSupported);
    }

    final public static function dpIsSupportedHashAlgorithm(): Generator
    {
        $known = BomSpecData::getHashAlgEnumForVersion(static::getSpecVersion()->value);
        $values = HashAlgorithm::cases();
        foreach ($values as $value) {
            yield $value->name => [$value, \in_array($value->value, $known, true)];
        }
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsSupportedHashContent')]
    final public function testIsSupportedHashContent(string $value, bool $expected): void
    {
        $isSupported = static::getSpec()->isSupportedHashContent($value);
        self::assertSame($expected, $isSupported);
    }

    final public static function dpIsSupportedHashContent(): Generator
    {
        yield 'crap' => ['this is an invalid hash', false];
        yield 'valid sha1' => ['a052cfe45093f1c2d26bd854d06aa370ceca3b38', true];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsSupportedExternalReferenceType')]
    final public function testIsSupportedExternalReferenceType(ExternalReferenceType $value, bool $expected): void
    {
        $isSupported = static::getSpec()->isSupportedExternalReferenceType($value);
        self::assertSame($expected, $isSupported);
    }

    final public static function dpIsSupportedExternalReferenceType(): Generator
    {
        $known = BomSpecData::getExternalReferenceTypeForVersion(static::getSpecVersion()->value);
        $values = ExternalReferenceType::cases();
        foreach ($values as $value) {
            yield $value->name => [$value, \in_array($value->value, $known, true)];
        }
    }

    final public function testSupportsLicenseExpression(): void
    {
        $isSupported = static::getSpec()->supportsLicenseExpression();
        self::assertSame(static::shouldSupportLicenseExpression(), $isSupported);
    }

    abstract protected static function shouldSupportLicenseExpression(): bool;

    final public function testSupportsMetadata(): void
    {
        $isSupported = static::getSpec()->supportsMetadata();
        self::assertSame(static::shouldSupportMetadata(), $isSupported);
    }

    abstract protected static  function shouldSupportMetadata(): bool;

    final public function testSupportsBomRef(): void
    {
        $isSupported = static::getSpec()->supportsBomRef();
        self::assertSame(static::shouldSupportBomRef(), $isSupported);
    }

    abstract protected static function shouldSupportBomRef(): bool;

    final public function testSupportsDependencies(): void
    {
        $isSupported = static::getSpec()->supportsDependencies();
        self::assertSame(static::shouldSupportDependencies(), $isSupported);
    }

    abstract protected static function shouldSupportDependencies(): bool;

    final public static function testSupportsExternalReferenceHashes(): void
    {
        $isSupported = static::getSpec()->supportsExternalReferenceHashes();
        self::assertSame(static::shouldSupportExternalReferenceHashes(), $isSupported);
    }

    abstract protected static function shouldSupportExternalReferenceHashes(): bool;

    final public function testRequiresComponentVersion(): void
    {
        $isSupported = static::getSpec()->requiresComponentVersion();
        self::assertSame(static::shouldRequireComponentVersion(), $isSupported);
    }

    abstract protected static function shouldRequireComponentVersion(): bool;

    final public function testSupportsToolExternalReferences(): void
    {
        $isSupported = static::getSpec()->supportsToolExternalReferences();
        self::assertSame(static::shouldSupportToolExternalReferences(), $isSupported);
    }

    abstract protected static function shouldSupportToolExternalReferences(): bool;

    final public function testSupportsMetadataProperties(): void
    {
        $isSupported = static::getSpec()->supportsMetadataProperties();
        self::assertSame(static::shouldSupportMetadataProperties(), $isSupported);
    }

    abstract protected static function shouldSupportMetadataProperties(): bool;

    final public function testSupportsComponentProperties(): void
    {
        $isSupported = static::getSpec()->supportsComponentProperties();
        self::assertSame(static::shouldSupportComponentProperties(), $isSupported);
    }

    abstract protected static function shouldSupportComponentProperties(): bool;
}
