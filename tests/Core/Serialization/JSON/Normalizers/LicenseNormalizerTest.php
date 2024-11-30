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

namespace CycloneDX\Tests\Core\Serialization\JSON\Normalizers;

use CycloneDX\Core\Models\License\LicenseExpression;
use CycloneDX\Core\Models\License\NamedLicense;
use CycloneDX\Core\Models\License\SpdxLicense;
use CycloneDX\Core\Serialization\JSON\_BaseNormalizer;
use CycloneDX\Core\Serialization\JSON\NormalizerFactory;
use CycloneDX\Core\Serialization\JSON\Normalizers\LicenseNormalizer;
use CycloneDX\Core\Spec\_SpecProtocol;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(LicenseNormalizer::class)]
#[CoversClass(_BaseNormalizer::class)]
class LicenseNormalizerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @psalm-param class-string<LicenseExpression|SpdxLicense|NamedLicense> $licenseClass
     * @psalm-param array<string, mixed> $licenseMockConf
     */
    #[DataProvider('dpNormalize')]
    public function testNormalize(string $licenseClass, array $licenseMockConf, array $expected): void
    {
        /** @var (LicenseExpression|SpdxLicense|NamedLicense)&\PHPUnit\Framework\MockObject\MockObject */
        $license = $this->createConfiguredMock($licenseClass, $licenseMockConf);
        $spec = $this->createMock(_SpecProtocol::class);
        if ($license instanceof SpdxLicense) {
            $spec->method('isSupportedLicenseIdentifier')
                ->with($license->getId())
                ->willReturn(true);
        }
        $factory = $this->createConfiguredMock(NormalizerFactory::class, ['getSpec' => $spec]);
        $normalizer = new LicenseNormalizer($factory);

        $actual = $normalizer->normalize($license);

        self::assertSame($expected, $actual);
    }

    public function testNormalizeUnsupportedLicenseId(): void
    {
        $license = $this->createConfiguredMock(SpdxLicense::class, ['getId' => 'MIT']);
        $spec = $this->createConfiguredMock(_SpecProtocol::class, ['isSupportedLicenseIdentifier' => false]);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, ['getSpec' => $spec]);
        $normalizer = new LicenseNormalizer($factory);

        $actual = $normalizer->normalize($license);

        self::assertSame(
            ['license' => ['name' => 'MIT']],
            $actual);
    }

    public static function dpNormalize(): \Generator
    {
        yield 'license expression' => [
            LicenseExpression::class, [
                'getExpression' => 'MIT OR Apache-2.0',
            ],
            ['expression' => 'MIT OR Apache-2.0'],
        ];
        yield 'SPDX license' => [
            SpdxLicense::class, [
                'getId' => 'MIT',
                'getUrl' => 'https://foo.bar',
            ],
            [
                'license' => [
                    'id' => 'MIT',
                    'url' => 'https://foo.bar',
                ],
            ],
        ];
        yield 'named license' => [
            NamedLicense::class, [
                'getName' => 'copyright by the crew',
                'getUrl' => 'https://foo.bar',
            ],
            [
                'license' => [
                    'name' => 'copyright by the crew',
                    'url' => 'https://foo.bar',
                ],
            ],
        ];
    }
}
