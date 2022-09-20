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

namespace CycloneDX\Tests\Core\Serialize\JSON\Normalizers;

use CycloneDX\Core\Collections\ComponentRepository;
use CycloneDX\Core\Collections\ExternalReferenceRepository;
use CycloneDX\Core\Models\Bom;
use CycloneDX\Core\Models\Component;
use CycloneDX\Core\Models\ExternalReference;
use CycloneDX\Core\Models\Metadata;
use CycloneDX\Core\Serialize\JSON\NormalizerFactory;
use CycloneDX\Core\Serialize\JSON\Normalizers;
use CycloneDX\Core\Spec\SpecInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Serialize\JSON\Normalizers\BomNormalizer
 * @covers \CycloneDX\Core\Serialize\JSON\AbstractNormalizer
 *
 * @uses   \CycloneDX\Core\Serialize\JSON\Normalizers\DependenciesNormalizer
 * @uses   \CycloneDX\Core\Serialize\JSON\Normalizers\MetaDataNormalizer
 */
class BomNormalizerTest extends TestCase
{
    public function testNormalize(): void
    {
        $spec = $this->createConfiguredMock(SpecInterface::class, ['getVersion' => '1.2']);
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
                'getVersion' => 23,
            ]
        );

        $actual = $normalizer->normalize($bom);

        self::assertSame(
            [
                'bomFormat' => 'CycloneDX',
                'specVersion' => '1.2',
                'version' => 23,
                'components' => [],
            ],
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
            ->with($bom->getComponents())
            ->willReturn(['FakeComponents']);

        $actual = $normalizer->normalize($bom);

        self::assertSame(
            [
                'bomFormat' => 'CycloneDX',
                'specVersion' => '1.2',
                'version' => 23,
                'components' => ['FakeComponents'],
            ],
            $actual
        );
    }

    /**
     * @uses \CycloneDX\Core\Helpers\NullAssertionTrait
     */
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
                'makeForMetaData' => $metadataNormalizer,
            ]
        );
        $normalizer = new Normalizers\BomNormalizer($factory);
        $bom = $this->createConfiguredMock(
            Bom::class,
            [
                'getVersion' => 23,
                'getMetaData' => $this->createStub(Metadata::class),
            ]
        );

        $metadataNormalizer->expects(self::once())
            ->method('normalize')
            ->with($bom->getMetadata())
            ->willReturn(['FakeMetaData']);

        $actual = $normalizer->normalize($bom);

        self::assertSame(
            [
                'bomFormat' => 'CycloneDX',
                'specVersion' => '1.2',
                'version' => 23,
                'metadata' => ['FakeMetaData'],
                'components' => [],
            ],
            $actual
        );
    }

    public function testNormalizeMetaDataEmpty(): void
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
                'makeForMetaData' => $metadataNormalizer,
            ]
        );
        $normalizer = new Normalizers\BomNormalizer($factory);
        $bom = $this->createConfiguredMock(
            Bom::class,
            [
                'getVersion' => 23,
                'getMetaData' => $this->createStub(Metadata::class),
            ]
        );

        $metadataNormalizer->method('normalize')
            ->with($bom->getMetadata())
            ->willReturn([/* empty */]);

        $actual = $normalizer->normalize($bom);

        self::assertSame(
            [
                'bomFormat' => 'CycloneDX',
                'specVersion' => '1.2',
                'version' => 23,
                'components' => [],
            ],
            $actual
        );
    }

    public function testNormalizeMetaDataSkipped(): void
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
                'makeForMetaData' => $metadataNormalizer,
            ]
        );
        $normalizer = new Normalizers\BomNormalizer($factory);
        $bom = $this->createConfiguredMock(
            Bom::class,
            [
                'getVersion' => 23,
                'getMetaData' => $this->createStub(Metadata::class),
            ]
        );

        $metadataNormalizer->method('normalize')
            ->with($bom->getMetadata())
            ->willReturn(['FakeMetaData']);

        $actual = $normalizer->normalize($bom);

        self::assertSame(
            [
                'bomFormat' => 'CycloneDX',
                'specVersion' => '1.2',
                'version' => 23,
                'components' => [],
            ],
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
                'makeForDependencies' => $dependencyNormalizer,
            ]
        );
        $normalizer = new Normalizers\BomNormalizer($factory);
        $bom = $this->createConfiguredMock(
            Bom::class,
            [
                'getVersion' => 23,
                'getMetaData' => $this->createStub(Metadata::class),
            ]
        );

        $dependencyNormalizer->expects(self::once())
            ->method('normalize')
            ->with($bom)
            ->willReturn(['FakeDependencies' => 'dummy']);

        $actual = $normalizer->normalize($bom);

        self::assertSame(
            [
                'bomFormat' => 'CycloneDX',
                'specVersion' => '1.2',
                'version' => 23,
                'components' => [],
                'dependencies' => ['FakeDependencies' => 'dummy'],
            ],
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
                'makeForDependencies' => $dependencyNormalizer,
            ]
        );
        $normalizer = new Normalizers\BomNormalizer($factory);
        $bom = $this->createConfiguredMock(
            Bom::class,
            [
                'getVersion' => 23,
                'getMetaData' => $this->createStub(Metadata::class),
            ]
        );

        $dependencyNormalizer->expects(self::once())
            ->method('normalize')
            ->with($bom)
            ->willReturn([]);

        $actual = $normalizer->normalize($bom);

        self::assertSame(
            [
                'bomFormat' => 'CycloneDX',
                'specVersion' => '1.2',
                'version' => 23,
                'components' => [],
                // 'dependencies' is unset,
            ],
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
                'supportsMetaData' => true,
            ]
        );
        $externalReferenceRepositoryNormalizer = $this->createMock(
            Normalizers\ExternalReferenceRepositoryNormalizer::class
        );
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'makeForExternalReferenceRepository' => $externalReferenceRepositoryNormalizer,
            ]
        );
        $normalizer = new Normalizers\BomNormalizer($factory);
        $bom = $this->createConfiguredMock(
            Bom::class,
            [
                'getVersion' => 23,
                'getExternalReferenceRepository' => $this->createConfiguredMock(
                    ExternalReferenceRepository::class,
                    ['count' => 1]
                ),
            ]
        );

        $externalReferenceRepositoryNormalizer->expects(self::once())
            ->method('normalize')
            ->with($bom->getExternalReferences())
            ->willReturn(['FakeReferenceRepositoryNormalized']);

        $actual = $normalizer->normalize($bom);

        self::assertSame(
            [
                'bomFormat' => 'CycloneDX',
                'specVersion' => '1.2',
                'version' => 23,
                'components' => [],
                'externalReferences' => ['FakeReferenceRepositoryNormalized'],
            ],
            $actual
        );
    }

    public function testNormalizeExternalReferencesOmitIfEmpty(): void
    {
        $spec = $this->createConfiguredMock(
            SpecInterface::class,
            [
                'getVersion' => '1.2',
                'supportsMetaData' => true,
            ]
        );
        $externalReferenceRepositoryNormalizer = $this->createMock(
            Normalizers\ExternalReferenceRepositoryNormalizer::class
        );
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'makeForExternalReferenceRepository' => $externalReferenceRepositoryNormalizer,
            ]
        );
        $normalizer = new Normalizers\BomNormalizer($factory);
        $bom = $this->createConfiguredMock(
            Bom::class,
            [
                'getVersion' => 23,
                'getExternalReferenceRepository' => $this->createConfiguredMock(
                    ExternalReferenceRepository::class,
                    ['count' => 0]
                ),
            ]
        );

        $externalReferenceRepositoryNormalizer->method('normalize')
            ->with($bom->getExternalReferences())
            ->willReturn([/* empty */]);

        $actual = $normalizer->normalize($bom);

        self::assertSame(
            [
                'bomFormat' => 'CycloneDX',
                'specVersion' => '1.2',
                'version' => 23,
                'components' => [],
            ],
            $actual
        );
    }

    /**
     * @dataProvider dpNormalizeExternalReferencesWithComponent
     *
     * @uses \CycloneDX\Core\Collections\ExternalReferenceRepository
     */
    public function testNormalizeExternalReferencesWithComponent(
        ?ExternalReferenceRepository $bomExternalReferenceRepository,
        ?ExternalReferenceRepository $componentExternalReferenceRepository
    ): void {
        $expectedNormal = $bomExternalReferenceRepository || $componentExternalReferenceRepository
            ? ['externalReferences' => ['FakeReferenceRepositoryNormalized']]
            : [/* not rendered */];
        $spec = $this->createConfiguredMock(
            SpecInterface::class,
            [
                'getVersion' => '1.1',
                'supportsMetaData' => false,
            ]
        );
        $externalReferenceRepositoryNormalizer = $this->createMock(
            Normalizers\ExternalReferenceRepositoryNormalizer::class
        );
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'makeForExternalReferenceRepository' => $externalReferenceRepositoryNormalizer,
            ]
        );
        $normalizer = new Normalizers\BomNormalizer($factory);
        $bom = $this->createConfiguredMock(
            Bom::class,
            [
                'getVersion' => 23,
                'getExternalReferenceRepository' => $bomExternalReferenceRepository,
                'getMetaData' => $this->createConfiguredMock(
                    Metadata::class,
                    [
                        'getComponent' => $this->createConfiguredMock(Component::class, [
                            'getExternalReferenceRepository' => $componentExternalReferenceRepository,
                        ]),
                    ]
                ),
            ]
        );

        $repoContainsNeeded = static function (ExternalReferenceRepository $repo) use (
            $bomExternalReferenceRepository,
            $componentExternalReferenceRepository
        ): bool {
            $actualRefs = $repo->getItems();
            self::assertCount(
                ($bomExternalReferenceRepository ? \count($bomExternalReferenceRepository) : 0)
                + ($componentExternalReferenceRepository ? \count($componentExternalReferenceRepository) : 0),
                $actualRefs
            );

            if ($bomExternalReferenceRepository) {
                foreach ($bomExternalReferenceRepository->getItems() as $expectedRef) {
                    self::assertContains($expectedRef, $actualRefs);
                }
            }

            if ($componentExternalReferenceRepository) {
                foreach ($componentExternalReferenceRepository->getItems() as $expectedRef) {
                    self::assertContains($expectedRef, $actualRefs);
                }
            }

            return true;
        };
        $externalReferenceRepositoryNormalizer->method('normalize')
            ->with($this->callback($repoContainsNeeded))
            ->willReturn(['FakeReferenceRepositoryNormalized']);

        $actual = $normalizer->normalize($bom);

        self::assertSame(
            [
                'bomFormat' => 'CycloneDX',
                'specVersion' => '1.1',
                'version' => 23,
                'components' => [],
            ] + $expectedNormal,
            $actual
        );
    }

    public function dpNormalizeExternalReferencesWithComponent(): \Generator
    {
        yield 'null null' => [
            null,
            null,
        ];
        yield 'null one' => [
            null,
            new ExternalReferenceRepository($this->createStub(ExternalReference::class)),
        ];
        yield 'one null' => [
            new ExternalReferenceRepository($this->createStub(ExternalReference::class)),
            null,
        ];
        yield 'one one' => [
            new ExternalReferenceRepository($this->createStub(ExternalReference::class)),
            new ExternalReferenceRepository($this->createStub(ExternalReference::class)),
        ];
    }

    // endregion normalize ExternalReferences
}
