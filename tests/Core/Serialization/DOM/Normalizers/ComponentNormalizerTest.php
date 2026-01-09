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

use CycloneDX\Core\Collections\ExternalReferenceRepository;
use CycloneDX\Core\Collections\HashDictionary;
use CycloneDX\Core\Collections\LicenseRepository;
use CycloneDX\Core\Collections\PropertyRepository;
use CycloneDX\Core\Enums\ComponentType;
use CycloneDX\Core\Models\BomRef;
use CycloneDX\Core\Models\Component;
use CycloneDX\Core\Models\ComponentEvidence;
use CycloneDX\Core\Models\License\NamedLicense;
use CycloneDX\Core\Serialization\DOM\_BaseNormalizer;
use CycloneDX\Core\Serialization\DOM\NormalizerFactory;
use CycloneDX\Core\Serialization\DOM\Normalizers;
use CycloneDX\Core\Spec\_SpecProtocol;
use CycloneDX\Tests\_traits\DomNodeAssertionTrait;
use DomainException;
use DOMDocument;
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
    use DomNodeAssertionTrait;

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

        $spec->expects(self::once())
            ->method('isSupportedComponentType')
            ->with(ComponentType::Library)
            ->willReturn(false);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/Component .+ has unsupported type/i');

        $normalizer->normalize($component);
    }

    #[DataProvider('dbNormalizeMinimal')]
    public function testNormalizeMinimal(string $expected, bool $requiresComponentVersion): void
    {
        $component = $this->createConfiguredMock(
            Component::class,
            [
                'getName' => 'myName',
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
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            ['getSpec' => $spec, 'getDocument' => new DOMDocument()]
        );
        $normalizer = new Normalizers\ComponentNormalizer($factory);

        $spec->expects(self::once())
            ->method('isSupportedComponentType')
            ->with(ComponentType::Library)
            ->willReturn(true);
        $spec->method('requiresComponentVersion')
            ->willReturn($requiresComponentVersion);

        $actual = $normalizer->normalize($component);

        self::assertStringEqualsDomNode($expected, $actual);
    }

    public static function dbNormalizeMinimal(): Generator
    {
        yield 'mandatory ComponentVersion' => [
            '<component type="library"><name>myName</name><version></version></component>',
            true,
        ];
        yield 'optional ComponentVersion' => [
            '<component type="library"><name>myName</name></component>',
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
                'getLicenses' => $this->createConfiguredMock(LicenseRepository::class, ['count' => 1, 'getItems' => [$this->createMock(NamedLicense::class)]]),
                'getCopyright' => '(c) me and the gang',
                'getEvidence' => $this->createMock(ComponentEvidence::class),
                'getHashes' => $this->createConfiguredMock(HashDictionary::class, ['count' => 1]),
                'getPackageUrl' => 'pkg:generic/FakePURL',
            ]
        );
        $spec = $this->createConfiguredMock(
            _SpecProtocol::class,
            [
                'supportsBomRef' => true,
                'supportsComponentAuthor' => true,
                'supportsComponentEvidence' => true,
            ]
        );
        $licenseRepoNormalizer = $this->createMock(Normalizers\LicenseRepositoryNormalizer::class);
        $hashDictionaryNormalizer = $this->createMock(Normalizers\HashDictionaryNormalizer::class);
        $evidenceNormalizer = $this->createMock(Normalizers\ComponentEvidenceNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'getDocument' => new DOMDocument(),
                'makeForLicenseRepository' => $licenseRepoNormalizer,
                'makeForHashDictionary' => $hashDictionaryNormalizer,
                'makeForComponentEvidence' => $evidenceNormalizer,
            ]
        );
        $normalizer = new Normalizers\ComponentNormalizer($factory);

        $spec->expects(self::once())
            ->method('isSupportedComponentType')
            ->with(ComponentType::Library)
            ->willReturn(true);
        $licenseRepoNormalizer->expects(self::once())
            ->method('normalize')
            ->with($component->getLicenses())
            ->willReturn([$factory->getDocument()->createElement('FakeLicense', 'dummy')]);
        $hashDictionaryNormalizer->expects(self::once())
            ->method('normalize')
            ->with($component->getHashes())
            ->willReturn([$factory->getDocument()->createElement('FakeHash', 'dummy')]);
        $evidenceNormalizer->expects(self::once())
            ->method('normalize')
            ->with($component->getEvidence())
            ->willReturn($factory->getDocument()->createElement('FakeEvidence', 'dummy'));

        $actual = $normalizer->normalize($component);

        self::assertStringEqualsDomNode(
            '<component bom-ref="myBomRef" type="library">'.
            '<author>Jan Kowalleck</author>'.
            '<group>myGroup</group>'.
            '<name>myName</name>'.
            '<version>some-version</version>'.
            '<description>my description</description>'.
            '<hashes><FakeHash>dummy</FakeHash></hashes>'.
            '<licenses><FakeLicense>dummy</FakeLicense></licenses>'.
            '<copyright>(c) me and the gang</copyright>'.
            '<purl>pkg:generic/FakePURL</purl>'.
            '<FakeEvidence>dummy</FakeEvidence>'.
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
                'getType' => ComponentType::Library,
                'getLicenses' => $this->createConfiguredMock(LicenseRepository::class, ['count' => 1]),
            ]
        );
        $spec = $this->createMock(_SpecProtocol::class);
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
            ->with(ComponentType::Library)
            ->willReturn(true);
        $licenseRepoNormalizer->expects(self::once())
            ->method('normalize')
            ->with($component->getLicenses())
            ->willReturn([$factory->getDocument()->createElement('FakeLicense', 'dummy')]);

        $got = $normalizer->normalize($component);

        self::assertStringEqualsDomNode(
            '<component type="library">'.
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
                'getType' => ComponentType::Library,
                'getLicenses' => $this->createConfiguredMock(LicenseRepository::class, ['count' => 0]),
            ]
        );
        $spec = $this->createMock(_SpecProtocol::class);
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
            ->with(ComponentType::Library)
            ->willReturn(true);
        $licenseRepoNormalizer->expects(self::never())
            ->method('normalize');

        $got = $normalizer->normalize($component);

        self::assertStringEqualsDomNode(
            '<component type="library">'.
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
                'getDocument' => new DOMDocument(),
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
            ->willReturn([$factory->getDocument()->createElement('FakeExternalReference', 'dummy')]);

        $actual = $normalizer->normalize($component);

        self::assertStringEqualsDomNode(
            '<component type="library">'.
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
                'getDocument' => new DOMDocument(),
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

        self::assertStringEqualsDomNode(
            '<component type="library">'.
            '<name>myName</name>'.
            '</component>',
            $actual
        );
    }

    // endregion normalize ExternalReferences

    // region properties

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
        $spec = $this->createConfiguredMock(_SpecProtocol::class, [
            'supportsComponentProperties' => true,
        ]);
        $propertiesNormalizer = $this->createMock(Normalizers\PropertyRepositoryNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'getDocument' => new DOMDocument(),
                'makeForPropertyRepository' => $propertiesNormalizer,
            ]
        );
        $normalizer = new Normalizers\ComponentNormalizer($factory);

        $propertiesNormalizer->expects(self::once())
            ->method('normalize')
            ->with($component->getProperties())
            ->willReturn(
                [$factory->getDocument()->createElement('FakeProperties', 'dummy')]);
        $spec->expects(self::once())
            ->method('isSupportedComponentType')
            ->with(ComponentType::Library)
            ->willReturn(true);

        $actual = $normalizer->normalize($component);

        self::assertStringEqualsDomNode(
            '<component type="library"><name>myName</name><properties><FakeProperties>dummy</FakeProperties></properties></component>',
            $actual
        );
    }

    public function testNormalizePropertiesOmitEmpty(): void
    {
        $component = $this->createConfiguredMock(
            Component::class,
            [
                'getName' => 'myName',
                'getType' => ComponentType::Library,
                'getProperties' => $this->createConfiguredMock(PropertyRepository::class, ['count' => 0]),
            ]
        );
        $spec = $this->createConfiguredMock(_SpecProtocol::class, [
            'supportsComponentProperties' => true,
        ]);
        $propertiesNormalizer = $this->createMock(Normalizers\PropertyRepositoryNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'getDocument' => new DOMDocument(),
                'makeForPropertyRepository' => $propertiesNormalizer,
            ]
        );
        $normalizer = new Normalizers\ComponentNormalizer($factory);
        $spec->expects(self::once())
            ->method('isSupportedComponentType')
            ->with(ComponentType::Library)
            ->willReturn(true);

        $actual = $normalizer->normalize($component);

        self::assertStringEqualsDomNode(
            '<component type="library"><name>myName</name></component>',
            $actual
        );
    }

    // endregion properties
}
