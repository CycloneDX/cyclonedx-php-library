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
use CycloneDX\Core\Spec\Spec;
use CycloneDX\Tests\_data\BomSpecData;
use Generator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

abstract class SpecBaseTestCase extends TestCase
{
    abstract protected function getSpec(): Spec;

    /**
     * @psalm-return \CycloneDX\Core\Spec\Version::*
     */
    abstract protected function getSpecVersion(): string;

    final public function testVersionMatches(): void
    {
        $version = $this->getSpec()->getVersion();
        self::assertSame($this->getSpecVersion(), $version);
    }

    abstract protected function shouldSupportFormats(): array;

    final public function testKnownFormats(): array
    {
        $formats = $this->shouldSupportFormats();

        self::assertIsArray($formats);
        self::assertNotEmpty($formats);

        return $formats;
    }

    /**
     * @dataProvider dpIsSupportsFormat
     */
    final public function testIsSupportedFormat(string $format, bool $expected): void
    {
        $isSupported = $this->getSpec()->isSupportedFormat($format);
        self::assertSame($expected, $isSupported);
    }

    final public function dpIsSupportsFormat(): Generator
    {
        yield 'unknown' => [uniqid('Format', false), false];
        foreach ($this->shouldSupportFormats() as $format) {
            yield $format => [$format, true];
        }
    }

    /**
     * @dataProvider dpIsSupportedComponentType
     */
    final public function testIsSupportedComponentType(string $value, bool $expected): void
    {
        $isSupported = $this->getSpec()->isSupportedComponentType($value);
        self::assertSame($expected, $isSupported);
    }

    final public function dpIsSupportedComponentType(): Generator
    {
        yield 'unknown' => [uniqid('ComponentType', false), false];
        $known = BomSpecData::getClassificationEnumForVersion($this->getSpecVersion());
        $values = (new ReflectionClass(ComponentType::class))->getConstants();
        foreach ($values as $value) {
            yield $value => [$value, \in_array($value, $known, true)];
        }
    }

    /**
     * @dataProvider dpIsSupportedHashAlgorithm
     */
    final public function testIsSupportedHashAlgorithm(string $value, bool $expected): void
    {
        $isSupported = $this->getSpec()->isSupportedHashAlgorithm($value);
        self::assertSame($expected, $isSupported);
    }

    final public function dpIsSupportedHashAlgorithm(): Generator
    {
        yield 'unknown' => [uniqid('HashAlg', false), false];
        $known = BomSpecData::getHashAlgEnumForVersion($this->getSpecVersion());
        $values = (new ReflectionClass(HashAlgorithm::class))->getConstants();
        foreach ($values as $value) {
            yield $value => [$value, \in_array($value, $known, true)];
        }
    }

    /**
     * @dataProvider dpIsSupportedHashContent
     */
    final public function testIsSupportedHashContent(string $value, bool $expected): void
    {
        $isSupported = $this->getSpec()->isSupportedHashContent($value);
        self::assertSame($expected, $isSupported);
    }

    final public function dpIsSupportedHashContent(): Generator
    {
        yield 'crap' => ['this is an invalid hash', false];
        yield 'valid sha1' => ['a052cfe45093f1c2d26bd854d06aa370ceca3b38', true];
    }

    /**
     * @dataProvider dpIsSupportedExternalReferenceType
     */
    final public function testIsSupportedExternalReferenceType(string $value, bool $expected): void
    {
        $isSupported = $this->getSpec()->isSupportedExternalReferenceType($value);
        self::assertSame($expected, $isSupported);
    }

    final public function dpIsSupportedExternalReferenceType(): Generator
    {
        yield 'unknown' => [uniqid('ExternalReferenceType', false), false];
        $known = BomSpecData::getExternalReferenceTypeForVersion($this->getSpecVersion());
        $values = (new ReflectionClass(ExternalReferenceType::class))->getConstants();
        foreach ($values as $value) {
            yield $value => [$value, \in_array($value, $known, true)];
        }
    }

    final public function testSupportsLicenseExpression(): void
    {
        $isSupported = $this->getSpec()->supportsLicenseExpression();
        self::assertSame($this->shouldSupportLicenseExpression(), $isSupported);
    }

    abstract public function shouldSupportLicenseExpression(): bool;

    final public function testSupportsMetadata(): void
    {
        $isSupported = $this->getSpec()->supportsMetadata();
        self::assertSame($this->shouldSupportMetadata(), $isSupported);
    }

    abstract public function shouldSupportMetadata(): bool;

    final public function testSupportsBomRef(): void
    {
        $isSupported = $this->getSpec()->supportsBomRef();
        self::assertSame($this->shouldSupportBomRef(), $isSupported);
    }

    abstract public function shouldSupportBomRef(): bool;

    final public function testSupportsDependencies(): void
    {
        $isSupported = $this->getSpec()->supportsDependencies();
        self::assertSame($this->shouldSupportDependencies(), $isSupported);
    }

    abstract public function shouldSupportDependencies(): bool;

    final public function testSupportsExternalReferenceHashes(): void
    {
        $isSupported = $this->getSpec()->supportsExternalReferenceHashes();
        self::assertSame($this->shouldSupportExternalReferenceHashes(), $isSupported);
    }

    abstract public function shouldSupportExternalReferenceHashes(): bool;

    final public function testRequiresComponentVersion(): void
    {
        $isSupported = $this->getSpec()->requiresComponentVersion();
        self::assertSame($this->shouldRequireComponentVersion(), $isSupported);
    }

    abstract public function shouldRequireComponentVersion(): bool;

    final public function testSupportsToolExternalReferences(): void
    {
        $isSupported = $this->getSpec()->supportsToolExternalReferences();
        self::assertSame($this->shouldSupportToolExternalReferences(), $isSupported);
    }

    abstract public function shouldSupportToolExternalReferences(): bool;

    final public function testSupportsMetadataProperties(): void
    {
        $isSupported = $this->getSpec()->supportsMetadataProperties();
        self::assertSame($this->shouldSupportMetadataProperties(), $isSupported);
    }

    abstract public function shouldSupportMetadataProperties(): bool;

    final public function testSupportsComponentProperties(): void
    {
        $isSupported = $this->getSpec()->supportsComponentProperties();
        self::assertSame($this->shouldSupportComponentProperties(), $isSupported);
    }

    abstract public function shouldSupportComponentProperties(): bool;
}