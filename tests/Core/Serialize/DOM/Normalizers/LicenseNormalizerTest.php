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

use CycloneDX\Core\Models\License\DisjunctiveLicenseWithId;
use CycloneDX\Core\Models\License\DisjunctiveLicenseWithName;
use CycloneDX\Core\Models\License\LicenseExpression;
use CycloneDX\Core\Serialize\DOM\NormalizerFactory;
use CycloneDX\Core\Serialize\DOM\Normalizers\LicenseNormalizer;
use CycloneDX\Core\Spec\Spec;
use CycloneDX\Tests\_traits\DomNodeAssertionTrait;
use DOMDocument;

/**
 * @covers \CycloneDX\Core\Serialize\DOM\Normalizers\LicenseNormalizer
 * @covers \CycloneDX\Core\Serialize\DOM\_BaseNormalizer
 */
class LicenseNormalizerTest extends \PHPUnit\Framework\TestCase
{
    use DomNodeAssertionTrait;

    /**
     * @dataProvider dpNormalize
     */
    public function testNormalize(LicenseExpression|DisjunctiveLicenseWithId|DisjunctiveLicenseWithName $license, string $expectedXML): void
    {
        $spec = $this->createMock(Spec::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'getDocument' => new DOMDocument(),
            ]
        );
        $normalizer = new LicenseNormalizer($factory);

        $actual = $normalizer->normalize($license);

        self::assertStringEqualsDomNode($expectedXML, $actual);
    }

    public function dpNormalize(): \Generator
    {
        yield 'license expression' => [
            $this->createConfiguredMock(LicenseExpression::class, [
                'getExpression' => 'MIT OR Apache-2.0',
            ]),
            '<expression>MIT OR Apache-2.0</expression>',
        ];
        yield 'SPDX license' => [
            $this->createConfiguredMock(DisjunctiveLicenseWithId::class, [
                'getId' => 'MIT',
                'getUrl' => 'https://foo.bar',
            ]),
            '<license>'.
            '<id>MIT</id>'.
            '<url>https://foo.bar</url>'.
            '</license>',
        ];
        yield 'named license' => [
            $this->createConfiguredMock(DisjunctiveLicenseWithName::class, [
                'getName' => 'copyright by the crew',
                'getUrl' => 'https://foo.bar',
            ]),
            '<license>'.
            '<name>copyright by the crew</name>'.
            '<url>https://foo.bar</url>'.
            '</license>',
        ];
    }
}
