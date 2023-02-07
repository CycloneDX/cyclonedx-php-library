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

use CycloneDX\Core\Collections\HashDictionary;
use CycloneDX\Core\Enums\HashAlgorithm;
use CycloneDX\Core\Serialization\DOM\NormalizerFactory;
use CycloneDX\Core\Serialization\DOM\Normalizers\HashDictionaryNormalizer;
use CycloneDX\Core\Serialization\DOM\Normalizers\HashNormalizer;
use DomainException;
use DOMDocument;
use DOMElement;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\CycloneDX\Core\Serialization\DOM\Normalizers\HashDictionaryNormalizer::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\CycloneDX\Core\Serialization\DOM\_BaseNormalizer::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\CycloneDX\Core\_helpers\SimpleDomTrait::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(\CycloneDX\Core\Serialization\DOM\Normalizers\HashNormalizer::class)]
class HashDictionaryNormalizerTest extends TestCase
{
    public function testConstructor(): void
    {
        $factory = $this->createMock(NormalizerFactory::class);
        $normalizer = new HashDictionaryNormalizer($factory);
        self::assertSame($factory, $normalizer->getNormalizerFactory());
    }

    public function testNormalize(): void
    {
        $hashNormalizer = $this->createMock(HashNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'makeForHash' => $hashNormalizer,
                'getDocument' => new DOMDocument(),
            ]
        );
        $dummy1 = $this->createStub(DOMElement::class);
        $dummy2 = $this->createStub(DOMElement::class);
        $normalizer = new HashDictionaryNormalizer($factory);
        $repo = $this->createStub(HashDictionary::class);
        $repo->method('getItems')->willReturn([[HashAlgorithm::MD5, 'content1'], [HashAlgorithm::SHA_1, 'content2']]);

        $hashNormalizer->expects(self::exactly(2))
            ->method('normalize')
            ->willReturnMap([
                [HashAlgorithm::MD5, 'content1', $dummy1],
                [HashAlgorithm::SHA_1, 'content2', $dummy2],
            ]);

        $normalized = $normalizer->normalize($repo);

        self::assertSame([$dummy1, $dummy2], $normalized);
    }

    public function testNormalizeSkipOnThrow(): void
    {
        $hashNormalizer = $this->createMock(HashNormalizer::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, ['makeForHash' => $hashNormalizer]);
        $normalizer = new HashDictionaryNormalizer($factory);

        $repo = $this->createConfiguredMock(
            HashDictionary::class,
            [
                'getItems' => [
                    [HashAlgorithm::MD5, 'cont1'],
                    [HashAlgorithm::SHA_1, 'cont2'],
                    [HashAlgorithm::SHA_256, 'cont3'],
            ],
            ]
        );

        $hashNormalizer->expects(self::exactly(3))
            ->method('normalize')
            ->willThrowException(new DomainException());

        $got = $normalizer->normalize($repo);

        self::assertSame([], $got);
    }
}
