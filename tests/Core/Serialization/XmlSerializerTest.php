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

namespace CycloneDX\Tests\Core\Serialization;

use CycloneDX\Core\Models\Bom;
use CycloneDX\Core\Serialization\BaseSerializer;
use CycloneDX\Core\Serialization\BomRefDiscriminator;
use CycloneDX\Core\Serialization\DOM;
use CycloneDX\Core\Serialization\XmlSerializer;
use DOMDocument;
use DOMElement;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(XmlSerializer::class)]
#[UsesClass(BaseSerializer::class)]
#[UsesClass(BomRefDiscriminator::class)]
class XmlSerializerTest extends TestCase
{
    #[DataProvider('dpSerializeStructure')]
    public function testSerialize(string $xmlVersion, string $xmlEncoding, ?bool $prettyPrint, DOMElement $normalized, string $expected): void
    {
        $bom = $this->createStub(Bom::class);
        $bomNormalizer = $this->createMock(DOM\Normalizers\BomNormalizer::class);
        $normalizerFactory = $this->createConfiguredMock(DOM\NormalizerFactory::class, [
            'makeForBom' => $bomNormalizer,
        ]);
        $bomNormalizer->method('normalize')
            ->with($bom)
            ->willReturn($normalized);
        $serializer = new XmlSerializer($normalizerFactory, $xmlVersion, $xmlEncoding);

        $actual = $serializer->serialize($bom, $prettyPrint);

        self::assertSame($expected, $actual);
    }

    public static function dpSerializeStructure(): Generator
    {
        $doc = new DOMDocument();
        $dummyText = uniqid('normalized', true);
        $xmlNS = 'normalized';
        $normalizedDummy = $doc->createElementNS($xmlNS, 'dummyElement');
        $normalizedDummy->appendChild($doc->createElement($xmlNS, $dummyText));

        yield 'plain xml1.1 ISO-8859-1' => [
            '1.1', 'ISO-8859-1', null,
            $normalizedDummy,
            <<<"XML"
                <?xml version="1.1" encoding="ISO-8859-1"?>
                <dummyElement xmlns="normalized"><normalized>$dummyText</normalized></dummyElement>\n
                XML,
        ];

        yield 'pretty=false' => [
            '1.0', 'UTF-8', false,
            $normalizedDummy,
            <<<"XML"
                <?xml version="1.0" encoding="UTF-8"?>
                <dummyElement xmlns="normalized"><normalized>$dummyText</normalized></dummyElement>\n
                XML,
        ];

        yield 'pretty=true' => [
            '1.0', 'UTF-8', true,
            $normalizedDummy,
            <<<"XML"
                <?xml version="1.0" encoding="UTF-8"?>
                <dummyElement xmlns="normalized">
                  <normalized>$dummyText</normalized>
                </dummyElement>\n
                XML,
        ];
    }
}
