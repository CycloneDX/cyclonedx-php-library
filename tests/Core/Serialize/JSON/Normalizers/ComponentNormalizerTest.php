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

use CycloneDX\Core\Collections\ExternalReferenceRepository;
use CycloneDX\Core\Collections\HashDictionary;
use CycloneDX\Core\Collections\LicenseRepository;
use CycloneDX\Core\Models\BomRef;
use CycloneDX\Core\Models\Component;
use CycloneDX\Core\Serialize\JSON\NormalizerFactory;
use CycloneDX\Core\Serialize\JSON\Normalizers;
use CycloneDX\Core\Spec\Spec;
use DomainException;
use PackageUrl\PackageUrl;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Serialize\JSON\Normalizers\ComponentNormalizer
 * @covers \CycloneDX\Core\Serialize\JSON\_BaseNormalizer
 */
class ComponentNormalizerTest extends TestCase
{
    public function testNormalizeThrowsOnUnsupportedType(): void
    {
        $component = $this->createConfiguredMock(
            Component::class,
            [
                'getName' => 'foo',
                'getType' => 'FakeType',
                'getVersion' => 'v1.33.7',
            ]
        );
        $spec = $this->createMock(Spec::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, ['getSpec' => $spec]);
        $normalizer = new Normalizers\ComponentNormalizer($factory);

        $spec->expects(self::once())->method('isSupportedComponentType')
            ->with('FakeType')
            ->willReturn(false);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/Component .+ has unsupported type/i');

        $normalizer->normalize($component);
    }

    /**
     * @dataProvider dptNormalizeMinimal
     */
    public function testNormalizeMinimal(array $expected, bool $requiresComponentVersion): void
    {
        $component = $this->createConfiguredMock(
            Component::class,
            [
                'getName' => 'foo',
                'getVersion' => null,
                'getType' => 'FakeType',
                'getGroup' => null,
                'getDescription' => null,
                'getLicenses' => $this->createStub(LicenseRepository::class),
                'getHashes' => $this->createStub(HashDictionary::class),
                'getPackageUrl' => null,
            ]
        );
        $spec = $this->createMock(Spec::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, ['getSpec' => $spec]);
        $normalizer = new Normalizers\ComponentNormalizer($factory);

        $spec->expects(self::once())
            ->method('isSupportedComponentType')
            ->with('FakeType')
            ->willReturn(true);
        $spec->method('requiresComponentVersion')
            ->willReturn($requiresComponentVersion);

        $actual = $normalizer->normalize($component);

        self::assertSame($expected, $actual);
    }

    public function dptNormalizeMinimal(): \Generator
    {
        yield 'mandatory Component Version' => [
            [
                'type' => 'FakeType',
                'name' => 'foo',
                'version' => '',
            ],
            true,
        ];
        yield 'optional Component Version' => [
            [
                'type' => 'FakeType',
                'name' => 'foo',
            ],
            false,
        ];
    }

    /**
     * @uses \CycloneDX\Core\Models\BomRef
     */
    public function testNormalizeFull(): void
    {
        $component = $this->createConfiguredMock(
            Component::class,
            [
                'getBomRef' => new BomRef('myBomRef'),
                'getName' => 'myName',
                'getVersion' => 'some-version',
                'getType' => 'FakeType',
                'getGroup' => 'myGroup',
                'getDescription' => 'my description',
                'getLicenses' => $this->createConfiguredMock(LicenseRepository::class, ['count' => 1]),
                'getHashes' => $this->createConfiguredMock(HashDictionary::class, ['count' => 1]),
                'getPackageUrl' => $this->createConfiguredMock(
                    PackageUrl::class,
                    ['toString' => 'FakePURL', '__toString' => 'FakePURL']
                ),
            ]
        );
        $spec = $this->createConfiguredMock(Spec::class, [
            'supportsBomRef' => true,
        ]);
        $licenseRepoNormalizer = $this->createMock(Normalizers\LicenseRepositoryNormalizer::class);
        $HashDictionaryNormalizer = $this->createMock(Normalizers\HashDictionaryNormalizer::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $spec,
            'makeForLicenseRepository' => $licenseRepoNormalizer,
            'makeForHashDictionary' => $HashDictionaryNormalizer,
        ]);
        $normalizer = new Normalizers\ComponentNormalizer($factory);

        $spec->expects(self::once())
            ->method('isSupportedComponentType')
            ->with('FakeType')
            ->willReturn(true);
        $licenseRepoNormalizer->expects(self::once())
            ->method('normalize')
            ->with($component->getLicenses())
            ->willReturn(['FakeLicenses']);
        $HashDictionaryNormalizer->expects(self::once())
            ->method('normalize')
            ->with($component->getHashes())
            ->willReturn(['FakeHashes']);

        $actual = $normalizer->normalize($component);

        self::assertEquals(
            [
                'bom-ref' => 'myBomRef',
                'type' => 'FakeType',
                'name' => 'myName',
                'version' => 'some-version',
                'group' => 'myGroup',
                'description' => 'my description',
                'hashes' => ['FakeHashes'],
                'licenses' => ['FakeLicenses'],
                'purl' => 'FakePURL',
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
                'getType' => 'FakeType',
                'getLicenses' => $this->createConfiguredMock(LicenseRepository::class, ['count' => 1]),
            ]
        );
        $spec = $this->createMock(Spec::class);
        $licenseRepoNormalizer = $this->createMock(Normalizers\LicenseRepositoryNormalizer::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $spec,
            'makeForLicenseRepository' => $licenseRepoNormalizer,
        ]);
        $normalizer = new Normalizers\ComponentNormalizer($factory);

        $spec->expects(self::once())
            ->method('isSupportedComponentType')
            ->with('FakeType')
            ->willReturn(true);
        $licenseRepoNormalizer->expects(self::once())
            ->method('normalize')
            ->with($component->getLicenses())
            ->willReturn(['FakeLicenses']);

        $got = $normalizer->normalize($component);

        self::assertEquals([
            'type' => 'FakeType',
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
                'getType' => 'FakeType',
                'getLicenses' => $this->createConfiguredMock(LicenseRepository::class, ['count' => 0]),
            ]
        );
        $spec = $this->createMock(Spec::class);
        $licenseRepoNormalizer = $this->createMock(Normalizers\LicenseRepositoryNormalizer::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $spec,
            'makeForLicenseRepository' => $licenseRepoNormalizer,
        ]);
        $normalizer = new Normalizers\ComponentNormalizer($factory);

        $spec->expects(self::once())
            ->method('isSupportedComponentType')
            ->with('FakeType')
            ->willReturn(true);
        $licenseRepoNormalizer->expects(self::never())
            ->method('normalize');

        $got = $normalizer->normalize($component);

        self::assertEquals([
            'type' => 'FakeType',
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
                'getType' => 'FakeType',
                'getExternalReferences' => $this->createConfiguredMock(ExternalReferenceRepository::class, ['count' => 1]),
            ]
        );
        $spec = $this->createMock(Spec::class);
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
            ->with('FakeType')
            ->willReturn(true);
        $externalReferenceRepositoryNormalizer->expects(self::once())
            ->method('normalize')
            ->with($component->getExternalReferences())
            ->willReturn(['FakeExternalReference']);

        $actual = $normalizer->normalize($component);

        self::assertSame([
            'type' => 'FakeType',
            'name' => 'myName',
            'externalReferences' => ['FakeExternalReference'],
            ],
            $actual
        );
    }

    public function testNormalizeExternalReferencesOmitIfEmpty(): void
    {
        $component = $this->createConfiguredMock(
            Component::class,
            [
                'getName' => 'myName',
                'getType' => 'FakeType',
                'getExternalReferences' => $this->createConfiguredMock(ExternalReferenceRepository::class, ['count' => 0]),
            ]
        );
        $spec = $this->createMock(Spec::class);
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
            ->with('FakeType')
            ->willReturn(true);
        $externalReferenceRepositoryNormalizer->expects(self::never())
            ->method('normalize');

        $actual = $normalizer->normalize($component);

        self::assertEquals([
            'type' => 'FakeType',
            'name' => 'myName',
        ], $actual);
    }

    // endregion normalize ExternalReferences
}
