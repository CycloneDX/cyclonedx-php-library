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

namespace CycloneDX\Tests\Core\Serialize\DOM\Normalizers;

use CycloneDX\Core\Models\Bom;
use CycloneDX\Core\Models\Component;
use CycloneDX\Core\Models\ExternalReference;
use CycloneDX\Core\Models\Metadata;
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
            ->with($bom->getComponents())
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
                'getMetaData' => $this->createStub(Metadata::class),
            ]
        );

        $metadataNormalizer->expects(self::once())
            ->method('normalize')
            ->with($bom->getMetadata())
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
                'getMetaData' => $this->createStub(Metadata::class),
            ]
        );

        $metadataNormalizer->method('normalize')
            ->with($bom->getMetadata())
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
                'getMetaData' => $this->createStub(Metadata::class),
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
                'getMetaData' => $this->createStub(Metadata::class),
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
                'getDocument' => new DOMDocument(),
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
            ->willReturn([$factory->getDocument()->createElement('FakeNormalized', 'faked')]);

        $actual = $normalizer->normalize($bom);

        self::assertStringEqualsDomNode(
            '<bom xmlns="http://cyclonedx.org/schema/bom/1.2" version="23">'.
            '<components></components>'.
            '<externalReferences><FakeNormalized>faked</FakeNormalized></externalReferences>'.
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
                'getDocument' => new DOMDocument(),
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

        self::assertStringEqualsDomNode(
            '<bom xmlns="http://cyclonedx.org/schema/bom/1.2" version="23">'.
            '<components></components>'.
            // externalReferences are omitted
            '</bom>',
            $actual
        );
    }

    /**
     * @dataProvider dpNormalizeExternalReferencesWithComponent
     *
     * @uses \CycloneDX\Core\Repositories\ExternalReferenceRepository
     */
    public function testNormalizeExternalReferencesWithComponent(
        ?ExternalReferenceRepository $bomExternalReferenceRepository,
        ?ExternalReferenceRepository $componentExternalReferenceRepository
    ): void {
        $expectedNormal = $bomExternalReferenceRepository || $componentExternalReferenceRepository
            ? '<externalReferences><FakeNormalized>faked</FakeNormalized></externalReferences>'
            : '';
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
                'getDocument' => new DOMDocument(),
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
            $actualRefs = $repo->getExternalReferences();
            self::assertCount(
                ($bomExternalReferenceRepository ? \count($bomExternalReferenceRepository) : 0)
                + ($componentExternalReferenceRepository ? \count($componentExternalReferenceRepository) : 0),
                $actualRefs
            );

            if ($bomExternalReferenceRepository) {
                foreach ($bomExternalReferenceRepository->getExternalReferences() as $expectedRef) {
                    self::assertContains($expectedRef, $actualRefs);
                }
            }

            if ($componentExternalReferenceRepository) {
                foreach ($componentExternalReferenceRepository->getExternalReferences() as $expectedRef) {
                    self::assertContains($expectedRef, $actualRefs);
                }
            }

            return true;
        };
        $externalReferenceRepositoryNormalizer->method('normalize')
            ->with($this->callback($repoContainsNeeded))
            ->willReturn([$factory->getDocument()->createElement('FakeNormalized', 'faked')]);

        $actual = $normalizer->normalize($bom);

        self::assertStringEqualsDomNode(
            '<bom xmlns="http://cyclonedx.org/schema/bom/1.1" version="23">'.
            '<components></components>'.
            $expectedNormal.
            '</bom>',
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
