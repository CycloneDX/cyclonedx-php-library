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

use CycloneDX\Core\Collections\ExternalReferenceRepository;
use CycloneDX\Core\Collections\HashDictionary;
use CycloneDX\Core\Collections\LicenseRepository;
use CycloneDX\Core\Collections\PropertyRepository;
use CycloneDX\Core\Enums\ComponentType;
use CycloneDX\Core\Models\BomRef;
use CycloneDX\Core\Models\Component;
use CycloneDX\Core\Models\ComponentEvidence;
use CycloneDX\Core\Serialization\JSON\_BaseNormalizer;
use CycloneDX\Core\Serialization\JSON\NormalizerFactory;
use CycloneDX\Core\Serialization\JSON\Normalizers;
use CycloneDX\Core\Spec\_SpecProtocol;
use DomainException;
use Generator;
use PackageUrl\PackageUrl;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Normalizers\ComponentNormalizer::class)]
#[CoversClass(_BaseNormalizer::class)]
#[UsesClass(BomRef::class)]
class ComponentNormalizerTest extends TestCase
{
    public function testNormalizeThrowsOnUnsupportedType(): void
    {
        $component = $this->createConfiguredMock(
            Component::class,
            [
                'getName' => 'foo',
                'getType' => ComponentType::Library,
                'getVersion' => 'v1.33.7',
            ]
        );
        $spec = $this->createMock(_SpecProtocol::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, ['getSpec' => $spec]);
        $normalizer = new Normalizers\ComponentNormalizer($factory);

        $spec->expects(self::once())->method('isSupportedComponentType')
            ->with(ComponentType::Library)
            ->willReturn(false);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/Component .+ has unsupported type/i');

        $normalizer->normalize($component);
    }

    #[DataProvider('dptNormalizeMinimal')]
    public function testNormalizeMinimal(array $expected, bool $requiresComponentVersion): void
    {
        $component = $this->createConfiguredMock(
            Component::class,
            [
                'getName' => 'foo',
                'getVersion' => null,
                'getType' => ComponentType::Library,
                'getGroup' => null,
                'getDescription' => null,
                'getAuthor' => null,
                'getLicenses' => $this->createStub(LicenseRepository::class),
                'getHashes' => $this->createStub(HashDictionary::class),
                'getPackageUrl' => null,
            ]
        );
        $spec = $this->createMock(_SpecProtocol::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, ['getSpec' => $spec]);
        $normalizer = new Normalizers\ComponentNormalizer($factory);

        $spec->expects(self::once())
            ->method('isSupportedComponentType')
            ->with(ComponentType::Library)
            ->willReturn(true);
        $spec->method('requiresComponentVersion')
            ->willReturn($requiresComponentVersion);

        $actual = $normalizer->normalize($component);

        self::assertSame($expected, $actual);
    }

    public static function dptNormalizeMinimal(): Generator
    {
        yield 'mandatory Component Version' => [
            [
                'type' => 'library',
                'name' => 'foo',
                'version' => '',
            ],
            true,
        ];
        yield 'optional Component Version' => [
            [
                'type' => 'library',
                'name' => 'foo',
            ],
            false,
        ];
    }

    public function testNormalizeFull(): void
    {
        $component = $this->createConfiguredMock(
            Component::class,
            [
                'getBomRef' => new BomRef('myBomRef'),
                'getName' => 'myName',
                'getVersion' => 'some-version',
                'getType' => ComponentType::Library,
                'getGroup' => 'myGroup',
                'getDescription' => 'my description',
                'getAuthor' => 'Jan Kowalleck',
                'getLicenses' => $this->createConfiguredMock(LicenseRepository::class, ['count' => 1]),
                'getCopyright' => '(c) me and the gang',
                'getEvidence' => $this->createMock(ComponentEvidence::class),
                'getHashes' => $this->createConfiguredMock(HashDictionary::class, ['count' => 1]),
                'getPackageUrl' => 'pkg:generic/FakePURL',
            ]
        );
        $spec = $this->createConfiguredMock(_SpecProtocol::class, [
            'supportsBomRef' => true,
            'supportsComponentEvidence' => true,
        ]);
        $licenseRepoNormalizer = $this->createMock(Normalizers\LicenseRepositoryNormalizer::class);
        $HashDictionaryNormalizer = $this->createMock(Normalizers\HashDictionaryNormalizer::class);
        $evidenceNormalizer = $this->createMock(Normalizers\ComponentEvidenceNormalizer::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $spec,
            'makeForLicenseRepository' => $licenseRepoNormalizer,
            'makeForHashDictionary' => $HashDictionaryNormalizer,
            'makeForComponentEvidence' => $evidenceNormalizer,
        ]);
        $normalizer = new Normalizers\ComponentNormalizer($factory);

        $spec->expects(self::once())
            ->method('isSupportedComponentType')
            ->with(ComponentType::Library)
            ->willReturn(true);
        $licenseRepoNormalizer->expects(self::once())
            ->method('normalize')
            ->with($component->getLicenses())
            ->willReturn(['FakeLicenses']);
        $HashDictionaryNormalizer->expects(self::once())
            ->method('normalize')
            ->with($component->getHashes())
            ->willReturn(['FakeHashes']);
        $evidenceNormalizer->expects(self::once())
            ->method('normalize')
            ->with($component->getEvidence())
            ->willReturn(['FakeEvidence']);

        $actual = $normalizer->normalize($component);

        self::assertEquals(
            [
                'bom-ref' => 'myBomRef',
                'type' => 'library',
                'name' => 'myName',
                'version' => 'some-version',
                'group' => 'myGroup',
                'description' => 'my description',
                'author' => 'Jan Kowalleck',
                'hashes' => ['FakeHashes'],
                'licenses' => ['FakeLicenses'],
                'copyright' => '(c) me and the gang',
                'evidence' => ['FakeEvidence'],
                'purl' => 'pkg:generic/FakePURL',
            ],
            $actual
        );
    }

    public function testNormalizeLicenses(): void
    {
        $component = $this->createConfiguredMock(
            Component::class,
            [
                'getName' => 'myName',
                'getType' => ComponentType::Library,
                'getLicenses' => $this->createConfiguredMock(LicenseRepository::class, ['count' => 1]),
            ]
        );
        $spec = $this->createMock(_SpecProtocol::class);
        $licenseRepoNormalizer = $this->createMock(Normalizers\LicenseRepositoryNormalizer::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $spec,
            'makeForLicenseRepository' => $licenseRepoNormalizer,
        ]);
        $normalizer = new Normalizers\ComponentNormalizer($factory);

        $spec->expects(self::once())
            ->method('isSupportedComponentType')
            ->with(ComponentType::Library)
            ->willReturn(true);
        $licenseRepoNormalizer->expects(self::once())
            ->method('normalize')
            ->with($component->getLicenses())
            ->willReturn(['FakeLicenses']);

        $got = $normalizer->normalize($component);

        self::assertEquals([
            'type' => 'library',
            'name' => 'myName',
            'licenses' => ['FakeLicenses'],
        ], $got);
    }

    public function testNormalizeLicensesEmpty(): void
    {
        $component = $this->createConfiguredMock(
            Component::class,
            [
                'getName' => 'myName',
                'getType' => ComponentType::Library,
                'getLicenses' => $this->createConfiguredMock(LicenseRepository::class, ['count' => 0]),
            ]
        );
        $spec = $this->createMock(_SpecProtocol::class);
        $licenseRepoNormalizer = $this->createMock(Normalizers\LicenseRepositoryNormalizer::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $spec,
            'makeForLicenseRepository' => $licenseRepoNormalizer,
        ]);
        $normalizer = new Normalizers\ComponentNormalizer($factory);

        $spec->expects(self::once())
            ->method('isSupportedComponentType')
            ->with(ComponentType::Library)
            ->willReturn(true);
        $licenseRepoNormalizer->expects(self::never())
            ->method('normalize');

        $got = $normalizer->normalize($component);

        self::assertEquals([
            'type' => 'library',
            'name' => 'myName',
        ], $got);
    }

    // region normalize ExternalReferences

    public function testNormalizeExternalReferences(): void
    {
        $component = $this->createConfiguredMock(
            Component::class,
            [
                'getName' => 'myName',
                'getType' => ComponentType::Library,
                'getExternalReferences' => $this->createConfiguredMock(ExternalReferenceRepository::class, ['count' => 1]),
            ]
        );
        $spec = $this->createMock(_SpecProtocol::class);
        $externalReferenceRepositoryNormalizer = $this->createMock(Normalizers\ExternalReferenceRepositoryNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'makeForExternalReferenceRepository' => $externalReferenceRepositoryNormalizer,
            ]
        );
        $normalizer = new Normalizers\ComponentNormalizer($factory);

        $spec->expects(self::once())
            ->method('isSupportedComponentType')
            ->with(ComponentType::Library)
            ->willReturn(true);
        $externalReferenceRepositoryNormalizer->expects(self::once())
            ->method('normalize')
            ->with($component->getExternalReferences())
            ->willReturn(['FakeExternalReference']);

        $actual = $normalizer->normalize($component);

        self::assertSame([
            'type' => 'library',
            'name' => 'myName',
            'externalReferences' => ['FakeExternalReference'],
        ], $actual);
    }

    public function testNormalizeExternalReferencesOmitIfEmpty(): void
    {
        $component = $this->createConfiguredMock(
            Component::class,
            [
                'getName' => 'myName',
                'getType' => ComponentType::Library,
                'getExternalReferences' => $this->createConfiguredMock(ExternalReferenceRepository::class, ['count' => 0]),
            ]
        );
        $spec = $this->createMock(_SpecProtocol::class);
        $externalReferenceRepositoryNormalizer = $this->createMock(Normalizers\ExternalReferenceRepositoryNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'makeForExternalReferenceRepository' => $externalReferenceRepositoryNormalizer,
            ]
        );
        $normalizer = new Normalizers\ComponentNormalizer($factory);

        $spec->expects(self::once())
            ->method('isSupportedComponentType')
            ->with(ComponentType::Library)
            ->willReturn(true);
        $externalReferenceRepositoryNormalizer->expects(self::never())
            ->method('normalize');

        $actual = $normalizer->normalize($component);

        self::assertEquals([
            'type' => 'library',
            'name' => 'myName',
        ], $actual);
    }

    // endregion normalize ExternalReferences

    public function testNormalizeProperties(): void
    {
        $component = $this->createConfiguredMock(
            Component::class,
            [
                'getName' => 'myName',
                'getType' => ComponentType::Library,
                'getProperties' => $this->createConfiguredMock(PropertyRepository::class, ['count' => 2]),
            ]
        );
        $spec = $this->createConfiguredMock(_SpecProtocol::class, ['supportsComponentProperties' => true]);
        $repoNormalizer = $this->createMock(Normalizers\PropertyRepositoryNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'makeForPropertyRepository' => $repoNormalizer,
            ]
        );
        $normalizer = new Normalizers\ComponentNormalizer($factory);

        $spec->expects(self::once())
            ->method('isSupportedComponentType')
            ->with(ComponentType::Library)
            ->willReturn(true);
        $repoNormalizer->expects(self::once())
            ->method('normalize')
            ->with($component->getProperties())
            ->willReturn([['FakeProperty' => 'dummy']]);

        $actual = $normalizer->normalize($component);

        self::assertEquals([
            'type' => 'library',
            'name' => 'myName',
            'properties' => [['FakeProperty' => 'dummy']],
        ], $actual);
    }

    public function testNormalizePropertiesOmitWhenEmpty(): void
    {
        $component = $this->createConfiguredMock(
            Component::class,
            [
                'getName' => 'myName',
                'getType' => ComponentType::Library,
                'getProperties' => $this->createConfiguredMock(PropertyRepository::class, ['count' => 0]),
            ]
        );
        $spec = $this->createConfiguredMock(_SpecProtocol::class, ['supportsComponentProperties' => true]);
        $repoNormalizer = $this->createMock(Normalizers\PropertyRepositoryNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'makeForPropertyRepository' => $repoNormalizer,
            ]
        );
        $normalizer = new Normalizers\ComponentNormalizer($factory);

        $spec->expects(self::once())
            ->method('isSupportedComponentType')
            ->with(ComponentType::Library)
            ->willReturn(true);

        $actual = $normalizer->normalize($component);

        self::assertEquals([
            'type' => 'library',
            'name' => 'myName',
        ], $actual);
    }
}
