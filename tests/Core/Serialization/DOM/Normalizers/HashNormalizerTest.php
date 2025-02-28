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

use CycloneDX\Core\Enums\HashAlgorithm;
use CycloneDX\Core\Serialization\DOM\_BaseNormalizer;
use CycloneDX\Core\Serialization\DOM\NormalizerFactory;
use CycloneDX\Core\Serialization\DOM\Normalizers\HashNormalizer;
use CycloneDX\Core\Spec\_SpecProtocol;
use CycloneDX\Tests\_traits\DomNodeAssertionTrait;
use DomainException;
use DOMDocument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(HashNormalizer::class)]
#[CoversClass(_BaseNormalizer::class)]
class HashNormalizerTest extends TestCase
{
    use DomNodeAssertionTrait;

    public function testConstructor(): void
    {
        $factory = $this->createMock(NormalizerFactory::class);
        $normalizer = new HashNormalizer($factory);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }

    public function testNormalize(): void
    {
        $factory = $this->createConfiguredMock(NormalizerFactory::class,
            [
                'getDocument' => new DOMDocument(),
            ]
        );
        $normalizer = new HashNormalizer($factory);
        $factory->method('getSpec')->willReturn(
            $this->createConfiguredMock(
                _SpecProtocol::class,
                [
                    'isSupportedHashAlgorithm' => true,
                    'isSupportedHashContent' => true,
                ]
            )
        );

        $normalized = $normalizer->normalize(HashAlgorithm::SHA_1, 'bar');

        self::assertStringEqualsDomNode('<hash alg="SHA-1">bar</hash>', $normalized);
    }

    public function testNormalizeThrowOnUnsupportedAlgorithm(): void
    {
        $factory = $this->createMock(NormalizerFactory::class);
        $normalizer = new HashNormalizer($factory);
        $factory->method('getSpec')->willReturn(
            $this->createConfiguredMock(
                _SpecProtocol::class,
                [
                    'isSupportedHashAlgorithm' => false,
                    'isSupportedHashContent' => true,
                ]
            )
        );

        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/invalid hash algorithm/i');

        $normalizer->normalize(HashAlgorithm::MD5, 'bar');
    }

    public function testNormalizeThrowOnUnsupportedContent(): void
    {
        $factory = $this->createMock(NormalizerFactory::class);
        $normalizer = new HashNormalizer($factory);
        $factory->method('getSpec')->willReturn(
            $this->createConfiguredMock(
                _SpecProtocol::class,
                [
                    'isSupportedHashAlgorithm' => true,
                    'isSupportedHashContent' => false,
                ]
            )
        );

        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/invalid hash content/i');

        $normalizer->normalize(HashAlgorithm::MD5, 'bar');
    }
}
