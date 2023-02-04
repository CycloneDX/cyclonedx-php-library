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

namespace CycloneDX\Tests\Core\Serialization\JSON;

use CycloneDX\Core\Serialization\JSON\NormalizerFactory;
use CycloneDX\Core\Serialization\JSON\Normalizers;
use CycloneDX\Core\Spec\Spec;
use CycloneDX\Core\Spec\Version;
use DomainException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Serialization\JSON\NormalizerFactory
 *
 * @uses   \CycloneDX\Core\Serialization\JSON\_BaseNormalizer
 */
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

    /**
     * @depends testConstructor
     *
     * @uses    \CycloneDX\Core\Serialization\JSON\Normalizers\ComponentRepositoryNormalizer
     */
    public function testMakeForComponentRepository(NormalizerFactory $factory): void
    {
        $normalizer = $factory->makeForComponentRepository();
        self::assertInstanceOf(Normalizers\ComponentRepositoryNormalizer::class, $normalizer);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }

    /**
     * @depends testConstructor
     *
     * @uses    \CycloneDX\Core\Serialization\JSON\Normalizers\BomNormalizer
     */
    public function testMakeForBom(NormalizerFactory $factory): void
    {
        $normalizer = $factory->makeForBom();
        self::assertInstanceOf(Normalizers\BomNormalizer::class, $normalizer);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }

    /**
     * @depends testConstructor
     *
     * @uses    \CycloneDX\Core\Serialization\JSON\Normalizers\LicenseNormalizer
     */
    public function testMakeForLicense(NormalizerFactory $factory): void
    {
        $normalizer = $factory->makeForLicense();
        self::assertInstanceOf(Normalizers\LicenseNormalizer::class, $normalizer);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }

    /**
     * @depends testConstructor
     *
     * @uses    \CycloneDX\Core\Serialization\JSON\Normalizers\LicenseRepositoryNormalizer
     */
    public function testMakeForLicenseRepository(NormalizerFactory $factory): void
    {
        $normalizer = $factory->makeForLicenseRepository();
        self::assertInstanceOf(Normalizers\LicenseRepositoryNormalizer::class, $normalizer);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }

    /**
     * @depends testConstructor
     *
     * @uses    \CycloneDX\Core\Serialization\JSON\Normalizers\HashDictionaryNormalizer
     */
    public function testMakeForHashDictionary(NormalizerFactory $factory): void
    {
        $normalizer = $factory->makeForHashDictionary();
        self::assertInstanceOf(Normalizers\HashDictionaryNormalizer::class, $normalizer);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }

    /**
     * @depends testConstructor
     *
     * @uses    \CycloneDX\Core\Serialization\JSON\Normalizers\ComponentNormalizer
     */
    public function testMakeForComponent(NormalizerFactory $factory): void
    {
        $normalizer = $factory->makeForComponent();
        self::assertInstanceOf(Normalizers\ComponentNormalizer::class, $normalizer);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }

    /**
     * @depends testConstructor
     *
     * @uses    \CycloneDX\Core\Serialization\JSON\Normalizers\HashNormalizer
     */
    public function testMakeForHash(NormalizerFactory $factory): void
    {
        $normalizer = $factory->makeForHash();
        self::assertInstanceOf(Normalizers\HashNormalizer::class, $normalizer);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }

    /**
     * @depends testConstructor
     *
     * @uses    \CycloneDX\Core\Serialization\JSON\Normalizers\MetadataNormalizer
     */
    public function testMakeForMetadata(NormalizerFactory $factory): void
    {
        $normalizer = $factory->makeForMetadata();
        self::assertInstanceOf(Normalizers\MetadataNormalizer::class, $normalizer);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }

    /**
     * @depends testConstructor
     *
     * @uses    \CycloneDX\Core\Serialization\JSON\Normalizers\ToolRepositoryNormalizer
     */
    public function testMakeForToolRepository(NormalizerFactory $factory): void
    {
        $normalizer = $factory->makeForToolRepository();
        self::assertInstanceOf(Normalizers\ToolRepositoryNormalizer::class, $normalizer);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }

    /**
     * @depends testConstructor
     *
     * @uses    \CycloneDX\Core\Serialization\JSON\Normalizers\ToolNormalizer
     */
    public function testMakeForTool(NormalizerFactory $factory): void
    {
        $normalizer = $factory->makeForTool();
        self::assertInstanceOf(Normalizers\ToolNormalizer::class, $normalizer);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }

    /**
     * @depends testConstructor
     *
     * @uses    \CycloneDX\Core\Serialization\JSON\Normalizers\ToolNormalizer
     * @uses    \CycloneDX\Core\Serialization\JSON\Normalizers\DependenciesNormalizer
     */
    public function testMakeForDependencies(NormalizerFactory $factory): void
    {
        $normalizer = $factory->makeForDependencies();
        self::assertInstanceOf(Normalizers\DependenciesNormalizer::class, $normalizer);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }

    /**
     * @depends testConstructor
     *
     * @uses    \CycloneDX\Core\Serialization\JSON\Normalizers\ExternalReferenceNormalizer
     */
    public function testMakeForExternalReference(NormalizerFactory $factory): void
    {
        $normalizer = $factory->makeForExternalReference();
        self::assertInstanceOf(Normalizers\ExternalReferenceNormalizer::class, $normalizer);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }

    /**
     * @depends testConstructor
     *
     * @uses    \CycloneDX\Core\Serialization\JSON\Normalizers\ExternalReferenceRepositoryNormalizer
     */
    public function testMakeForExternalReferenceRepository(NormalizerFactory $factory): void
    {
        $normalizer = $factory->makeForExternalReferenceRepository();
        self::assertInstanceOf(Normalizers\ExternalReferenceRepositoryNormalizer::class, $normalizer);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }

    /**
     * @depends testConstructor
     *
     * @uses    \CycloneDX\Core\Serialization\JSON\Normalizers\PropertyNormalizer
     */
    public function testMakeForProperty(NormalizerFactory $factory): void
    {
        $normalizer = $factory->makeForProperty();
        self::assertInstanceOf(Normalizers\PropertyNormalizer::class, $normalizer);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }

    /**
     * @depends testConstructor
     *
     * @uses    \CycloneDX\Core\Serialization\JSON\Normalizers\PropertyRepositoryNormalizer
     */
    public function testMakeForPropertyRepository(NormalizerFactory $factory): void
    {
        $normalizer = $factory->makeForPropertyRepository();
        self::assertInstanceOf(Normalizers\PropertyRepositoryNormalizer::class, $normalizer);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }
}
