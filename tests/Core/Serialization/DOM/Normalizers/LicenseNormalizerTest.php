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

use CycloneDX\Core\Models\License\LicenseExpression;
use CycloneDX\Core\Models\License\NamedLicense;
use CycloneDX\Core\Models\License\SpdxLicense;
use CycloneDX\Core\Serialization\DOM\NormalizerFactory;
use CycloneDX\Core\Serialization\DOM\Normalizers\LicenseNormalizer;
use CycloneDX\Core\Spec\Spec;
use CycloneDX\Tests\_traits\DomNodeAssertionTrait;
use DOMDocument;
use Generator;

#[\PHPUnit\Framework\Attributes\CoversClass(\CycloneDX\Core\Serialization\DOM\Normalizers\LicenseNormalizer::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\CycloneDX\Core\Serialization\DOM\_BaseNormalizer::class)]
class LicenseNormalizerTest extends \PHPUnit\Framework\TestCase
{
    use DomNodeAssertionTrait;

    /**
     * @psalm-param class-string<LicenseExpression|SpdxLicense|NamedLicense> $licenseClass
     * @psalm-param array<string,mixed> $licenseConfig
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dpNormalize')]
    public function testNormalize(string $licenseClass, array $licenseConfig, string $expectedXML): void
    {
        /** @var (LicenseExpression|SpdxLicense|NamedLicense)&\PHPUnit\Framework\MockObject\MockObject */
        $license = $this->createConfiguredMock($licenseClass, $licenseConfig);
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

    public static function dpNormalize(): Generator
    {
        yield 'license expression' => [
            LicenseExpression::class, [
                'getExpression' => 'MIT OR Apache-2.0',
            ],
            '<expression>MIT OR Apache-2.0</expression>',
        ];
        yield 'SPDX license' => [
            SpdxLicense::class, [
                'getId' => 'MIT',
                'getUrl' => 'https://foo.bar',
            ],
            '<license>'.
            '<id>MIT</id>'.
            '<url>https://foo.bar</url>'.
            '</license>',
        ];
        yield 'named license' => [
            NamedLicense::class, [
                'getName' => 'copyright by the crew',
                'getUrl' => 'https://foo.bar',
            ],
            '<license>'.
            '<name>copyright by the crew</name>'.
            '<url>https://foo.bar</url>'.
            '</license>',
        ];
    }
}
