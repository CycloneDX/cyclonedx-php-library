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
use CycloneDX\Core\Enums\ExternalReferenceType;
use CycloneDX\Core\Models\ExternalReference;
use CycloneDX\Core\Serialization\JSON\_BaseNormalizer;
use CycloneDX\Core\Serialization\JSON\NormalizerFactory;
use CycloneDX\Core\Serialization\JSON\Normalizers;
use CycloneDX\Core\Spec\_SpecProtocol;
use CycloneDX\Tests\_data\AnyUriData;
use DomainException;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

#[CoversClass(Normalizers\ExternalReferenceNormalizer::class)]
#[CoversClass(_BaseNormalizer::class)]
class ExternalReferenceNormalizerTest extends TestCase
{
    public function testNormalizeTypeAndUrl(): void
    {
        $spec = $this->createMock(_SpecProtocol::class);
        $normalizerFactory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSPec' => $spec,
        ]);
        $normalizer = new Normalizers\ExternalReferenceNormalizer($normalizerFactory);
        $extRef = $this->createConfiguredMock(ExternalReference::class, [
            'getUrl' => 'someUrl',
            'getType' => ExternalReferenceType::BOM,
            'getComment' => null,
            'getHashes' => $this->createMock(HashDictionary::class),
        ]);

        $spec->expects(self::atLeastOnce())
            ->method('isSupportedExternalReferenceType')
            ->with(ExternalReferenceType::BOM)
            ->willReturn(true);

        $actual = $normalizer->normalize($extRef);

        self::assertSame([
            'type' => 'bom',
            'url' => 'someUrl',
            // comment omitted
            // hashes omitted
        ], $actual);
    }

    public function testNormalizeTypeConvertIfNotSupported(): void
    {
        $spec = $this->createMock(_SpecProtocol::class);
        $normalizerFactory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSPec' => $spec,
        ]);
        $normalizer = new Normalizers\ExternalReferenceNormalizer($normalizerFactory);
        $extRef = $this->createConfiguredMock(ExternalReference::class, [
            'getType' => ExternalReferenceType::BOM,
            'getUrl' => 'someUrl',
        ]);

        $spec->expects(self::exactly(2))
            ->method('isSupportedExternalReferenceType')
            ->willReturnMap([
                [ExternalReferenceType::BOM, false],
                [ExternalReferenceType::Other, true],
            ]);

        $actual = $normalizer->normalize($extRef);

        self::assertSame([
            'type' => 'other',
            'url' => 'someUrl',
            // comment omitted
            // hashes omitted
        ], $actual);
    }

    public function testThrowOnUnsupportedRefType(): void
    {
        $spec = $this->createMock(_SpecProtocol::class);
        $normalizerFactory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSPec' => $spec,
        ]);
        $normalizer = new Normalizers\ExternalReferenceNormalizer($normalizerFactory);
        $extRef = $this->createConfiguredMock(ExternalReference::class, [
            'getUrl' => 'someUrl',
            'getType' => ExternalReferenceType::BOM,
            'getComment' => null,
            'getHashes' => $this->createMock(HashDictionary::class),
        ]);

        $spec->expects(self::exactly(2))
            ->method('isSupportedExternalReferenceType')
            ->willReturnMap([
                [ExternalReferenceType::BOM, false],
                [ExternalReferenceType::Other, false],
            ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('ExternalReference has unsupported type: BOM');

        $normalizer->normalize($extRef);
    }

    #[DataProvider('dpThrowOnUnsupportedUrl')]
    public function testThrowOnUnsupportedUrl(string $unsupportedURL): void
    {
        $spec = $this->createMock(_SpecProtocol::class);
        $normalizerFactory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSPec' => $spec,
        ]);
        $normalizer = new Normalizers\ExternalReferenceNormalizer($normalizerFactory);
        $extRef = $this->createConfiguredMock(ExternalReference::class, [
            'getUrl' => $unsupportedURL,
        ]);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage("invalid to format 'IriReference': $unsupportedURL");

        $normalizer->normalize($extRef);
    }

    public static function dpThrowOnUnsupportedUrl(): Generator
    {
        yield 'multiple #' => ['https://example.com#foo#bar'];
    }

    public function testNormalizeComment(): void
    {
        $spec = $this->createMock(_SpecProtocol::class);
        $normalizerFactory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSPec' => $spec,
        ]);
        $normalizer = new Normalizers\ExternalReferenceNormalizer($normalizerFactory);
        $extRef = $this->createConfiguredMock(ExternalReference::class, [
            'getUrl' => 'someUrl',
            'getType' => ExternalReferenceType::BOM,
            'getComment' => 'someComment',
            'getHashes' => $this->createMock(HashDictionary::class),
        ]);

        $spec->method('isSupportedExternalReferenceType')
            ->with(ExternalReferenceType::BOM)
            ->willReturn(true);

        $actual = $normalizer->normalize($extRef);

        self::assertSame([
            'type' => 'bom',
            'url' => 'someUrl',
            'comment' => 'someComment',
        ], $actual);
    }

    // region normalize hashes

    public function testNormalizeHashes(): void
    {
        $spec = $this->createConfiguredMock(_SpecProtocol::class, [
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
            'getType' => ExternalReferenceType::BOM,
            'getComment' => null,
            'getHashes' => $this->createConfiguredMock(HashDictionary::class, ['count' => 1]),
        ]);

        $spec->method('isSupportedExternalReferenceType')
            ->with(ExternalReferenceType::BOM)
            ->willReturn(true);
        $hashDictNormalizer->expects(self::once())
            ->method('normalize')
            ->with($extRef->getHashes())
            ->willReturn(['NormalizedHashDictFake']);

        $actual = $normalizer->normalize($extRef);

        self::assertSame([
            'type' => 'bom',
            'url' => 'someUrl',
            'hashes' => ['NormalizedHashDictFake'],
        ], $actual);
    }

    public function testNormalizeHashesOmitIfEmpty(): void
    {
        $spec = $this->createConfiguredMock(_SpecProtocol::class, [
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
            'getType' => ExternalReferenceType::BOM,
            'getComment' => null,
            'getHashes' => $this->createConfiguredMock(HashDictionary::class, ['count' => 0]),
        ]);

        $spec->method('isSupportedExternalReferenceType')
            ->with(ExternalReferenceType::BOM)
            ->willReturn(true);
        $HashDictionaryNormalizer->expects(self::never())
            ->method('normalize')
            ->with($extRef->getHashes());

        $actual = $normalizer->normalize($extRef);

        self::assertSame([
            'type' => 'bom',
            'url' => 'someUrl',
            // hashes is omitted
        ], $actual);
    }

    public function testNormalizeHashesOmitIfNotSupported(): void
    {
        $spec = $this->createConfiguredMock(_SpecProtocol::class, [
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
            'getType' => ExternalReferenceType::BOM,
            'getComment' => null,
            'getHashes' => $this->createConfiguredMock(HashDictionary::class, ['count' => 1]),
        ]);

        $spec->method('isSupportedExternalReferenceType')
            ->with(ExternalReferenceType::BOM)
            ->willReturn(true);
        $HashDictionaryNormalizer->expects(self::never())
            ->method('normalize')
            ->with($extRef->getHashes());

        $actual = $normalizer->normalize($extRef);

        self::assertSame([
            'type' => 'bom',
            'url' => 'someUrl',
            // hashes is omitted
        ], $actual);
    }

    // endregion normalize hashes

    #[DataProviderExternal(AnyUriData::class, 'dpEncodeAnyUri')]
    public function testNormalizeUrlEncodeAnyUri(string $rawUrl, string $encodedUrl): void
    {
        $spec = $this->createMock(_SpecProtocol::class);
        $normalizerFactory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $spec,
        ]);
        $normalizer = new Normalizers\ExternalReferenceNormalizer($normalizerFactory);
        $extRef = $this->createConfiguredMock(ExternalReference::class, [
            'getUrl' => $rawUrl,
            'getType' => ExternalReferenceType::Other,
            'getComment' => null,
        ]);

        $spec->method('isSupportedExternalReferenceType')
            ->willReturn(true);

        $actual = $normalizer->normalize($extRef);

        self::assertSame([
            'type' => 'other',
            'url' => $encodedUrl,
        ], $actual);
    }
}
