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
use CycloneDX\Core\Models\License\NamedLicense;
use CycloneDX\Core\Serialization\DOM\NormalizerFactory;
use CycloneDX\Core\Serialization\DOM\Normalizers;
use CycloneDX\Core\Serialization\DOM\Normalizers\PropertyRepositoryNormalizer;
use CycloneDX\Core\Spec\Spec;
use CycloneDX\Tests\_traits\DomNodeAssertionTrait;
use DomainException;
use DOMDocument;
use Generator;
use PackageUrl\PackageUrl;
use PHPUnit\Framework\TestCase;


#[\PHPUnit\Framework\Attributes\CoversClass(\CycloneDX\Core\Serialization\DOM\Normalizers\ComponentNormalizer::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\CycloneDX\Core\Serialization\DOM\_BaseNormalizer::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\CycloneDX\Core\_helpers\SimpleDomTrait::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(\CycloneDX\Core\Models\BomRef::class)]
class ComponentNormalizerTest extends TestCase
{
    use DomNodeAssertionTrait;

    public function testNormalizeThrowsOnUnsupportedType(): void
    {
        $component = $this->createConfiguredMock(
            Component::class,
            [
                'getName' => 'foo',
                'getType' => ComponentType::LIBRARY,
                'getVersion' => 'v1.33.7',
            ]
        );
        $spec = $this->createMock(Spec::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, ['getSpec' => $spec]);
        $normalizer = new Normalizers\ComponentNormalizer($factory);

        $spec->expects(self::once())
            ->method('isSupportedComponentType')
            ->with(ComponentType::LIBRARY)
            ->willReturn(false);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/Component .+ has unsupported type/i');

        $normalizer->normalize($component);
    }

     #[\PHPUnit\Framework\Attributes\DataProvider('dbNormalizeMinimal')]

    public function testNormalizeMinimal(string $expected, bool $requiresComponentVersion): void
    {
        $component = $this->createConfiguredMock(
            Component::class,
            [
                'getName' => 'myName',
                'getVersion' => null,
                'getType' => ComponentType::LIBRARY,
                'getGroup' => null,
                'getDescription' => null,
                'getAuthor' => null,
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
            ->with(ComponentType::LIBRARY)
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
                'getType' => ComponentType::LIBRARY,
                'getGroup' => 'myGroup',
                'getDescription' => 'my description',
                'getAuthor' => 'Jan Kowalleck',
                'getLicenses' => $this->createConfiguredMock(LicenseRepository::class, ['count' => 1, 'getItems' => [$this->createMock(NamedLicense::class)]]),
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
                'supportsComponentAuthor' => true,
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
            ->with(ComponentType::LIBRARY)
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
            '<component bom-ref="myBomRef" type="library">'.
            '<author>Jan Kowalleck</author>'.
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
                'getType' => ComponentType::LIBRARY,
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
            ->with(ComponentType::LIBRARY)
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
                'getType' => ComponentType::LIBRARY,
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
            ->with(ComponentType::LIBRARY)
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
                'getType' => ComponentType::LIBRARY,
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
            ->with(ComponentType::LIBRARY)
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
                'getType' => ComponentType::LIBRARY,
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
            ->with(ComponentType::LIBRARY)
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

    public function testNormalizeProperties(): void
    {
        $component = $this->createConfiguredMock(
            Component::class,
            [
                'getName' => 'myName',
                'getType' => ComponentType::LIBRARY,
                'getProperties' => $this->createConfiguredMock(PropertyRepository::class, ['count' => 2]),
            ]
        );
        $spec = $this->createConfiguredMock(Spec::class, [
            'supportsComponentProperties' => true,
        ]);
        $propertiesNormalizer = $this->createMock(PropertyRepositoryNormalizer::class);
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
            ->with(ComponentType::LIBRARY)
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
                'getType' => ComponentType::LIBRARY,
                'getProperties' => $this->createConfiguredMock(PropertyRepository::class, ['count' => 0]),
            ]
        );
        $spec = $this->createConfiguredMock(Spec::class, [
            'supportsComponentProperties' => true,
        ]);
        $propertiesNormalizer = $this->createMock(PropertyRepositoryNormalizer::class);
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
            ->with(ComponentType::LIBRARY)
            ->willReturn(true);

        $actual = $normalizer->normalize($component);

        self::assertStringEqualsDomNode(
            '<component type="library"><name>myName</name></component>',
            $actual
        );
    }
}
