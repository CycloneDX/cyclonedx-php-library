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

namespace CycloneDX\Tests\Core\Serialization\DOM\Normalizers;

use CycloneDX\Core\_helpers\SimpleDOM;
use CycloneDX\Core\Collections\ComponentRepository;
use CycloneDX\Core\Collections\ExternalReferenceRepository;
use CycloneDX\Core\Collections\PropertyRepository;
use CycloneDX\Core\Models\Bom;
use CycloneDX\Core\Models\Component;
use CycloneDX\Core\Models\ExternalReference;
use CycloneDX\Core\Models\Metadata;
use CycloneDX\Core\Serialization\DOM\_BaseNormalizer;
use CycloneDX\Core\Serialization\DOM\NormalizerFactory;
use CycloneDX\Core\Serialization\DOM\Normalizers;
use CycloneDX\Core\Spec\Spec;
use CycloneDX\Core\Spec\Version;
use CycloneDX\Tests\_traits\DomNodeAssertionTrait;
use CycloneDX\Tests\_traits\MockOCTrait;
use DOMDocument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use function pcov\memory;

#[CoversClass(Normalizers\BomNormalizer::class)]
#[CoversClass(_BaseNormalizer::class)]
#[UsesClass(NormalizerFactory::class)]
#[UsesClass(SimpleDOM::class)]
#[UsesClass(ExternalReferenceRepository::class)]
#[UsesClass(ExternalReferenceRepository::class)]
class BomNormalizerTest extends TestCase
{
    use DomNodeAssertionTrait;
    use MockOCTrait;

    private NormalizerFactory & MockObject $factory;
    private Spec&MockObject $spec;

    protected function setUp(): void
    {
        $this->spec = $this->createConfiguredMock(
            Spec::class,
            [
            'isSupportedFormat' => true,
            'getVersion' => Version::v1dot2]
        );
        $this->factory = $this->createMockOC(
            NormalizerFactory::class,
            [$this->spec,  new DOMDocument()]
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->factory,
            $this->spec
        );
     }

    public function testNormalize(): void
    {
        $normalizer = new Normalizers\BomNormalizer($this->factory);
        $bom = $this->createConfiguredMock(
            Bom::class,
            [
                'getSerialNumber' => 'urn:uuid:12345678-dead-1337-beef-123456789012',
                'getVersion' => 23,
            ]
        );

        $actual = $normalizer->normalize($bom);

        self::assertStringEqualsDomNode(
            '<bom xmlns="http://cyclonedx.org/schema/bom/1.2" serialNumber="urn:uuid:12345678-dead-1337-beef-123456789012" version="23">'.
                '<components></components>'.
            '</bom>',
            $actual
        );
    }

    public function testNormalizeComponents(): void
    {
        $componentsNormalizer = $this->createMock(Normalizers\ComponentRepositoryNormalizer::class);
        $this->factory->method('makeForComponentRepository')->willReturn($componentsNormalizer);
        $normalizer = new Normalizers\BomNormalizer($this->factory);
        $bom = $this->createConfiguredMock(
            Bom::class,
            [
                'getVersion' => 42,
                'getComponents' => $this->createMock(ComponentRepository::class),
            ]
        );

        $componentsNormalizer->expects(self::once())
            ->method('normalize')
            ->with($bom->getComponents())
            ->willReturn([$this->factory->document->createElement('FakeComponent', 'dummy')]);

        $actual = $normalizer->normalize($bom);

        self::assertStringEqualsDomNode(
            '<bom xmlns="http://cyclonedx.org/schema/bom/1.2" version="42">'.
                '<components><FakeComponent>dummy</FakeComponent></components>'.
            '</bom>',
            $actual
        );
    }

    // region metadata

    public function testNormalizeMetadata(): void
    {
        $this->spec->method('supportsMetadata')->willReturn(true);
        $metadataNormalizer = $this->createMock(Normalizers\MetadataNormalizer::class);
        $this->factory->method( 'makeForMetadata')->willReturn($metadataNormalizer);
        $normalizer = new Normalizers\BomNormalizer($this->factory);
        $bom = $this->createConfiguredMock(
            Bom::class,
            [
                'getVersion' => 1337,
                'getMetadata' => $this->createMock(Metadata::class),
            ]
        );

        $metadataNormalizer->expects(self::once())
            ->method('normalize')
            ->with($bom->getMetadata())
            ->willReturn($this->factory->document->createElement('metadata', 'FakeMetadata'));

        $actual = $normalizer->normalize($bom);

        self::assertStringEqualsDomNode(
            '<bom xmlns="http://cyclonedx.org/schema/bom/1.2" version="1337">'.
                '<metadata>FakeMetadata</metadata>'.
                '<components></components>'.
            '</bom>',
            $actual
        );
    }

    public function testNormalizeMetadataNotSupported(): void
    {
        $this->spec->method('supportsMetadata')->willReturn(false);
        $metadataNormalizer = $this->createMock(Normalizers\MetadataNormalizer::class);
        $this->factory->method('makeForMetadata')->willReturn($metadataNormalizer);
        $normalizer = new Normalizers\BomNormalizer($this->factory);
        $bom = $this->createConfiguredMock(
            Bom::class,
            [
                'getVersion' => 1,
                'getMetadata' => $this->createMock(Metadata::class),
            ]
        );

        $metadataNormalizer->method('normalize')
            ->with($bom->getMetadata())
            ->willReturn($this->factory->document->createElement('metadata', 'FakeMetadata'));

        $actual = $normalizer->normalize($bom);

        self::assertStringEqualsDomNode(
            '<bom xmlns="http://cyclonedx.org/schema/bom/1.2" version="1">'.
                '<components></components>'.
            '</bom>',
            $actual
        );
    }

    // endregion metadata

    // region dependencies

    public function testNormalizeDependencies(): void
    {
        $spec = $this->createConfiguredMock(
            Spec::class,
            [
                'isSupportedFormat' => true,
                'getVersion' => Version::v1dot2,
                'supportsDependencies' => true,
            ]
        );
        $dependencyNormalizer = $this->createMock(Normalizers\DependenciesNormalizer::class);
        $factory = $this->createConfiguredMockOC(
            NormalizerFactory::class,
            [
                'spec' => $spec,
                'document' => new DOMDocument(),
                ], [
                'makeForDependencies' => $dependencyNormalizer,
            ]
        );
        $normalizer = new Normalizers\BomNormalizer($factory);
        $bom = $this->createConfiguredMock(
            Bom::class,
            [
                'getVersion' => 23,
                'getMetadata' => $this->createMock(Metadata::class),
            ]
        );

        $dependencyNormalizer->expects(self::once())
            ->method('normalize')
            ->with($bom)
            ->willReturn([$factory->document->createElement('FakeDependencies', 'faked')]);

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
            Spec::class,
            [
                'isSupportedFormat' => true,
                'getVersion' => Version::v1dot2,
                'supportsDependencies' => true,
            ]
        );
        $dependencyNormalizer = $this->createMock(Normalizers\DependenciesNormalizer::class);
        $factory = $this->createConfiguredMockOC(
            NormalizerFactory::class,
            [
                'spec' => $spec,
                'document' => new DOMDocument(),
                ], [
                'makeForDependencies' => $dependencyNormalizer,
            ]
        );
        $normalizer = new Normalizers\BomNormalizer($factory);
        $bom = $this->createConfiguredMock(
            Bom::class,
            [
                'getVersion' => 23,
                'getMetadata' => $this->createMock(Metadata::class),
            ]
        );

        $dependencyNormalizer->expects(self::once())
            ->method('normalize')
            ->with($bom)
            ->willReturn([/* empty */]);

        $actual = $normalizer->normalize($bom);

        self::assertStringEqualsDomNode(
            '<bom xmlns="http://cyclonedx.org/schema/bom/1.2" version="23">'.
            '<components></components>'.
            // 'dependencies' is unset,
            '</bom>',
            $actual
        );
    }

    // endregion dependencies

    // region external references

    public function testNormalizeExternalReferencesMergedIfUnsupportedMetadata(): void
    {
        $spec = $this->createConfiguredMock(Spec::class, [
            'isSupportedFormat' => true,
            'getVersion' => Version::v1dot2,
            'supportsMetadata' => false,
        ]);
        $extRefNormalizer = $this->createMock(Normalizers\ExternalReferenceRepositoryNormalizer::class);
        $factory = $this->createConfiguredMockOC(NormalizerFactory::class, [
            'spec' => $spec,
            'document' => new DOMDocument(),
            ], [
            'makeForExternalReferenceRepository' => $extRefNormalizer,
        ]);
        $normalizer = new Normalizers\BomNormalizer($factory);
        $extRef1 = $this->createMock(ExternalReference::class);
        $extRef2 = $this->createMock(ExternalReference::class);
        $bom = $this->createConfiguredMock(
            Bom::class,
            [
                'getVersion' => 23,
                'getExternalReferences' => new ExternalReferenceRepository($extRef1),
                'getMetadata' => $this->createConfiguredMock(
                    Metadata::class,
                    [
                        'getComponent' => $this->createConfiguredMock(
                            Component::class, [
                                'getExternalReferences' => new ExternalReferenceRepository($extRef2),
                            ]
                        ),
                    ]
                ),
            ]
        );

        $extRefNormalizer->expects(self::once())
            ->method('normalize')
            ->with($this->callback(static function (ExternalReferenceRepository $extRefs) use ($extRef1, $extRef2) {
                self::assertEquals([$extRef1, $extRef2], $extRefs->getItems());

                return true;
            }))
            ->willReturn([$factory->document->createElement('FakeexternalReference', 'faked')]);

        $actual = $normalizer->normalize($bom);

        self::assertStringEqualsDomNode(
            '<bom xmlns="http://cyclonedx.org/schema/bom/1.2" version="23">'.
            '<components></components>'.
            '<externalReferences><FakeexternalReference>faked</FakeexternalReference></externalReferences>'.
            '</bom>',
            $actual
        );
    }

    public function testNormalizeExternalReferencesOmittedWHenEmpty(): void
    {
        $spec = $this->createConfiguredMock(Spec::class, [
            'isSupportedFormat' => true,
            'getVersion' => Version::v1dot2,
            'supportsMetadata' => false,
        ]);
        $extRefNormalizer = $this->createMock(Normalizers\ExternalReferenceRepositoryNormalizer::class);
        $factory = $this->createConfiguredMockOC(NormalizerFactory::class,
            [
            'spec' => $spec,
            'document' => new DOMDocument(),
            ], [
            'makeForExternalReferenceRepository' => $extRefNormalizer,
        ]);
        $normalizer = new Normalizers\BomNormalizer($factory);
        $extRef1 = $this->createMock(ExternalReference::class);
        $extRef2 = $this->createMock(ExternalReference::class);
        $bom = $this->createConfiguredMock(
            Bom::class,
            [
                'getVersion' => 23,
                'getExternalReferences' => new ExternalReferenceRepository($extRef1),
            ]
        );

        $extRefNormalizer->expects(self::once())
            ->method('normalize')
            ->with($bom->getExternalReferences())
            ->willReturn([/* empty */]);

        $actual = $normalizer->normalize($bom);

        self::assertStringEqualsDomNode(
            '<bom xmlns="http://cyclonedx.org/schema/bom/1.2" version="23">'.
            '<components></components>'.
            // 'externalReferences' is unset,
            '</bom>',
            $actual
        );
    }

    // endregion external references

    // region properties

    public function testNormalizeProperties(): void
    {
        $bom = $this->createConfiguredMock(
            Bom::class,
            [
                'getProperties' => $this->createConfiguredMock(PropertyRepository::class, ['count' => 2]),
            ]
        );
        $spec = $this->createConfiguredMock(Spec::class, [
            'isSupportedFormat' => true,
            'getVersion' => Version::v1dot4,
            'supportsBomProperties' => true,
        ]);
        $propertiesNormalizer = $this->createMock(Normalizers\PropertyRepositoryNormalizer::class);
        $factory = $this->createConfiguredMockOC(
            NormalizerFactory::class,
            [
                'spec' => $spec,
                'document' => new DOMDocument(),
                ], [
                'makeForPropertyRepository' => $propertiesNormalizer,
            ]
        );
        $normalizer = new Normalizers\BomNormalizer($factory);

        $propertiesNormalizer->expects(self::once())
            ->method('normalize')
            ->with($bom->getProperties())
            ->willReturn(
                [$factory->document->createElement('FakeProperties', 'dummy')]);

        $actual = $normalizer->normalize($bom);

        self::assertStringEqualsDomNode(
            '<bom xmlns="http://cyclonedx.org/schema/bom/1.4" version="0"><components></components>'.
            '<properties><FakeProperties>dummy</FakeProperties></properties>'.
            '</bom>',
            $actual
        );
    }

    public function testNormalizePropertiesOmitEmpty(): void
    {
        $bom = $this->createConfiguredMock(
            Bom::class,
            [
                'getProperties' => $this->createConfiguredMock(PropertyRepository::class, ['count' => 0]),
            ]
        );
        $spec = $this->createConfiguredMock(Spec::class, [
            'isSupportedFormat' => true,
            'getVersion' => Version::v1dot4,
            'supportsBomProperties' => true,
        ]);
        $propertiesNormalizer = $this->createMock(Normalizers\PropertyRepositoryNormalizer::class);
        $factory = $this->createConfiguredMockOC(
            NormalizerFactory::class,
            [
                'spec' => $spec,
                'document' => new DOMDocument(),
                ], [
                'makeForPropertyRepository' => $propertiesNormalizer,
            ]
        );
        $normalizer = new Normalizers\BomNormalizer($factory);

        $actual = $normalizer->normalize($bom);

        self::assertStringEqualsDomNode(
            '<bom xmlns="http://cyclonedx.org/schema/bom/1.4" version="0"><components></components></bom>',
            $actual
        );
    }

    // endregion properties
}
