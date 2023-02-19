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
use CycloneDX\Core\Serialization\JSON;
use CycloneDX\Core\Serialization\JsonSerializer;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(JsonSerializer::class)]
#[UsesClass(BaseSerializer::class)]
#[UsesClass(BomRefDiscriminator::class)]
class JsonSerializerTest extends TestCase
{
    #[DataProvider('dpSerializeStructure')]
    public function testSerialize(int $jsonEncodeFlags, ?bool $prettyPrint, array $normalized, string $expected): void
    {
        $bom = $this->createStub(Bom::class);
        $bomNormalizer = $this->createMock(JSON\Normalizers\BomNormalizer::class);
        $normalizerFactory = $this->createConfiguredMock(JSON\NormalizerFactory::class, [
            'makeForBom' => $bomNormalizer,
        ]);
        $bomNormalizer->method('normalize')
            ->with($bom)
            ->willReturn($normalized);
        $serializer = new JsonSerializer($normalizerFactory, $jsonEncodeFlags);

        $actual = $serializer->serialize($bom, $prettyPrint);

        self::assertSame($expected, $actual);
    }

    public static function dpSerializeStructure(): Generator
    {
        $normalizedDummy = uniqid('normalized', true);
        $normalizedDummyJson = json_encode($normalizedDummy);

        yield 'plain' => [
            0, null,
            ['normalized' => $normalizedDummy],
            '{"normalized":'.$normalizedDummyJson.'}',
        ];

        yield 'full float must not become an integer' => [
            0, null,
            ['normalized' => 23.0],
            '{"normalized":23.0}',
        ];

        yield 'JSON_UNESCAPED_SLASHES is supported' => [
            \JSON_UNESCAPED_SLASHES, null,
            ['normalized' => "some/slash/$normalizedDummy"],
            '{"normalized":"some/slash/'.$normalizedDummy.'"}',
        ];

        yield 'pretty=false' => [
            0, false,
            ['normalized' => $normalizedDummy],
            '{"normalized":'.$normalizedDummyJson.'}',
        ];

        yield 'pretty=true' => [
            0, true,
            ['normalized' => $normalizedDummy],
            '{'."\n".'    "normalized": '.$normalizedDummyJson."\n}",
        ];

        yield 'pretty=null, JSON_PRETTY_PRINT' => [
            \JSON_PRETTY_PRINT, null,
            ['normalized' => $normalizedDummy],
            '{'."\n".'    "normalized": '.$normalizedDummyJson."\n}",
        ];

        yield 'pretty=false, JSON_PRETTY_PRINT' => [
            \JSON_PRETTY_PRINT, false,
            ['normalized' => $normalizedDummy],
            '{"normalized":'.$normalizedDummyJson.'}',
        ];

        yield 'pretty=true, JSON_PRETTY_PRINT' => [
            \JSON_PRETTY_PRINT, true,
            ['normalized' => $normalizedDummy],
            '{'."\n".'    "normalized": '.$normalizedDummyJson."\n}",
        ];
    }
}
