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
 * Copyright (c) Steve Springett. All Rights Reserved.
 */

namespace CycloneDX\Tests\Core\Serialize\DOM\Normalizers;

use CycloneDX\Core\Models\Bom;
use CycloneDX\Core\Models\MetaData;
use CycloneDX\Core\Repositories\ComponentRepository;
use CycloneDX\Core\Repositories\ExternalReferenceRepository;
use CycloneDX\Core\Serialize\DOM\NormalizerFactory;
use CycloneDX\Core\Serialize\DOM\Normalizers;
use CycloneDX\Core\Spec\SpecInterface;
use CycloneDX\Tests\_traits\DomNodeAssertionTrait;
use DOMDocument;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Serialize\DOM\Normalizers\BomNormalizer
 * @covers \CycloneDX\Core\Serialize\DOM\AbstractNormalizer
 * @covers \CycloneDX\Core\Helpers\SimpleDomTrait
 */
class BomNormalizerTest extends TestCase
{
    use DomNodeAssertionTrait;

    public function testNormalize(): void
    {
        $spec = $this->createConfiguredMock(SpecInterface::class, ['getVersion' => '1.2']);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'getDocument' => new DOMDocument(),
            ]
        );
        $normalizer = new Normalizers\BomNormalizer($factory);
        $bom = $this->createConfiguredMock(
            Bom::class,
            [
                'getVersion' => 23,
            ]
        );

        $actual = $normalizer->normalize($bom);

        self::assertStringEqualsDomNode(
            '<bom xmlns="http://cyclonedx.org/schema/bom/1.2" version="23">'.
            '<components></components>'.
            '</bom>',
            $actual
        );
    }

    public function testNormalizeComponents(): void
    {
        $spec = $this->createConfiguredMock(SpecInterface::class, ['getVersion' => '1.2']);
        $componentsNormalizer = $this->createMock(Normalizers\ComponentRepositoryNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'getDocument' => new DOMDocument(),
                'makeForComponentRepository' => $componentsNormalizer,
            ]
        );
        $normalizer = new Normalizers\BomNormalizer($factory);
        $bom = $this->createConfiguredMock(
            Bom::class,
            [
                'getVersion' => 23,
                'getComponentRepository' => $this->createStub(ComponentRepository::class),
            ]
        );

        $componentsNormalizer->expects(self::once())
            ->method('normalize')
            ->with($bom->getComponentRepository())
            ->willReturn([$factory->getDocument()->createElement('FakeComponent', 'dummy')]);

        $actual = $normalizer->normalize($bom);

        self::assertStringEqualsDomNode(
            '<bom xmlns="http://cyclonedx.org/schema/bom/1.2" version="23">'.
            '<components><FakeComponent>dummy</FakeComponent></components>'.
            '</bom>',
            $actual
        );
    }

    public function testNormalizeMetaData(): void
    {
        $spec = $this->createConfiguredMock(
            SpecInterface::class,
            [
                'getVersion' => '1.2',
                'supportsMetaData' => true,
            ]
        );
        $metadataNormalizer = $this->createMock(Normalizers\MetaDataNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'getDocument' => new DOMDocument(),
                'makeForMetaData' => $metadataNormalizer,
            ]
        );
        $normalizer = new Normalizers\BomNormalizer($factory);
        $bom = $this->createConfiguredMock(
            Bom::class,
            [
                'getVersion' => 23,
                'getMetaData' => $this->createStub(MetaData::class),
            ]
        );

        $metadataNormalizer->expects(self::once())
            ->method('normalize')
            ->with($bom->getMetaData())
            ->willReturn($factory->getDocument()->createElement('metadata', 'FakeMetaData'));

        $actual = $normalizer->normalize($bom);

        self::assertStringEqualsDomNode(
            '<bom xmlns="http://cyclonedx.org/schema/bom/1.2" version="23">'.
            '<metadata>FakeMetaData</metadata>'.
            '<components></components>'.
            '</bom>',
            $actual
        );
    }

    public function testNormalizeMetaDataNotSupported(): void
    {
        $spec = $this->createConfiguredMock(
            SpecInterface::class,
            [
                'getVersion' => '1.2',
                'supportsMetaData' => false,
            ]
        );
        $metadataNormalizer = $this->createMock(Normalizers\MetaDataNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'getDocument' => new DOMDocument(),
                'makeForMetaData' => $metadataNormalizer,
            ]
        );
        $normalizer = new Normalizers\BomNormalizer($factory);
        $bom = $this->createConfiguredMock(
            Bom::class,
            [
                'getVersion' => 23,
                'getMetaData' => $this->createStub(MetaData::class),
            ]
        );

        $metadataNormalizer->method('normalize')
            ->with($bom->getMetaData())
            ->willReturn($factory->getDocument()->createElement('metadata', 'FakeMetaData'));

        $actual = $normalizer->normalize($bom);

        self::assertStringEqualsDomNode(
            '<bom xmlns="http://cyclonedx.org/schema/bom/1.2" version="23">'.
            '<components></components>'.
            '</bom>',
            $actual
        );
    }

    public function testNormalizeDependencies(): void
    {
        $spec = $this->createConfiguredMock(
            SpecInterface::class,
            [
                'getVersion' => '1.2',
                'supportsDependencies' => true,
            ]
        );
        $dependencyNormalizer = $this->createMock(Normalizers\DependenciesNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'getDocument' => new DOMDocument(),
                'makeForDependencies' => $dependencyNormalizer,
            ]
        );
        $normalizer = new Normalizers\BomNormalizer($factory);
        $bom = $this->createConfiguredMock(
            Bom::class,
            [
                'getVersion' => 23,
                'getMetaData' => $this->createStub(MetaData::class),
            ]
        );

        $dependencyNormalizer->expects(self::once())
            ->method('normalize')
            ->with($bom)
            ->willReturn([$factory->getDocument()->createElement('FakeDependencies', 'faked')]);

        $actual = $normalizer->normalize($bom);

        self::assertStringEqualsDomNode(
            '<bom xmlns="http://cyclonedx.org/schema/bom/1.2" version="23">'.
            '<components></components>'.
            '<dependencies><FakeDependencies>faked</FakeDependencies></dependencies>'.
            '</bom>',
            $actual
        );
    }

    public function testNormalizeDependenciesOmitWhenEmpty(): void
    {
        $spec = $this->createConfiguredMock(
            SpecInterface::class,
            [
                'getVersion' => '1.2',
                'supportsDependencies' => true,
            ]
        );
        $dependencyNormalizer = $this->createMock(Normalizers\DependenciesNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'getDocument' => new DOMDocument(),
                'makeForDependencies' => $dependencyNormalizer,
            ]
        );
        $normalizer = new Normalizers\BomNormalizer($factory);
        $bom = $this->createConfiguredMock(
            Bom::class,
            [
                'getVersion' => 23,
                'getMetaData' => $this->createStub(MetaData::class),
            ]
        );

        $dependencyNormalizer->expects(self::once())
            ->method('normalize')
            ->with($bom)
            ->willReturn([]);

        $actual = $normalizer->normalize($bom);

        self::assertStringEqualsDomNode(
            '<bom xmlns="http://cyclonedx.org/schema/bom/1.2" version="23">'.
            '<components></components>'.
            // 'dependencies' is unset,
            '</bom>',
            $actual
        );
    }

    // region normalize ExternalReferences

    public function testNormalizeExternalReferences(): void
    {
        $spec = $this->createConfiguredMock(
            SpecInterface::class,
            [
                'getVersion' => '1.2',
            ]
        );
        $externalReferenceRepositoryNormalizer = $this->createMock(Normalizers\ExternalReferenceRepositoryNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'getDocument' => new DOMDocument(),
                'makeForExternalReferenceRepository' => $externalReferenceRepositoryNormalizer,
            ]
        );
        $normalizer = new Normalizers\BomNormalizer($factory);
        $bom = $this->createConfiguredMock(
            Bom::class,
            [
                'getVersion' => 23,
                'getExternalReferenceRepository' => $this->createConfiguredMock(ExternalReferenceRepository::class, ['count' => 1]),
            ]
        );

        $externalReferenceRepositoryNormalizer->expects(self::once())
            ->method('normalize')
            ->with($bom->getExternalReferenceRepository())
            ->willReturn([$factory->getDocument()->createElement('FakeReferenceRepositoryNormalized', 'faked')]);

        $actual = $normalizer->normalize($bom);

        self::assertStringEqualsDomNode(
            '<bom xmlns="http://cyclonedx.org/schema/bom/1.2" version="23">'.
            '<components></components>'.
            '<externalReferences><FakeReferenceRepositoryNormalized>faked</FakeReferenceRepositoryNormalized></externalReferences>'.
            '</bom>',
            $actual
        );
    }

    public function testNormalizeExternalReferencesOmitIfEmpty(): void
    {
        $spec = $this->createConfiguredMock(
            SpecInterface::class,
            [
                'getVersion' => '1.2',
            ]
        );
        $externalReferenceRepositoryNormalizer = $this->createMock(Normalizers\ExternalReferenceRepositoryNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'getDocument' => new DOMDocument(),
                'makeForExternalReferenceRepository' => $externalReferenceRepositoryNormalizer,
            ]
        );
        $normalizer = new Normalizers\BomNormalizer($factory);
        $bom = $this->createConfiguredMock(
            Bom::class,
            [
                'getVersion' => 23,
                'getExternalReferenceRepository' => $this->createConfiguredMock(ExternalReferenceRepository::class, ['count' => 0]),
            ]
        );

        $externalReferenceRepositoryNormalizer->expects(self::never())
            ->method('normalize')
            ->with($bom->getExternalReferenceRepository())
            ->willReturn(['FakeReferenceRepositoryNormalized']);

        $actual = $normalizer->normalize($bom);

        self::assertStringEqualsDomNode(
            '<bom xmlns="http://cyclonedx.org/schema/bom/1.2" version="23">'.
            '<components></components>'.
            // externalReferences are omitted
            '</bom>',
            $actual
        );
    }

    // endregion normalize ExternalReferences
}
