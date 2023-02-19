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

namespace CycloneDX\Tests\Core\Serialization\DOM;

use CycloneDX\Core\Serialization\DOM\_BaseNormalizer;
use CycloneDX\Core\Serialization\DOM\NormalizerFactory;
use CycloneDX\Core\Serialization\DOM\Normalizers;
use CycloneDX\Core\Spec\Spec;
use CycloneDX\Core\Spec\Version;
use DomainException;
use DOMDocument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NormalizerFactory::class)]
#[UsesClass(_BaseNormalizer::class)]
#[UsesClass(Normalizers\ComponentRepositoryNormalizer::class)]
#[UsesClass(Normalizers\BomNormalizer::class)]
#[UsesClass(Normalizers\LicenseNormalizer::class)]
#[UsesClass(Normalizers\LicenseRepositoryNormalizer::class)]
#[UsesClass(Normalizers\HashDictionaryNormalizer::class)]
#[UsesClass(Normalizers\ComponentNormalizer::class)]
#[UsesClass(Normalizers\HashNormalizer::class)]
#[UsesClass(Normalizers\MetadataNormalizer::class)]
#[UsesClass(Normalizers\ToolRepositoryNormalizer::class)]
#[UsesClass(Normalizers\ToolNormalizer::class)]
#[UsesClass(Normalizers\ToolNormalizer::class)]
#[UsesClass(Normalizers\ExternalReferenceNormalizer::class)]
#[UsesClass(Normalizers\ExternalReferenceRepositoryNormalizer::class)]
#[UsesClass(Normalizers\PropertyNormalizer::class)]
#[UsesClass(Normalizers\PropertyRepositoryNormalizer::class)]
class NormalizerFactoryTest extends TestCase
{
    public function testConstructor(): NormalizerFactory
    {
        $spec = $this->createConfiguredMock(
            Spec::class,
            [
                'isSupportedFormat' => true,
            ]
        );

        $factory = new NormalizerFactory($spec);
        self::assertSame($spec, $factory->getSpec());
        self::assertInstanceOf(DOMDocument::class, $factory->getDocument());

        return $factory;
    }

    public function testConstructThrowsWhenUnsupported(): void
    {
        $spec = $this->createConfiguredMock(
            Spec::class,
            [
                'getVersion' => Version::v1dot4,
                'isSupportedFormat' => false,
            ]
        );

        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/unsupported format/i');

        new NormalizerFactory($spec);
    }

    #[Depends('testConstructor')]
    public function testMakeForComponentRepository(NormalizerFactory $factory): void
    {
        $normalizer = $factory->makeForComponentRepository();
        self::assertInstanceOf(Normalizers\ComponentRepositoryNormalizer::class, $normalizer);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }

    #[Depends('testConstructor')]
    public function testMakeForBom(NormalizerFactory $factory): void
    {
        $normalizer = $factory->makeForBom();
        self::assertInstanceOf(Normalizers\BomNormalizer::class, $normalizer);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }

    #[Depends('testConstructor')]
    public function testMakeForLicense(NormalizerFactory $factory): void
    {
        $normalizer = $factory->makeForLicense();
        self::assertInstanceOf(Normalizers\LicenseNormalizer::class, $normalizer);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }

    #[Depends('testConstructor')]
    public function testMakeForDisjunctiveLicense(NormalizerFactory $factory): void
    {
        $normalizer = $factory->makeForLicenseRepository();
        self::assertInstanceOf(Normalizers\LicenseRepositoryNormalizer::class, $normalizer);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }

    #[Depends('testConstructor')]
    public function testMakeForHashDictionary(NormalizerFactory $factory): void
    {
        $normalizer = $factory->makeForHashDictionary();
        self::assertInstanceOf(Normalizers\HashDictionaryNormalizer::class, $normalizer);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }

    #[Depends('testConstructor')]
    public function testMakeForComponent(NormalizerFactory $factory): void
    {
        $normalizer = $factory->makeForComponent();
        self::assertInstanceOf(Normalizers\ComponentNormalizer::class, $normalizer);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }

    #[Depends('testConstructor')]
    public function testMakeForHash(NormalizerFactory $factory): void
    {
        $normalizer = $factory->makeForHash();
        self::assertInstanceOf(Normalizers\HashNormalizer::class, $normalizer);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }

    #[Depends('testConstructor')]
    public function testMakeForMetadata(NormalizerFactory $factory): void
    {
        $normalizer = $factory->makeForMetadata();
        self::assertInstanceOf(Normalizers\MetadataNormalizer::class, $normalizer);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }

    #[Depends('testConstructor')]
    public function testMakeForToolRepository(NormalizerFactory $factory): void
    {
        $normalizer = $factory->makeForToolRepository();
        self::assertInstanceOf(Normalizers\ToolRepositoryNormalizer::class, $normalizer);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }

    #[Depends('testConstructor')]
    public function testMakeForTool(NormalizerFactory $factory): void
    {
        $normalizer = $factory->makeForTool();
        self::assertInstanceOf(Normalizers\ToolNormalizer::class, $normalizer);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }

    #[Depends('testConstructor')]
    public function testMakeForDependencies(NormalizerFactory $factory): void
    {
        $normalizer = $factory->makeForDependencies();
        self::assertInstanceOf(Normalizers\DependenciesNormalizer::class, $normalizer);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }

    #[Depends('testConstructor')]
    public function testMakeForExternalReference(NormalizerFactory $factory): void
    {
        $normalizer = $factory->makeForExternalReference();
        self::assertInstanceOf(Normalizers\ExternalReferenceNormalizer::class, $normalizer);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }

    #[Depends('testConstructor')]
    public function testMakeForExternalReferenceRepository(NormalizerFactory $factory): void
    {
        $normalizer = $factory->makeForExternalReferenceRepository();
        self::assertInstanceOf(Normalizers\ExternalReferenceRepositoryNormalizer::class, $normalizer);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }

    #[Depends('testConstructor')]
    public function testMakeForProperty(NormalizerFactory $factory): void
    {
        $normalizer = $factory->makeForProperty();
        self::assertInstanceOf(Normalizers\PropertyNormalizer::class, $normalizer);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }

    #[Depends('testConstructor')]
    public function testMakeForPropertyRepository(NormalizerFactory $factory): void
    {
        $normalizer = $factory->makeForPropertyRepository();
        self::assertInstanceOf(Normalizers\PropertyRepositoryNormalizer::class, $normalizer);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }
}
