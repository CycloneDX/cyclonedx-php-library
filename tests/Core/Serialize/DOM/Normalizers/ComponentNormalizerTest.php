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

use CycloneDX\Core\Collections\ExternalReferenceRepository;
use CycloneDX\Core\Collections\HashDictionary;
use CycloneDX\Core\Collections\LicenseRepository;
use CycloneDX\Core\Models\BomRef;
use CycloneDX\Core\Models\Component;
use CycloneDX\Core\Models\License\DisjunctiveLicenseWithName;
use CycloneDX\Core\Serialize\DOM\NormalizerFactory;
use CycloneDX\Core\Serialize\DOM\Normalizers;
use CycloneDX\Core\Spec\Spec;
use CycloneDX\Tests\_traits\DomNodeAssertionTrait;
use DomainException;
use DOMDocument;
use PackageUrl\PackageUrl;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Serialize\DOM\Normalizers\ComponentNormalizer
 * @covers \CycloneDX\Core\Serialize\DOM\_BaseNormalizer
 * @covers \CycloneDX\Core\_helpers\SimpleDomTrait
 */
class ComponentNormalizerTest extends TestCase
{
    use DomNodeAssertionTrait;

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

        $spec->expects(self::once())
            ->method('isSupportedComponentType')
            ->with('FakeType')
            ->willReturn(false);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/Component .+ has unsupported type/i');

        $normalizer->normalize($component);
    }

    /**
     * @dataProvider dbNormalizeMinimal
     */
    public function testNormalizeMinimal(string $expected, bool $requiresComponentVersion): void
    {
        $component = $this->createConfiguredMock(
            Component::class,
            [
                'getName' => 'myName',
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
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            ['getSpec' => $spec, 'getDocument' => new DOMDocument()]
        );
        $normalizer = new Normalizers\ComponentNormalizer($factory);

        $spec->expects(self::once())
            ->method('isSupportedComponentType')
            ->with('FakeType')
            ->willReturn(true);
        $spec->method('requiresComponentVersion')
            ->willReturn($requiresComponentVersion);

        $actual = $normalizer->normalize($component);

        self::assertStringEqualsDomNode($expected, $actual);
    }

    public function dbNormalizeMinimal(): \Generator
    {
        yield 'mandatory ComponentVersion' => [
            '<component type="FakeType"><name>myName</name><version></version></component>',
            true,
        ];
        yield 'optional ComponentVersion' => [
            '<component type="FakeType"><name>myName</name></component>',
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
                'getLicenses' => $this->createConfiguredMock(LicenseRepository::class, ['count' => 1, 'getItems' => [$this->createMock(DisjunctiveLicenseWithName::class)]]),
                'getHashes' => $this->createConfiguredMock(HashDictionary::class, ['count' => 1]),
                'getPackageUrl' => $this->createConfiguredMock(
                    PackageUrl::class,
                    ['toString' => 'FakePURL', '__toString' => 'FakePURL']
                ),
            ]
        );
        $spec = $this->createConfiguredMock(
            Spec::class,
            [
                'supportsBomRef' => true,
            ]
        );
        $licenseRepoNormalizer = $this->createMock(Normalizers\LicenseRepositoryNormalizer::class);
        $HashDictionaryNormalizer = $this->createMock(Normalizers\HashDictionaryNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'getDocument' => new DOMDocument(),
                'makeForLicenseRepository' => $licenseRepoNormalizer,
                'makeForHashDictionary' => $HashDictionaryNormalizer,
            ]
        );
        $normalizer = new Normalizers\ComponentNormalizer($factory);

        $spec->expects(self::once())
            ->method('isSupportedComponentType')
            ->with('FakeType')
            ->willReturn(true);
        $licenseRepoNormalizer->expects(self::once())
            ->method('normalize')
            ->with($component->getLicenses())
            ->willReturn([$factory->getDocument()->createElement('FakeLicense', 'dummy')]);
        $HashDictionaryNormalizer->expects(self::once())
            ->method('normalize')
            ->with($component->getHashes())
            ->willReturn([$factory->getDocument()->createElement('FakeHash', 'dummy')]);

        $actual = $normalizer->normalize($component);

        self::assertStringEqualsDomNode(
            '<component bom-ref="myBomRef" type="FakeType">'.
            '<group>myGroup</group>'.
            '<name>myName</name>'.
            '<version>some-version</version>'.
            '<description>my description</description>'.
            '<hashes><FakeHash>dummy</FakeHash></hashes>'.
            '<licenses><FakeLicense>dummy</FakeLicense></licenses>'.
            '<purl>FakePURL</purl>'.
            '</component>',
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
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'getDocument' => new DOMDocument(),
                'makeForLicenseRepository' => $licenseRepoNormalizer,
            ]
        );
        $normalizer = new Normalizers\ComponentNormalizer($factory);

        $spec->expects(self::once())
            ->method('isSupportedComponentType')
            ->with('FakeType')
            ->willReturn(true);
        $licenseRepoNormalizer->expects(self::once())
            ->method('normalize')
            ->with($component->getLicenses())
            ->willReturn([$factory->getDocument()->createElement('FakeLicense', 'dummy')]);

        $got = $normalizer->normalize($component);

        self::assertStringEqualsDomNode(
            '<component type="FakeType">'.
            '<name>myName</name>'.
            '<licenses><FakeLicense>dummy</FakeLicense></licenses>'.
            '</component>',
            $got
        );
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
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'getDocument' => new DOMDocument(),
                'makeForLicenseRepository' => $licenseRepoNormalizer,
            ]
        );
        $normalizer = new Normalizers\ComponentNormalizer($factory);

        $spec->expects(self::once())
            ->method('isSupportedComponentType')
            ->with('FakeType')
            ->willReturn(true);
        $licenseRepoNormalizer->expects(self::never())
            ->method('normalize');

        $got = $normalizer->normalize($component);

        self::assertStringEqualsDomNode(
            '<component type="FakeType">'.
            '<name>myName</name>'.
            '</component>',
            $got
        );
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
                'getDocument' => new DOMDocument(),
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
            ->willReturn([$factory->getDocument()->createElement('FakeExternalReference', 'dummy')]);

        $actual = $normalizer->normalize($component);

        self::assertStringEqualsDomNode(
            '<component type="FakeType">'.
            '<name>myName</name>'.
            '<externalReferences><FakeExternalReference>dummy</FakeExternalReference></externalReferences>'.
            '</component>',
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
                'getDocument' => new DOMDocument(),
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

        self::assertStringEqualsDomNode(
            '<component type="FakeType">'.
            '<name>myName</name>'.
            '</component>',
            $actual
        );
    }

    // endregion normalize ExternalReferences
}
