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

namespace CycloneDX\Tests\Core\Serialization\JSON\Normalizers;

use CycloneDX\Core\Collections\ComponentRepository;
use CycloneDX\Core\Collections\ExternalReferenceRepository;
use CycloneDX\Core\Models\Bom;
use CycloneDX\Core\Models\Component;
use CycloneDX\Core\Models\ExternalReference;
use CycloneDX\Core\Models\Metadata;
use CycloneDX\Core\Serialization\JSON\NormalizerFactory;
use CycloneDX\Core\Serialization\JSON\Normalizers;
use CycloneDX\Core\Spec\Spec;
use CycloneDX\Core\Spec\Version;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\CycloneDX\Core\Serialization\JSON\Normalizers\BomNormalizer::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\CycloneDX\Core\Serialization\JSON\_BaseNormalizer::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(\CycloneDX\Core\Serialization\JSON\Normalizers\DependenciesNormalizer::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(\CycloneDX\Core\Serialization\JSON\Normalizers\MetadataNormalizer::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(\CycloneDX\Core\_helpers\NullAssertionTrait::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(\CycloneDX\Core\Collections\ExternalReferenceRepository::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(\CycloneDX\Core\Collections\ExternalReferenceRepository::class)]
class BomNormalizerTest extends TestCase
{
    public function testNormalize(): void
    {
        $spec = $this->createConfiguredMock(Spec::class, ['getVersion' => Version::v1dot2]);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
            ]
        );
        $normalizer = new Normalizers\BomNormalizer($factory);
        $bom = $this->createConfiguredMock(
            Bom::class,
            [
                'getSerialNumber' => 'urn:uuid:12345678-dead-1337-beef-123456789012',
                'getVersion' => 23,
            ]
        );

        $actual = $normalizer->normalize($bom);

        self::assertSame(
            [
                '$schema' => 'http://cyclonedx.org/schema/bom-1.2b.schema.json',
                'bomFormat' => 'CycloneDX',
                'specVersion' => '1.2',
                'serialNumber' => 'urn:uuid:12345678-dead-1337-beef-123456789012',
                'version' => 23,
                'components' => [],
            ],
            $actual
        );
    }

    public function testNormalizeComponents(): void
    {
        $spec = $this->createConfiguredMock(Spec::class, ['getVersion' => Version::v1dot2]);
        $componentsNormalizer = $this->createMock(Normalizers\ComponentRepositoryNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'makeForComponentRepository' => $componentsNormalizer,
            ]
        );
        $normalizer = new Normalizers\BomNormalizer($factory);
        $bom = $this->createConfiguredMock(
            Bom::class,
            [
                'getVersion' => 42,
                'getComponents' => $this->createStub(ComponentRepository::class),
            ]
        );

        $componentsNormalizer->expects(self::once())
            ->method('normalize')
            ->with($bom->getComponents())
            ->willReturn(['FakeComponents']);

        $actual = $normalizer->normalize($bom);

        self::assertSame(
            [
                '$schema' => 'http://cyclonedx.org/schema/bom-1.2b.schema.json',
                'bomFormat' => 'CycloneDX',
                'specVersion' => '1.2',
                'version' => 42,
                'components' => ['FakeComponents'],
            ],
            $actual
        );
    }

    // region metadata

    public function testNormalizeMetadata(): void
    {
        $spec = $this->createConfiguredMock(
            Spec::class,
            [
                'getVersion' => Version::v1dot2,
                'supportsMetadata' => true,
            ]
        );
        $metadataNormalizer = $this->createMock(Normalizers\MetadataNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'makeForMetadata' => $metadataNormalizer,
            ]
        );
        $normalizer = new Normalizers\BomNormalizer($factory);
        $bom = $this->createConfiguredMock(
            Bom::class,
            [
                'getVersion' => 1337,
                'getMetadata' => $this->createStub(Metadata::class),
            ]
        );

        $metadataNormalizer->expects(self::once())
            ->method('normalize')
            ->with($bom->getMetadata())
            ->willReturn(['FakeMetadata']);

        $actual = $normalizer->normalize($bom);

        self::assertSame(
            [
                '$schema' => 'http://cyclonedx.org/schema/bom-1.2b.schema.json',
                'bomFormat' => 'CycloneDX',
                'specVersion' => '1.2',
                'version' => 1337,
                'metadata' => ['FakeMetadata'],
                'components' => [],
            ],
            $actual
        );
    }

    public function testNormalizeMetadataEmpty(): void
    {
        $spec = $this->createConfiguredMock(
            Spec::class,
            [
                'getVersion' => Version::v1dot2,
                'supportsMetadata' => true,
            ]
        );
        $metadataNormalizer = $this->createMock(Normalizers\MetadataNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'makeForMetadata' => $metadataNormalizer,
            ]
        );
        $normalizer = new Normalizers\BomNormalizer($factory);
        $bom = $this->createConfiguredMock(
            Bom::class,
            [
                'getVersion' => 1,
                'getMetadata' => $this->createStub(Metadata::class),
            ]
        );

        $metadataNormalizer->method('normalize')
            ->with($bom->getMetadata())
            ->willReturn([/* empty */]);

        $actual = $normalizer->normalize($bom);

        self::assertSame(
            [
                '$schema' => 'http://cyclonedx.org/schema/bom-1.2b.schema.json',
                'bomFormat' => 'CycloneDX',
                'specVersion' => '1.2',
                'version' => 1,
                'components' => [],
            ],
            $actual
        );
    }

    public function testNormalizeMetadataSkipped(): void
    {
        $spec = $this->createConfiguredMock(
            Spec::class,
            [
                'getVersion' => Version::v1dot2,
                'supportsMetadata' => false,
            ]
        );
        $metadataNormalizer = $this->createMock(Normalizers\MetadataNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'makeForMetadata' => $metadataNormalizer,
            ]
        );
        $normalizer = new Normalizers\BomNormalizer($factory);
        $bom = $this->createConfiguredMock(
            Bom::class,
            [
                'getVersion' => 1,
                'getMetadata' => $this->createStub(Metadata::class),
            ]
        );

        $metadataNormalizer->method('normalize')
            ->with($bom->getMetadata())
            ->willReturn(['FakeMetadata']);

        $actual = $normalizer->normalize($bom);

        self::assertSame(
            [
                '$schema' => 'http://cyclonedx.org/schema/bom-1.2b.schema.json',
                'bomFormat' => 'CycloneDX',
                'specVersion' => '1.2',
                'version' => 1,
                'components' => [],
            ],
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
                'getVersion' => Version::v1dot2,
                'supportsDependencies' => true,
            ]
        );
        $dependencyNormalizer = $this->createMock(Normalizers\DependenciesNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'makeForDependencies' => $dependencyNormalizer,
            ]
        );
        $normalizer = new Normalizers\BomNormalizer($factory);
        $bom = $this->createConfiguredMock(
            Bom::class,
            [
                'getVersion' => 1,
                'getMetadata' => $this->createStub(Metadata::class),
            ]
        );

        $dependencyNormalizer->expects(self::once())
            ->method('normalize')
            ->with($bom)
            ->willReturn(['FakeDependencies' => 'dummy']);

        $actual = $normalizer->normalize($bom);

        self::assertSame(
            [
                '$schema' => 'http://cyclonedx.org/schema/bom-1.2b.schema.json',
                'bomFormat' => 'CycloneDX',
                'specVersion' => '1.2',
                'version' => 1,
                'components' => [],
                'dependencies' => ['FakeDependencies' => 'dummy'],
            ],
            $actual
        );
    }

    public function testNormalizeDependenciesOmitWhenEmpty(): void
    {
        $spec = $this->createConfiguredMock(
            Spec::class,
            [
                'getVersion' => Version::v1dot2,
                'supportsDependencies' => true,
            ]
        );
        $dependencyNormalizer = $this->createMock(Normalizers\DependenciesNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'makeForDependencies' => $dependencyNormalizer,
            ]
        );
        $normalizer = new Normalizers\BomNormalizer($factory);
        $bom = $this->createConfiguredMock(
            Bom::class,
            [
                'getVersion' => 1,
                'getMetadata' => $this->createStub(Metadata::class),
            ]
        );

        $dependencyNormalizer->expects(self::once())
            ->method('normalize')
            ->with($bom)
            ->willReturn([]);

        $actual = $normalizer->normalize($bom);

        self::assertSame(
            [
                '$schema' => 'http://cyclonedx.org/schema/bom-1.2b.schema.json',
                'bomFormat' => 'CycloneDX',
                'specVersion' => '1.2',
                'version' => 1,
                'components' => [],
                // 'dependencies' is unset,
            ],
            $actual
        );
    }

    // endregion dependencies

    // region external references

    public function testNormalizeExternalReferencesMergedIfUnsupportedMetadata(): void
    {
        $spec = $this->createConfiguredMock(Spec::class, [
            'getVersion' => Version::v1dot2,
            'supportsMetadata' => false,
        ]);
        $extRefNormalizer = $this->createMock(Normalizers\ExternalReferenceRepositoryNormalizer::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $spec,
            'makeForExternalReferenceRepository' => $extRefNormalizer,
        ]);
        $normalizer = new Normalizers\BomNormalizer($factory);
        $extRef1 = $this->createStub(ExternalReference::class);
        $extRef2 = $this->createStub(ExternalReference::class);
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
            ->willReturn(['dummyExRefs']);

        $actual = $normalizer->normalize($bom);

        self::assertSame(
            [
                '$schema' => 'http://cyclonedx.org/schema/bom-1.2b.schema.json',
                'bomFormat' => 'CycloneDX',
                'specVersion' => '1.2',
                'version' => 23,
                'components' => [],
                'externalReferences' => ['dummyExRefs'],
            ],
            $actual
        );
    }

    public function testNormalizeExternalReferencesOmittedWHenEmpty(): void
    {
        $spec = $this->createConfiguredMock(Spec::class, [
            'getVersion' => Version::v1dot2,
            'supportsMetadata' => false,
        ]);
        $extRefNormalizer = $this->createMock(Normalizers\ExternalReferenceRepositoryNormalizer::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $spec,
            'makeForExternalReferenceRepository' => $extRefNormalizer,
        ]);
        $normalizer = new Normalizers\BomNormalizer($factory);
        $extRef1 = $this->createStub(ExternalReference::class);
        $extRef2 = $this->createStub(ExternalReference::class);
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

        self::assertSame(
            [
                '$schema' => 'http://cyclonedx.org/schema/bom-1.2b.schema.json',
                'bomFormat' => 'CycloneDX',
                'specVersion' => '1.2',
                'version' => 23,
                'components' => [],
            ],
            $actual
        );
    }

    // endregion external references
}
