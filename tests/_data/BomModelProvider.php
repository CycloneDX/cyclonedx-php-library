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

namespace CycloneDX\Tests\_data;

use CycloneDX\Core\Enums\Classification;
use CycloneDX\Core\Enums\ExternalReferenceType;
use CycloneDX\Core\Enums\HashAlgorithm;
use CycloneDX\Core\Models\Bom;
use CycloneDX\Core\Models\Component;
use CycloneDX\Core\Models\ExternalReference;
use CycloneDX\Core\Models\License\DisjunctiveLicenseWithId;
use CycloneDX\Core\Models\License\DisjunctiveLicenseWithName;
use CycloneDX\Core\Models\License\LicenseExpression;
use CycloneDX\Core\Models\Metadata;
use CycloneDX\Core\Models\Tool;
use CycloneDX\Core\Repositories\ComponentRepository;
use CycloneDX\Core\Repositories\LicenseRepository;
use CycloneDX\Core\Repositories\ExternalReferenceRepository;
use CycloneDX\Core\Repositories\HashRepository;
use CycloneDX\Core\Repositories\ToolRepository;
use Generator;

/**
 * common DataProvider.
 */
abstract class BomModelProvider
{
    /**
     * a set of Bom structures.
     *
     * @psalm-return Generator<array{0: Bom}>
     */
    public static function allBomTestData(): Generator
    {
        yield from self::bomPlain();

        yield from self::bomWithAllComponents();

        yield from self::bomWithAllMetadata();

        yield from self::bomWithExternalReferences();
    }

    /**
     * Just a plain BOM.
     *
     * @psalm-return Generator<array{0: Bom}>
     */
    public static function bomPlain(): Generator
    {
        yield 'bom plain' => [new Bom()];
        yield 'bom plain v23' => [(new Bom())->setVersion(23)];
    }

    /**
     * BOM with externalReferences.
     *
     * @psalm-return Generator<array{0: Bom}>
     */
    public static function bomWithExternalReferences(): Generator
    {
        yield 'bom with no ExternalReferences' => [
            (new Bom())->setExternalReferences(
                new ExternalReferenceRepository()
            ),
        ];

        yield 'bom with ExternalReferences: empty string' => [
            (new Bom())->setExternalReferences(
                new ExternalReferenceRepository(
                    new ExternalReference(ExternalReferenceType::OTHER, '')
                )
            ),
        ];

        yield 'bom with ExternalReferences: malformed url - double#' => [
            (new Bom())->setExternalReferences(
                new ExternalReferenceRepository(
                    new ExternalReference(ExternalReferenceType::OTHER,
                        'https://example.com/something#foo#bar'
                    )
                )
            ),
        ];

        yield 'bom with ExternalReference: email' => [
            (new Bom())->setExternalReferences(
                new ExternalReferenceRepository(
                    new ExternalReference(
                        ExternalReferenceType::MAILING_LIST,
                        'mailbox@mailinglist.some-service.local'
                    )
                )
            ),
        ];

        foreach (self::externalReferencesForAllTypes() as $label => $extRef) {
            yield "bom with $label" => [
                (new Bom())->setExternalReferences(
                    new ExternalReferenceRepository($extRef)
                ),
            ];
        }

        foreach (self::externalReferencesForHashAlgorithmsAllKnown() as $label => $extRef) {
            yield "bom with $label" => [
                (new Bom())->setExternalReferences(
                    new ExternalReferenceRepository($extRef)
                ),
            ];
        }
    }

    /**
     * BOM wil all possible components.
     *
     * @psalm-return Generator<array{0: Bom}>
     */
    public static function bomWithAllComponents(): Generator
    {
        yield from self::bomWithComponentPlain();
        yield from self::bomWithComponentVersion();
        yield from self::bomWithComponentDescription();

        yield from self::bomWithComponentLicenseId();
        yield from self::bomWithComponentLicenseName();
        yield from self::bomWithComponentLicenseExpression();
        yield from self::bomWithComponentLicenseUrl();

        yield from self::bomWithComponentHashAlgorithmsAllKnown();
        yield from self::bomWithComponentWithExternalReferences();
        yield from self::bomWithComponentTypeAllKnown();
    }

    /**
     * BOM wil all possible metadata.
     *
     * @psalm-return Generator<array{0: Bom}>
     */
    public static function bomWithAllMetadata(): Generator
    {
        yield from self::bomWithMetaDataPlain();
        yield from self::bomWithMetaDataTools();
        yield from self::bomWithMetaDataComponent();
    }

    /**
     * BOM with one plain component.
     *
     * @psalm-return Generator<array{0: Bom}>
     */
    public static function bomWithComponentPlain(): Generator
    {
        yield 'component: plain' => [
            (new Bom())->setComponents(
                new ComponentRepository(
                    new Component(Classification::LIBRARY, 'name')
                )
            ),
        ];
    }

    /**
     * BOMs with all classification types known.
     *
     * @psalm-return Generator<array{0: Bom}>
     */
    public static function bomWithComponentTypeAllKnown(): Generator
    {
        /** @psalm-var list<string> $known */
        $known = array_values((new \ReflectionClass(Classification::class))->getConstants());
        yield from self::bomWithComponentTypes(
            ...$known,
            ...BomSpecData::getClassificationEnumForVersion('1.0'),
            ...BomSpecData::getClassificationEnumForVersion('1.1'),
            ...BomSpecData::getClassificationEnumForVersion('1.2'),
            ...BomSpecData::getClassificationEnumForVersion('1.3'),
            ...BomSpecData::getClassificationEnumForVersion('1.4')
        );
    }

    /**
     * BOM with externalReferences.
     *
     * @psalm-return Generator<array{0: Bom}>
     */
    public static function bomWithComponentWithExternalReferences(): Generator
    {
        yield 'component with empty ExternalReferences' => [
            (new Bom())->setComponents(
                new ComponentRepository(
                    (new Component(Classification::LIBRARY, 'dummy', 'foo-beta'))
                        ->setExternalReferences(new ExternalReferenceRepository())
                )
            ),
        ];

        foreach (self::externalReferencesForAllTypes() as $label => $extRef) {
            yield "component with $label" => [
                (new Bom())->setComponents(
                    new ComponentRepository(
                        (new Component(Classification::LIBRARY, 'dummy', 'foo-beta'))
                            ->setExternalReferences(new ExternalReferenceRepository($extRef))
                    )
                ),
            ];
        }
    }

    /**
     * @psalm-return Generator<array{0: Bom}>
     */
    public static function bomWithComponentTypeSpec10(): Generator
    {
        yield from self::bomWithComponentTypes(...BomSpecData::getClassificationEnumForVersion('1.0'));
    }

    /**
     * @psalm-return Generator<array{0: Bom}>
     */
    public static function bomWithComponentTypeSpec11(): Generator
    {
        yield from self::bomWithComponentTypes(...BomSpecData::getClassificationEnumForVersion('1.1'));
    }

    /**
     * @psalm-return Generator<array{0: Bom}>
     */
    public static function bomWithComponentTypeSpec12(): Generator
    {
        yield from self::bomWithComponentTypes(...BomSpecData::getClassificationEnumForVersion('1.2'));
    }

    /**
     * @psalm-return Generator<array{0: Bom}>
     */
    public static function bomWithComponentTypeSpec13(): Generator
    {
        yield from self::bomWithComponentTypes(...BomSpecData::getClassificationEnumForVersion('1.3'));
    }

    /**
     * @psalm-return Generator<array{0: Bom}>
     */
    public static function bomWithComponentTypeSpec14(): Generator
    {
        yield from self::bomWithComponentTypes(...BomSpecData::getClassificationEnumForVersion('1.4'));
    }

    /**
     * BOMs with all hash algorithms available in a spec.
     *
     * @psalm-return Generator<array{0: Bom}>
     */
    public static function bomWithComponentTypes(string ...$types): Generator
    {
        $types = array_unique($types, \SORT_STRING);
        foreach ($types as $type) {
            yield "component types: $type" => [
                (new Bom())->setComponents(
                    new ComponentRepository(
                        new Component($type, "dummy_$type", 'v0')
                    )
                ),
            ];
        }
    }

    /**
     * BOMs with one component that has one license.
     *
     * @psalm-return Generator<array{0: Bom}>
     */
    public static function bomWithComponentLicenseId(): Generator
    {
        $license = 'MIT';
        yield "component license: $license" => [
            (new Bom())->setComponents(
                new ComponentRepository(
                    (new Component(Classification::LIBRARY, 'name', 'version'))
                        ->setLicenses(
                            new LicenseRepository(
                                DisjunctiveLicenseWithId::makeValidated(
                                    $license,
                                    SpdxLicenseValidatorSingleton::getInstance()
                                )
                            )
                        )
                )
            ),
        ];
    }

    /**
     * BOMs with one component that has one license.
     *
     * @psalm-return Generator<array{0: Bom}>
     */
    public static function bomWithComponentLicenseName(): Generator
    {
        $license = 'random '.bin2hex(random_bytes(32));
        yield 'component license: random' => [
            (new Bom())->setComponents(
                new ComponentRepository(
                    (new Component(Classification::LIBRARY, 'name', 'version'))
                        ->setLicenses(
                            new LicenseRepository(
                                new DisjunctiveLicenseWithName($license)
                            )
                        )
                )
            ),
        ];
    }

    public static function bomWithComponentLicenseExpression(): Generator
    {
        yield 'component license expression' => [
            (new Bom())->setComponents(
                new ComponentRepository(
                    (new Component(Classification::LIBRARY, 'name', 'version'))
                        ->setLicenses(
                            new LicenseExpression('(Foo or Bar)')
                        )
                )
            ),
        ];
    }

    /**
     * @psalm-return Generator<array{0: Bom}>
     */
    public static function bomWithComponentLicenseUrl(): Generator
    {
        yield 'component license with URL' => [
            (new Bom())->setComponents(
                new ComponentRepository(
                    (new Component(Classification::LIBRARY, 'name', 'version'))
                        ->setLicenses(
                            new LicenseRepository(
                                (new DisjunctiveLicenseWithName('some text'))
                                    ->setUrl('https://example.com/license'),
                            )
                        )
                )
            ),
        ];
    }

    /**
     * BOMs with one component that has a version.
     *
     * @psalm-return Generator<array{0: Bom}>
     */
    public static function bomWithComponentVersion(): Generator
    {
        $versions = ['1.0', 'dev-master'];
        foreach ($versions as $version) {
            yield "component version: $version" => [
                (new Bom())->setComponents(
                    new ComponentRepository(
                        new Component(Classification::LIBRARY, 'name', $version),
                    )
                ),
            ];
        }
    }

    /** @psalm-return list<string> */
    private static function allHashAlgorithms(): array
    {
        /** @psalm-var list<string> $known */
        $known = array_values((new \ReflectionClass(HashAlgorithm::class))->getConstants());

        return array_values(
            array_unique(
                array_merge(
                    $known,
                    BomSpecData::getHashAlgEnumForVersion('1.0'),
                    BomSpecData::getHashAlgEnumForVersion('1.1'),
                    BomSpecData::getHashAlgEnumForVersion('1.2'),
                    BomSpecData::getHashAlgEnumForVersion('1.3'),
                    BomSpecData::getHashAlgEnumForVersion('1.4')
                ),
                \SORT_STRING
            )
        );
    }

    /**
     * BOMs with all hash algorithms known.
     *
     * @psalm-return Generator<array{0: Bom}>
     */
    public static function bomWithComponentHashAlgorithmsAllKnown(): Generator
    {
        yield from self::bomWithComponentHashAlgorithms(
            ...self::allHashAlgorithms()
        );
    }

    /**
     * BOMs with all hash algorithms available in Spec 1.0.
     *
     * @psalm-return Generator<array{0: Bom}>
     */
    public static function bomWithComponentHashAlgorithmsSpec10(): Generator
    {
        yield from self::bomWithComponentHashAlgorithms(...BomSpecData::getHashAlgEnumForVersion('1.0'));
    }

    /**
     * BOMs with all hash algorithms available in Spec 1.1.
     *
     * @psalm-return Generator<array{0: Bom}>
     */
    public static function bomWithComponentHashAlgorithmsSpec11(): Generator
    {
        yield from self::bomWithComponentHashAlgorithms(...BomSpecData::getHashAlgEnumForVersion('1.1'));
    }

    /**
     * BOMs with all hash algorithms available in Spec 1.2.
     *
     * @psalm-return Generator<array{0: Bom}>
     */
    public static function bomWithComponentHashAlgorithmsSpec12(): Generator
    {
        yield from self::bomWithComponentHashAlgorithms(...BomSpecData::getHashAlgEnumForVersion('1.2'));
    }

    /**
     * BOMs with all hash algorithms available in Spec 1.3.
     *
     * @psalm-return Generator<array{0: Bom}>
     */
    public static function bomWithComponentHashAlgorithmsSpec13(): Generator
    {
        yield from self::bomWithComponentHashAlgorithms(...BomSpecData::getHashAlgEnumForVersion('1.3'));
    }

    /**
     * BOMs with all hash algorithms available in Spec 1.4.
     *
     * @psalm-return Generator<array{0: Bom}>
     */
    public static function bomWithComponentHashAlgorithmsSpec14(): Generator
    {
        yield from self::bomWithComponentHashAlgorithms(...BomSpecData::getHashAlgEnumForVersion('1.4'));
    }

    /**
     * BOMs with all hash algorithms available in a spec.
     *
     * @psalm-return Generator<array{0: Bom}>
     */
    public static function bomWithComponentHashAlgorithms(string ...$hashAlgorithms): Generator
    {
        $hashAlgorithms = array_unique($hashAlgorithms, \SORT_STRING);
        foreach ($hashAlgorithms as $hashAlgorithm) {
            yield "component hash alg: $hashAlgorithm" => [
                (new Bom())->setComponents(
                    new ComponentRepository(
                        (new Component(Classification::LIBRARY, 'name', '1.0'))
                            ->setHashes(
                                new HashRepository([$hashAlgorithm => '12345678901234567890123456789012'])
                            )
                    )
                ),
            ];
        }
    }

    /**
     * BOMs with components that have a description.
     *
     * @psalm-return Generator<array{0: Bom}>
     */
    public static function bomWithComponentDescription(): Generator
    {
        yield 'component description: none' => [
            (new Bom())->setComponents(
                new ComponentRepository(
                    (new Component(Classification::LIBRARY, 'name', '1.0'))
                        ->setDescription(null)
                )
            ),
        ];
        yield 'component description: empty' => [
            (new Bom())->setComponents(
                new ComponentRepository(
                    (new Component(Classification::LIBRARY, 'name', '1.0'))
                        ->setDescription('')
                )
            ),
        ];
        yield 'component description: random' => [
            (new Bom())->setComponents(
                new ComponentRepository(
                    (new Component(Classification::LIBRARY, 'name', '1.0'))
                        ->setDescription(bin2hex(random_bytes(32)))
                )
            ),
        ];
        yield 'component description: spaces' => [
            (new Bom())->setComponents(
                new ComponentRepository(
                    (new Component(Classification::LIBRARY, 'name', '1.0'))
                        ->setDescription("\ta  test   ")
                )
            ),
        ];
        yield 'component description: XML special chars' => [
            (new Bom())->setComponents(
                new ComponentRepository(
                    (new Component(Classification::LIBRARY, 'name', '1.0'))
                        ->setDescription(
                            'thisa&that'. // an & that is not a XML entity
                            '<strong>html<strong>'. // things that might cause schema-invalid XML
                            'bar ]]><[CDATA[baz]]> foo' // unexpected CDATA end
                        )
                )
            ),
        ];
    }

    /**
     * BOMs with plain metadata.
     *
     * @psalm-return Generator<array{0: Bom}>
     */
    private static function bomWithMetaDataPlain(): Generator
    {
        yield 'metadata: plain' => [
            (new Bom())->setMetadata(new Metadata()),
        ];
    }

    /**
     * BOMs with plain metadata that have tools.
     *
     * @psalm-return Generator<array{0: Bom}>
     */
    private static function bomWithMetaDataTools(): Generator
    {
        yield 'metadata: empty tools' => [
            (new Bom())->setMetadata(
                (new Metadata())->setTools(new ToolRepository())
            ),
        ];

        yield 'metadata: some tools' => [
            (new Bom())->setMetadata(
                (new Metadata())->setTools(
                    new ToolRepository(
                        new Tool(),
                        (new Tool())
                            ->setVendor('myToolVendor')
                            ->setName('myTool')
                            ->setVersion('toolVersion')
                            ->setHashes(
                                new HashRepository([HashAlgorithm::MD5 => '12345678901234567890123456789012'])
                            )->setExternalReferences(
                                new ExternalReferenceRepository(
                                    new ExternalReference(ExternalReferenceType::OTHER, 'https://acme.com')
                                )
                            ),
                    )
                )
            ),
        ];
    }

    /**
     * BOMs with plain metadata that have a component.
     *
     * @psalm-return Generator<array{0: Bom}>
     */
    private static function bomWithMetaDataComponent(): Generator
    {
        yield 'metadata: minimal component' => [
            (new Bom())->setMetadata(
                (new Metadata())->setComponent(
                    new Component(
                        Classification::APPLICATION,
                        'foo',
                        'bar'
                    )
                )
            ),
        ];
    }

    /**
     * @return Generator<ExternalReference>
     */
    public static function externalReferencesForAllTypes(): Generator
    {
        /** @psalm-var list<string> $known */
        $known = array_values((new \ReflectionClass(ExternalReferenceType::class))->getConstants());
        $all = array_unique(
            array_merge(
                $known,
                BomSpecData::getExternalReferenceTypeForVersion('1.1'),
                BomSpecData::getExternalReferenceTypeForVersion('1.2'),
                BomSpecData::getExternalReferenceTypeForVersion('1.3'),
                BomSpecData::getExternalReferenceTypeForVersion('1.4'),
            )
        );

        foreach ($all as $type) {
            yield "externalReferenceType: $type" => new ExternalReference($type, ".../types/{$type}.txt");
        }
    }

    /**
     * BOMs with all hash algorithms known.
     *
     * @psalm-return Generator<array{0: Bom}>
     */
    public static function externalReferencesForHashAlgorithmsAllKnown(): Generator
    {
        $type = ExternalReferenceType::OTHER;
        foreach (self::allHashAlgorithms() as $algorithm) {
            yield "externalReferenceHash: $algorithm" => (new ExternalReference(
                $type, ".../algorithm/{$algorithm}.txt"
            ))->setHashes(new HashRepository([$algorithm => '12345678901234567890123456789012']));
        }
    }
}
