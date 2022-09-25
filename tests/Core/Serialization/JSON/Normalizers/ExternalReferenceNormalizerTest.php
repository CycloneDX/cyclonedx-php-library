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

use CycloneDX\Core\Collections\HashDictionary;
use CycloneDX\Core\Models\ExternalReference;
use CycloneDX\Core\Serialization\JSON\NormalizerFactory;
use CycloneDX\Core\Serialization\JSON\Normalizers;
use CycloneDX\Core\Spec\Spec;

/**
 * @covers \CycloneDX\Core\Serialization\JSON\Normalizers\ExternalReferenceNormalizer
 * @covers \CycloneDX\Core\Serialization\JSON\_BaseNormalizer
 */
class ExternalReferenceNormalizerTest extends \PHPUnit\Framework\TestCase
{
    public function testNormalizeTypeAndUrl(): void
    {
        $spec = $this->createMock(Spec::class);
        $normalizerFactory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSPec' => $spec,
        ]);
        $normalizer = new Normalizers\ExternalReferenceNormalizer($normalizerFactory);
        $extRef = $this->createConfiguredMock(ExternalReference::class, [
            'getUrl' => 'someUrl',
            'getType' => 'someType',
            'getComment' => null,
            'getHashes' => $this->createMock(HashDictionary::class),
        ]);

        $spec->expects(self::atLeastOnce())
            ->method('isSupportedExternalReferenceType')
            ->with('someType')
            ->willReturn(true);

        $actual = $normalizer->normalize($extRef);

        self::assertSame([
            'type' => 'someType',
            'url' => 'someUrl',
            // comment omitted
            // hashes omitted
        ], $actual);
    }

    public function testThrowOnUnsupportedRefType(): void
    {
        $spec = $this->createMock(Spec::class);
        $normalizerFactory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSPec' => $spec,
        ]);
        $normalizer = new Normalizers\ExternalReferenceNormalizer($normalizerFactory);
        $extRef = $this->createConfiguredMock(ExternalReference::class, [
            'getUrl' => 'someUrl',
            'getType' => 'someType',
            'getComment' => null,
            'getHashes' => $this->createMock(HashDictionary::class),
        ]);

        $spec->expects(self::atLeastOnce())
            ->method('isSupportedExternalReferenceType')
            ->with('someType')
            ->willReturn(false);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('ExternalReference has unsupported type: someType');

        $normalizer->normalize($extRef);
    }

    public function testNormalizeComment(): void
    {
        $spec = $this->createMock(Spec::class);
        $normalizerFactory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSPec' => $spec,
        ]);
        $normalizer = new Normalizers\ExternalReferenceNormalizer($normalizerFactory);
        $extRef = $this->createConfiguredMock(ExternalReference::class, [
            'getUrl' => 'someUrl',
            'getType' => 'someType',
            'getComment' => 'someComment',
            'getHashes' => $this->createMock(HashDictionary::class),
        ]);

        $spec->method('isSupportedExternalReferenceType')
            ->with('someType')
            ->willReturn(true);

        $actual = $normalizer->normalize($extRef);

        self::assertSame([
            'type' => 'someType',
            'url' => 'someUrl',
            'comment' => 'someComment',
        ], $actual);
    }

    // region normalize hashes

    public function testNormalizeHashes(): void
    {
        $spec = $this->createConfiguredMock(Spec::class, [
            'supportsExternalReferenceHashes' => true,
        ]);
        $hashDictNormalizer = $this->createMock(Normalizers\HashDictionaryNormalizer::class);
        $normalizerFactory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $spec,
            'makeForHashDictionary' => $hashDictNormalizer,
        ]);
        $normalizer = new Normalizers\ExternalReferenceNormalizer($normalizerFactory);
        $extRef = $this->createConfiguredMock(ExternalReference::class, [
            'getUrl' => 'someUrl',
            'getType' => 'someType',
            'getComment' => null,
            'getHashes' => $this->createConfiguredMock(HashDictionary::class, ['count' => 1]),
        ]);

        $spec->method('isSupportedExternalReferenceType')
            ->with('someType')
            ->willReturn(true);
        $hashDictNormalizer->expects(self::once())
            ->method('normalize')
            ->with($extRef->getHashes())
            ->willReturn(['NormalizedHashDictFake']);

        $actual = $normalizer->normalize($extRef);

        self::assertSame([
            'type' => 'someType',
            'url' => 'someUrl',
            'hashes' => ['NormalizedHashDictFake'],
        ], $actual);
    }

    public function testNormalizeHashesOmitIfEmpty(): void
    {
        $spec = $this->createConfiguredMock(Spec::class, [
            'supportsExternalReferenceHashes' => true,
        ]);
        $HashDictionaryNormalizer = $this->createMock(Normalizers\HashDictionaryNormalizer::class);
        $normalizerFactory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $spec,
            'makeForHashDictionary' => $HashDictionaryNormalizer,
        ]);
        $normalizer = new Normalizers\ExternalReferenceNormalizer($normalizerFactory);
        $extRef = $this->createConfiguredMock(ExternalReference::class, [
            'getUrl' => 'someUrl',
            'getType' => 'someType',
            'getComment' => null,
            'getHashes' => $this->createConfiguredMock(HashDictionary::class, ['count' => 0]),
        ]);

        $spec->method('isSupportedExternalReferenceType')
            ->with('someType')
            ->willReturn(true);
        $HashDictionaryNormalizer->expects(self::never())
            ->method('normalize')
            ->with($extRef->getHashes());

        $actual = $normalizer->normalize($extRef);

        self::assertSame([
            'type' => 'someType',
            'url' => 'someUrl',
            // hashes is omitted
        ], $actual);
    }

    public function testNormalizeHashesOmitIfNotSupported(): void
    {
        $spec = $this->createConfiguredMock(Spec::class, [
            'supportsExternalReferenceHashes' => false,
        ]);
        $HashDictionaryNormalizer = $this->createMock(Normalizers\HashDictionaryNormalizer::class);
        $normalizerFactory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $spec,
            'makeForHashDictionary' => $HashDictionaryNormalizer,
        ]);
        $normalizer = new Normalizers\ExternalReferenceNormalizer($normalizerFactory);
        $extRef = $this->createConfiguredMock(ExternalReference::class, [
            'getUrl' => 'someUrl',
            'getType' => 'someType',
            'getComment' => null,
            'getHashes' => $this->createConfiguredMock(HashDictionary::class, ['count' => 1]),
        ]);

        $spec->method('isSupportedExternalReferenceType')
            ->with('someType')
            ->willReturn(true);
        $HashDictionaryNormalizer->expects(self::never())
            ->method('normalize')
            ->with($extRef->getHashes());

        $actual = $normalizer->normalize($extRef);

        self::assertSame([
            'type' => 'someType',
            'url' => 'someUrl',
            // hashes is omitted
        ], $actual);
    }

    // endregion normalize hashes
}
