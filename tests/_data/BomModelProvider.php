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

use CycloneDX\Core\Collections\ComponentRepository;
use CycloneDX\Core\Collections\CopyrightRepository;
use CycloneDX\Core\Collections\ExternalReferenceRepository;
use CycloneDX\Core\Collections\HashDictionary;
use CycloneDX\Core\Collections\LicenseRepository;
use CycloneDX\Core\Collections\PropertyRepository;
use CycloneDX\Core\Collections\ToolRepository;
use CycloneDX\Core\Enums\ComponentType;
use CycloneDX\Core\Enums\ExternalReferenceType;
use CycloneDX\Core\Enums\HashAlgorithm;
use CycloneDX\Core\Models\Bom;
use CycloneDX\Core\Models\Component;
use CycloneDX\Core\Models\ComponentEvidence;
use CycloneDX\Core\Models\ExternalReference;
use CycloneDX\Core\Models\License\LicenseExpression;
use CycloneDX\Core\Models\License\NamedLicense;
use CycloneDX\Core\Models\License\SpdxLicense;
use CycloneDX\Core\Models\Metadata;
use CycloneDX\Core\Models\Property;
use CycloneDX\Core\Models\Tool;
use DateTimeImmutable;
use DateTimeZone;
use Generator;

/**
 * common DataProvider.
 */
abstract class BomModelProvider
{
    /**
     * a set of Bom structures.
     *
     * @return Generator<Bom[]>
     *
     * @psalm-return Generator<string, array{0:Bom}>
     */
    public static function allBomTestData(): Generator
    {
        yield from self::bomPlain();
        yield from self::bomWithAllComponents();
        yield from self::bomWithAllMetadata();
        yield from self::bomWithExternalReferences();
        yield from self::bomWithProperties();
    }

    /**
     * Just a plain BOM.
     *
     * @return Generator<Bom[]>
     *
     * @psalm-return Generator<string, array{0:Bom}>
     *
     * @psalm-suppress MissingThrowsDocblock
     */
    public static function bomPlain(): Generator
    {
        yield 'bom plain' => [new Bom()];
        yield 'bom plain with version' => [(new Bom())->setVersion(23)];
        yield 'bom plain with serialNumber' => [(new Bom())->setSerialNumber('urn:uuid:3e671687-395b-41f5-a30f-a58921a69b79')];
        yield 'bom plain with invalid serialNumber' => [(new Bom())->setSerialNumber('foo bar')];
    }

    /**
     * BOM with externalReferences.
     *
     * @return Generator<Bom[]>
     *
     * @psalm-return Generator<string, array{0:Bom}>
     *
     * @psalm-suppress MissingThrowsDocblock
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
                    new ExternalReference(ExternalReferenceType::Other, '')
                )
            ),
        ];

        yield 'bom with ExternalReferences: malformed url - multiple #' => [
            (new Bom())->setExternalReferences(
                new ExternalReferenceRepository(
                    new ExternalReference(ExternalReferenceType::Other,
                        'https://example.com/something#foo#bar'
                    )
                )
            ),
        ];

        yield 'bom with ExternalReference: email' => [
            (new Bom())->setExternalReferences(
                new ExternalReferenceRepository(
                    new ExternalReference(
                        ExternalReferenceType::MailingList,
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
     * @return Generator<Bom[]>
     *
     * @psalm-return Generator<string, array{0:Bom}>
     */
    public static function bomWithAllComponents(): Generator
    {
        yield from self::bomWithComponentPlain();
        yield from self::bomWithComponentVersion();
        yield from self::bomWithComponentDescription();
        yield from self::bomWithComponentAuthor();
        yield from self::bomWithComponentLicenses();
        yield from self::bomWithComponentCopyright();
        yield from self::bomWithComponentHashAlgorithmsAllKnown();
        yield from self::bomWithComponentWithExternalReferences();
        yield from self::bomWithComponentTypeAllKnown();
        yield from self::bomWithComponentWithProperties();
    }

    /**
     * BOM wil all possible metadata.
     *
     * @return Generator<Bom[]>
     *
     * @psalm-return Generator<string, array{0:Bom}>
     */
    public static function bomWithAllMetadata(): Generator
    {
        yield from self::bomWithMetadataPlain();
        yield from self::bomWithMetadataTimestamp();
        yield from self::bomWithMetadataTools();
        yield from self::bomWithMetadataComponent();
        yield from self::bomWithMetadataProperties();
    }

    /**
     * BOM with one plain component.
     *
     * @return Generator<Bom[]>
     *
     * @psalm-return Generator<string, array{0:Bom}>
     *
     * @psalm-suppress MissingThrowsDocblock
     */
    public static function bomWithComponentPlain(): Generator
    {
        yield 'component: plain' => [
            (new Bom())->setComponents(
                new ComponentRepository(
                    new Component(ComponentType::Library, 'name')
                )
            ),
        ];
    }

    /**
     * BOMs with all ComponentTypes known.
     *
     * @return Generator<Bom[]>
     *
     * @psalm-return Generator<string, array{0:Bom}>
     */
    public static function bomWithComponentTypeAllKnown(): Generator
    {
        $known = array_map(static fn (ComponentType $c) => $c->value, ComponentType::cases());
        yield from self::bomWithComponentTypes(
            ...$known,
            ...BomSpecData::getClassificationEnumForVersion('1.0'),
            ...BomSpecData::getClassificationEnumForVersion('1.1'),
            ...BomSpecData::getClassificationEnumForVersion('1.2'),
            ...BomSpecData::getClassificationEnumForVersion('1.3'),
            ...BomSpecData::getClassificationEnumForVersion('1.4'),
            ...BomSpecData::getClassificationEnumForVersion('1.5'),
        );
    }

    /**
     * BOM with externalReferences.
     *
     * @return Generator<Bom[]>
     *
     * @psalm-return Generator<string, array{0:Bom}>
     *
     * @psalm-suppress MissingThrowsDocblock
     */
    public static function bomWithComponentWithExternalReferences(): Generator
    {
        yield 'component with empty ExternalReferences' => [
            (new Bom())->setComponents(
                new ComponentRepository(
                    (new Component(ComponentType::Library, 'dummy'))
                        ->setExternalReferences(new ExternalReferenceRepository())
                )
            ),
        ];

        foreach (self::externalReferencesForAllTypes() as $label => $extRef) {
            yield "component with $label" => [
                (new Bom())->setComponents(
                    new ComponentRepository(
                        (new Component(ComponentType::Library, 'dummy'))
                            ->setExternalReferences(new ExternalReferenceRepository($extRef))
                    )
                ),
            ];
        }
    }

    /**
     * BOM with externalReferences.
     *
     * @return Generator<Bom[]>
     *
     * @psalm-return Generator<string, array{0:Bom}>
     *
     * @psalm-suppress MissingThrowsDocblock
     */
    public static function bomWithComponentWithProperties(): Generator
    {
        yield 'component with some properties' => [
            (new Bom())->setComponents(
                new ComponentRepository(
                    (new Component(ComponentType::Library, 'dummy'))
                        ->setProperties(new PropertyRepository(
                            new Property('somePropertyName', 'somePropertyValue-1'),
                            new Property('somePropertyName', 'somePropertyValue-2'),
                        ))
                )
            ),
        ];
    }

    /**
     * @return Generator<Bom[]>
     *
     * @psalm-return Generator<string, array{0:Bom}>
     *
     * @psalm-suppress MissingThrowsDocblock
     */
    public static function bomWithComponentTypeSpec10(): Generator
    {
        yield from self::bomWithComponentTypes(...BomSpecData::getClassificationEnumForVersion('1.0'));
    }

    /**
     * @return Generator<Bom[]>
     *
     * @psalm-return Generator<string, array{0:Bom}>
     *
     * @psalm-suppress MissingThrowsDocblock
     */
    public static function bomWithComponentTypeSpec11(): Generator
    {
        yield from self::bomWithComponentTypes(...BomSpecData::getClassificationEnumForVersion('1.1'));
    }

    /**
     * @return Generator<Bom[]>
     *
     * @psalm-return Generator<string, array{0:Bom}>
     *
     * @psalm-suppress MissingThrowsDocblock
     */
    public static function bomWithComponentTypeSpec12(): Generator
    {
        yield from self::bomWithComponentTypes(...BomSpecData::getClassificationEnumForVersion('1.2'));
    }

    /**
     * @return Generator<Bom[]>
     *
     * @psalm-return Generator<string, array{0:Bom}>
     *
     * @psalm-suppress MissingThrowsDocblock
     */
    public static function bomWithComponentTypeSpec13(): Generator
    {
        yield from self::bomWithComponentTypes(...BomSpecData::getClassificationEnumForVersion('1.3'));
    }

    /**
     * @return Generator<Bom[]>
     *
     * @psalm-return Generator<string, array{0:Bom}>
     *
     * @psalm-suppress MissingThrowsDocblock
     */
    public static function bomWithComponentTypeSpec14(): Generator
    {
        yield from self::bomWithComponentTypes(...BomSpecData::getClassificationEnumForVersion('1.4'));
    }

    /**
     * @return Generator<Bom[]>
     *
     * @psalm-return Generator<string, array{0:Bom}>
     *
     * @psalm-suppress MissingThrowsDocblock
     */
    public static function bomWithComponentTypeSpec15(): Generator
    {
        yield from self::bomWithComponentTypes(...BomSpecData::getClassificationEnumForVersion('1.5'));
    }

    /**
     * BOMs with all hash algorithms available in a spec.
     *
     * @return Generator<Bom[]>
     *
     * @psalm-return Generator<string, array{0:Bom}>
     *
     * @psalm-suppress MissingThrowsDocblock
     */
    public static function bomWithComponentTypes(string ...$types): Generator
    {
        $types = array_unique($types, \SORT_STRING);
        foreach ($types as $type) {
            $type = ComponentType::from($type);
            yield "component types: $type->name" => [
                (new Bom())->setComponents(
                    new ComponentRepository(
                        new Component($type, "dummy_$type->name")
                    )
                ),
            ];
        }
    }

    /**
     * BOMs with one component that has one license.
     *
     * @return Generator<Bom[]>
     *
     * @psalm-return Generator<string, array{0:Bom}>
     */
    public static function bomWithComponentLicenses(): Generator
    {
        yield from self::bomWithComponentLicenseId();
        yield from self::bomWithComponentLicenseName();
        yield from self::bomWithComponentLicenseExpression();
        yield from self::bomWithComponentLicenseUrl();
        yield from self::bomWithComponentLicensesMixed();
    }

    /**
     * BOMs with one component that has one license.
     *
     * @return Generator<Bom[]>
     *
     * @psalm-return Generator<string, array{0:Bom}>
     *
     * @psalm-suppress MissingThrowsDocblock
     */
    public static function bomWithComponentLicenseId(): Generator
    {
        yield 'component with valid license id' => [
            (new Bom())->setComponents(new ComponentRepository(
                (new Component(ComponentType::Library, 'name'))
                    ->setLicenses(new LicenseRepository(
                        new SpdxLicense('MIT')
                    ))
            )),
        ];
        yield 'component with unknown license id' => [
            (new Bom())->setComponents(new ComponentRepository(
                (new Component(ComponentType::Library, 'name'))
                    ->setLicenses(new LicenseRepository(
                        new SpdxLicense(uniqid('license', true))
                    ))
            )),
        ];
    }

    /**
     * BOMs with one component that has one license.
     *
     * @return Generator<Bom[]>
     *
     * @psalm-return Generator<string, array{0:Bom}>
     *
     * @psalm-suppress MissingThrowsDocblock
     */
    public static function bomWithComponentLicenseName(): Generator
    {
        $license = 'random '.bin2hex(random_bytes(32));
        yield 'component license: random' => [
            (new Bom())->setComponents(
                new ComponentRepository(
                    (new Component(ComponentType::Library, 'name'))
                        ->setLicenses(
                            new LicenseRepository(
                                new NamedLicense($license)
                            )
                        )
                )
            ),
        ];
    }

    /**
     * @return Generator<Bom[]>
     *
     * @psalm-return Generator<string, array{0:Bom}>
     *
     * @psalm-suppress MissingThrowsDocblock
     */
    public static function bomWithComponentLicenseExpression(): Generator
    {
        yield 'component license expression' => [
            (new Bom())->setComponents(
                new ComponentRepository(
                    (new Component(ComponentType::Library, 'name'))
                        ->setLicenses(
                            new LicenseRepository(
                                new LicenseExpression('(MIT OR Apache-2.0)')
                            )
                        )
                )
            ),
        ];
    }

    /**
     * @return Generator<Bom[]>
     *
     * @psalm-return Generator<string, array{0:Bom}>
     *
     * @psalm-suppress MissingThrowsDocblock
     */
    public static function bomWithComponentLicensesMixed(): Generator
    {
        $licenses = array_map(
            static fn (Bom $bom): SpdxLicense|NamedLicense|LicenseExpression => $bom->getComponents()->getItems()[0]->getLicenses()->getItems()[0],
            array_map(
                static fn (array $args): Bom => $args[0],
                [
                    ...iterator_to_array(self::bomWithComponentLicenseId(), false),
                    ...iterator_to_array(self::bomWithComponentLicenseName(), false),
                    ...iterator_to_array(self::bomWithComponentLicenseExpression(), false),
                ]
            )
        );
        yield 'component license mixed' => [
            (new Bom())->setComponents(
                new ComponentRepository(
                    (new Component(ComponentType::Library, 'name'))
                        ->setLicenses(new LicenseRepository(...$licenses))
                )
            ),
        ];
    }

    /**
     * @return Generator<Bom[]>
     *
     * @psalm-return Generator<string, array{0:Bom}>
     *
     * @psalm-suppress MissingThrowsDocblock
     */
    public static function bomWithComponentLicenseUrl(): Generator
    {
        yield 'component license with URL' => [
            (new Bom())->setComponents(
                new ComponentRepository(
                    (new Component(ComponentType::Library, 'name'))
                        ->setLicenses(
                            new LicenseRepository(
                                (new NamedLicense('some text'))
                                    ->setUrl('https://example.com/license'),
                            )
                        )
                )
            ),
        ];
    }

    /**
     * @return Generator<Bom[]>
     *
     * @psalm-return Generator<string, array{0:Bom}>
     *
     * @psalm-suppress MissingThrowsDocblock
     */
    public static function bomWithComponentCopyright(): Generator
    {
        yield 'component with copyright' => [
            (new Bom())->setComponents(
                new ComponentRepository(
                    (new Component(ComponentType::Library, 'name'))
                        ->setCopyright('(c) 2042 - by me and the gang')
                )
            ),
        ];
    }

    public static function bomWithComponentEvidence(): Generator
    {
        yield 'component with empty evidence' => [
            (new Bom())->setComponents(
                new ComponentRepository(
                    (new Component(ComponentType::Library, 'name'))
                        ->setEvidence(new ComponentEvidence())
                )
            ),
        ];
        yield 'component with license evidence' => [
            (new Bom())->setComponents(
                new ComponentRepository(
                    (new Component(ComponentType::Library, 'name'))
                        ->setEvidence((new ComponentEvidence())
                            ->setLicenses(
                                new LicenseRepository(
                                    (new NamedLicense('UNLICENSE'))
                                    ->setUrl('https://unlicense.org/')
                                )
                            )
                        )
                )
            ),
        ];
        yield 'component with copyright evidence' => [
            (new Bom())->setComponents(
                new ComponentRepository(
                    (new Component(ComponentType::Library, 'name'))
                        ->setEvidence(
                            (new ComponentEvidence())
                            ->setCopyright(new CopyrightRepository('(c) 2042 - by me and the gang'))
                        )
                )
            ),
        ];
    }

    /**
     * BOMs with one component that has a version.
     *
     * @return Generator<Bom[]>
     *
     * @psalm-return Generator<string, array{0:Bom}>
     */
    public static function bomWithComponentVersion(): Generator
    {
        $versions = ['1.0', 'dev-master'];
        foreach ($versions as $version) {
            yield "component version: $version" => [
                (new Bom())->setComponents(
                    new ComponentRepository(
                        (
                            new Component(ComponentType::Library, 'name')
                        )->setVersion($version),
                    )
                ),
            ];
        }
    }

    /** @psalm-return list<string> */
    private static function allHashAlgorithms(): array
    {
        $known = array_map(static fn (HashAlgorithm $ha) => $ha->value, HashAlgorithm::cases());

        return array_values(
            array_unique(
                array_merge(
                    $known,
                    BomSpecData::getHashAlgEnumForVersion('1.0'),
                    BomSpecData::getHashAlgEnumForVersion('1.1'),
                    BomSpecData::getHashAlgEnumForVersion('1.2'),
                    BomSpecData::getHashAlgEnumForVersion('1.3'),
                    BomSpecData::getHashAlgEnumForVersion('1.4'),
                    BomSpecData::getHashAlgEnumForVersion('1.5'),
                ),
                \SORT_STRING
            )
        );
    }

    /**
     * BOMs with all hash algorithms known.
     *
     * @return Generator<Bom[]>
     *
     * @psalm-return Generator<string, array{0:Bom}>
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
     * @return Generator<Bom[]>
     *
     * @psalm-return Generator<string, array{0:Bom}>
     */
    public static function bomWithComponentHashAlgorithmsSpec10(): Generator
    {
        yield from self::bomWithComponentHashAlgorithms(...BomSpecData::getHashAlgEnumForVersion('1.0'));
    }

    /**
     * BOMs with all hash algorithms available in Spec 1.1.
     *
     * @return Generator<Bom[]>
     *
     * @psalm-return Generator<string, array{0:Bom}>
     */
    public static function bomWithComponentHashAlgorithmsSpec11(): Generator
    {
        yield from self::bomWithComponentHashAlgorithms(...BomSpecData::getHashAlgEnumForVersion('1.1'));
    }

    /**
     * BOMs with all hash algorithms available in Spec 1.2.
     *
     * @return Generator<Bom[]>
     *
     * @psalm-return Generator<string, array{0:Bom}>
     */
    public static function bomWithComponentHashAlgorithmsSpec12(): Generator
    {
        yield from self::bomWithComponentHashAlgorithms(...BomSpecData::getHashAlgEnumForVersion('1.2'));
    }

    /**
     * BOMs with all hash algorithms available in Spec 1.3.
     *
     * @return Generator<Bom[]>
     *
     * @psalm-return Generator<string, array{0:Bom}>
     */
    public static function bomWithComponentHashAlgorithmsSpec13(): Generator
    {
        yield from self::bomWithComponentHashAlgorithms(...BomSpecData::getHashAlgEnumForVersion('1.3'));
    }

    /**
     * BOMs with all hash algorithms available in Spec 1.4.
     *
     * @return Generator<Bom[]>
     *
     * @psalm-return Generator<string, array{0:Bom}>
     */
    public static function bomWithComponentHashAlgorithmsSpec14(): Generator
    {
        yield from self::bomWithComponentHashAlgorithms(...BomSpecData::getHashAlgEnumForVersion('1.4'));
    }

    /**
     * BOMs with all hash algorithms available in Spec 1.5.
     *
     * @return Generator<Bom[]>
     *
     * @psalm-return Generator<string, array{0:Bom}>
     */
    public static function bomWithComponentHashAlgorithmsSpec15(): Generator
    {
        yield from self::bomWithComponentHashAlgorithms(...BomSpecData::getHashAlgEnumForVersion('1.5'));
    }

    /**
     * BOMs with all hash algorithms available in a spec.
     *
     * @return Generator<Bom[]>
     *
     * @psalm-return Generator<string, array{0:Bom}>
     */
    public static function bomWithComponentHashAlgorithms(string ...$hashAlgorithms): Generator
    {
        $hashAlgorithms = array_unique($hashAlgorithms, \SORT_STRING);
        foreach ($hashAlgorithms as $hashAlgorithm) {
            $hashAlgorithm = HashAlgorithm::from($hashAlgorithm);
            yield "component hash alg: $hashAlgorithm->name" => [
                (new Bom())->setComponents(
                    new ComponentRepository(
                        (new Component(ComponentType::Library, 'name'))
                            ->setHashes(
                                new HashDictionary([$hashAlgorithm, '12345678901234567890123456789012'])
                            )
                    )
                ),
            ];
        }
    }

    /**
     * BOMs with components that have a description.
     *
     * @return Generator<Bom[]>
     *
     * @psalm-return Generator<string, array{0:Bom}>
     *
     * @psalm-suppress MissingThrowsDocblock
     */
    public static function bomWithComponentDescription(): Generator
    {
        yield 'component description: none' => [
            (new Bom())->setComponents(
                new ComponentRepository(
                    (new Component(ComponentType::Library, 'name'))
                        ->setDescription(null)
                )
            ),
        ];
        yield 'component description: empty' => [
            (new Bom())->setComponents(
                new ComponentRepository(
                    (new Component(ComponentType::Library, 'name'))
                        ->setDescription('')
                )
            ),
        ];
        yield 'component description: random' => [
            (new Bom())->setComponents(
                new ComponentRepository(
                    (new Component(ComponentType::Library, 'name'))
                        ->setDescription(bin2hex(random_bytes(32)))
                )
            ),
        ];
        yield 'component description: spaces' => [
            (new Bom())->setComponents(
                new ComponentRepository(
                    (new Component(ComponentType::Library, 'name'))
                        ->setDescription("\ta  test   ")
                )
            ),
        ];
        yield 'component description: XML special chars' => [
            (new Bom())->setComponents(
                new ComponentRepository(
                    (new Component(ComponentType::Library, 'name'))
                        ->setDescription(
                            'this & that'. // an & that is not an XML entity
                            '<strong>html<strong>'. // things that might cause schema-invalid XML
                            'bar ]]><[CDATA[baz]]> foo' // unexpected CDATA end
                        )
                )
            ),
        ];
    }

    /**
     * BOMs with components that have an author.
     *
     * @return Generator<Bom[]>
     *
     * @psalm-return Generator<string, array{0:Bom}>
     *
     * @psalm-suppress MissingThrowsDocblock
     */
    private static function bomWithComponentAuthor(): Generator
    {
        yield 'component author: none' => [
            (new Bom())->setComponents(
                new ComponentRepository(
                    (new Component(ComponentType::Library, 'name'))
                        ->setAuthor(null)
                )
            ),
        ];
        yield 'component author: empty' => [
            (new Bom())->setComponents(
                new ComponentRepository(
                    (new Component(ComponentType::Library, 'name'))
                        ->setAuthor('')
                )
            ),
        ];
        yield 'component author: random' => [
            (new Bom())->setComponents(
                new ComponentRepository(
                    (new Component(ComponentType::Library, 'name'))
                        ->setAuthor(bin2hex(random_bytes(32)))
                )
            ),
        ];
        yield 'component author: spaces' => [
            (new Bom())->setComponents(
                new ComponentRepository(
                    (new Component(ComponentType::Library, 'name'))
                        ->setAuthor("\ta  test   ")
                )
            ),
        ];
        yield 'component author: XML special chars' => [
            (new Bom())->setComponents(
                new ComponentRepository(
                    (new Component(ComponentType::Library, 'name'))
                        ->setAuthor(
                            'this & that'. // an & that is not an XML entity
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
     * @return Generator<Bom[]>
     *
     * @psalm-return Generator<string, array{0:Bom}>
     */
    private static function bomWithMetadataPlain(): Generator
    {
        yield 'metadata: plain' => [
            (new Bom())->setMetadata(new Metadata()),
        ];
    }

    /**
     * BOMs with metadata with timestamp.
     *
     * @return Generator<Bom[]>
     *
     * @psalm-return Generator<string, array{0:Bom}>
     */
    private static function bomWithMetadataTimestamp(): Generator
    {
        yield 'metadata: 1984-12-25T08:15Z' => [
            (new Bom())->setMetadata((new Metadata())->setTimestamp(
                new DateTimeImmutable('1984-12-25 08:15:00', new DateTimeZone('utc'))
            )),
        ];
        yield 'metadata: Timestamp 2010-01-28T15:00:00-09:00' => [
            (new Bom())->setMetadata((new Metadata())->setTimestamp(
                new DateTimeImmutable('2010-01-28 15:00:00', new DateTimeZone('-09:00'))
            )),
        ];
    }

    /**
     * BOMs with plain metadata that have tools.
     *
     * @return Generator<Bom[]>
     *
     * @psalm-return Generator<string, array{0:Bom}>
     *
     * @psalm-suppress MissingThrowsDocblock
     */
    private static function bomWithMetadataTools(): Generator
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
                                new HashDictionary([HashAlgorithm::MD5, '12345678901234567890123456789012'])
                            )->setExternalReferences(
                                new ExternalReferenceRepository(
                                    new ExternalReference(ExternalReferenceType::Other, 'https://acme.com')
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
     * @return Generator<Bom[]>
     *
     * @psalm-return Generator<string, array{0:Bom}>
     *
     * @psalm-suppress MissingThrowsDocblock
     */
    private static function bomWithMetadataComponent(): Generator
    {
        yield 'metadata: minimal component' => [
            (new Bom())->setMetadata(
                (new Metadata())->setComponent(
                    new Component(
                        ComponentType::Application,
                        'foo'
                    )
                )
            ),
        ];
    }

    /**
     * BOMs with plain metadata that has some properties..
     *
     * @return Generator<Bom[]>
     *
     * @psalm-return Generator<string, array{0:Bom}>
     *
     * @psalm-suppress MissingThrowsDocblock
     */
    private static function bomWithMetadataProperties(): Generator
    {
        yield 'metadata: someProperties' => [
            (new Bom())->setMetadata(
                (new Metadata())->setProperties(
                    new PropertyRepository(
                        new Property('somePropertyName', 'somePropertyValue1-1'),
                        new Property('somePropertyName', 'somePropertyValue1-2')
                    )
                )
            ),
        ];
    }

    /**
     * @return Generator<ExternalReference>
     *
     * @psalm-return Generator<string, ExternalReference>
     */
    public static function externalReferencesForAllTypes(): Generator
    {
        $known = array_map(static fn (ExternalReferenceType $ert) => $ert->value, ExternalReferenceType::cases());
        $all = array_unique(
            array_merge(
                $known,
                BomSpecData::getExternalReferenceTypeForVersion('1.1'),
                BomSpecData::getExternalReferenceTypeForVersion('1.2'),
                BomSpecData::getExternalReferenceTypeForVersion('1.3'),
                BomSpecData::getExternalReferenceTypeForVersion('1.4'),
                BomSpecData::getExternalReferenceTypeForVersion('1.5'),
            )
        );
        foreach ($all as $type) {
            $type = ExternalReferenceType::tryFrom($type);
            if (null !== $type) {
                yield "externalReferenceType: $type->name" => new ExternalReference($type, ".../types/{$type->name}.txt");
            }
        }
    }

    /**
     * BOMs with all hash algorithms known.
     *
     * @return Generator<ExternalReference>
     *
     * @psalm-return Generator<string, ExternalReference>
     */
    public static function externalReferencesForHashAlgorithmsAllKnown(): Generator
    {
        $type = ExternalReferenceType::Other;
        foreach (self::allHashAlgorithms() as $algorithm) {
            $algorithm = HashAlgorithm::from($algorithm);
            yield "externalReferenceHash: $algorithm->name" => (new ExternalReference(
                $type, ".../algorithm/{$algorithm->name}.txt"
            ))->setHashes(new HashDictionary([$algorithm, '12345678901234567890123456789012']));
        }
    }

    /**
     * BOM with properties.
     *
     * @return Generator<Bom[]>
     *
     * @psalm-return Generator<string, array{0:Bom}>
     *
     * @psalm-suppress MissingThrowsDocblock
     */
    public static function bomWithProperties(): Generator
    {
        yield 'Bom with properties' => [
            (new Bom())->setProperties(
                new PropertyRepository(
                    new Property('foo', 'bar'),
                    new Property('baz', '')
                )
            ),
        ];
    }
}
