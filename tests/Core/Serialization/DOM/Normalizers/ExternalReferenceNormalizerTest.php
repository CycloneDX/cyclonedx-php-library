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
use CycloneDX\Core\Enums\ExternalReferenceType;
use CycloneDX\Core\Models\ExternalReference;
use CycloneDX\Core\Serialization\DOM\_BaseNormalizer;
use CycloneDX\Core\Serialization\DOM\NormalizerFactory;
use CycloneDX\Core\Serialization\DOM\Normalizers;
use CycloneDX\Core\Spec\_SpecProtocol;
use CycloneDX\Tests\_data\AnyUriData;
use CycloneDX\Tests\_traits\DomNodeAssertionTrait;
use DomainException;
use DOMDocument;
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
    use DomNodeAssertionTrait;

    public function testNormalizeTypeAndUrl(): void
    {
        $spec = $this->createMock(_SpecProtocol::class);
        $normalizerFactory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getDocument' => new DOMDocument(),
            'getSPec' => $spec,
        ]);
        $normalizer = new Normalizers\ExternalReferenceNormalizer($normalizerFactory);
        $extRef = $this->createConfiguredMock(ExternalReference::class, [
            'getUrl' => 'someUrl',
            'getType' => ExternalReferenceType::BOM,
            'getComment' => null,
            'getHashes' => $this->createStub(HashDictionary::class),
        ]);

        $spec->method('isSupportedExternalReferenceType')
            ->with(ExternalReferenceType::BOM)
            ->willReturn(true);

        $actual = $normalizer->normalize($extRef);

        self::assertStringEqualsDomNode(
            '<reference type="bom"><url>someUrl</url></reference>',
            $actual
        );
    }

    #[DataProvider('dpThrowOnUnsupportedUrl')]
    public function testThrowOnUnsupportedUrl(string $unsupportedURL): void
    {
        $spec = $this->createMock(_SpecProtocol::class);
        $normalizerFactory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getDocument' => new DOMDocument(),
            'getSpec' => $spec,
        ]);
        $normalizer = new Normalizers\ExternalReferenceNormalizer($normalizerFactory);
        $extRef = $this->createConfiguredMock(ExternalReference::class, [
            'getUrl' => $unsupportedURL,
        ]);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage("unable to make 'anyURI' from: $unsupportedURL");

        $normalizer->normalize($extRef);
    }

    public static function dpThrowOnUnsupportedUrl(): Generator
    {
        yield 'multiple #' => ['https://example.com#foo#bar'];
    }

    public function testThrowOnUnsupportedRefType(): void
    {
        $spec = $this->createMock(_SpecProtocol::class);
        $normalizerFactory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getDocument' => new DOMDocument(),
            'getSpec' => $spec,
        ]);
        $normalizer = new Normalizers\ExternalReferenceNormalizer($normalizerFactory);
        $extRef = $this->createConfiguredMock(ExternalReference::class, [
            'getUrl' => '..',
            'getType' => ExternalReferenceType::BOM,
            'getComment' => null,
            'getHashes' => $this->createStub(HashDictionary::class),
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

    public function testNormalizeTypeConvertIfNotSupported(): void
    {
        $spec = $this->createMock(_SpecProtocol::class);
        $normalizerFactory = $this->createConfiguredMock(\CycloneDX\Core\Serialization\DOM\NormalizerFactory::class, [
            'getDocument' => new DOMDocument(),
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

        self::assertStringEqualsDomNode(
            '<reference type="other"><url>someUrl</url></reference>',
            $actual
        );
    }

    public function testNormalizeComment(): void
    {
        $spec = $this->createMock(_SpecProtocol::class);
        $normalizerFactory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getDocument' => new DOMDocument(),
            'getSPec' => $spec,
        ]);
        $normalizer = new Normalizers\ExternalReferenceNormalizer($normalizerFactory);
        $extRef = $this->createConfiguredMock(ExternalReference::class, [
            'getUrl' => 'someUrl',
            'getType' => ExternalReferenceType::BOM,
            'getComment' => 'someComment',
            'getHashes' => $this->createStub(HashDictionary::class),
        ]);

        $spec->method('isSupportedExternalReferenceType')
            ->with(ExternalReferenceType::BOM)
            ->willReturn(true);

        $actual = $normalizer->normalize($extRef);

        self::assertStringEqualsDomNode(
            '<reference type="bom"><url>someUrl</url><comment>someComment</comment></reference>',
            $actual
        );
    }

    // region normalize hashes

    public function testNormalizeHashes(): void
    {
        $spec = $this->createConfiguredMock(_SpecProtocol::class, [
            'supportsExternalReferenceHashes' => true,
        ]);
        $HashDictionaryNormalizer = $this->createMock(Normalizers\HashDictionaryNormalizer::class);
        $normalizerFactory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $spec,
            'getDocument' => new DOMDocument(),
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
        $HashDictionaryNormalizer->expects(self::once())
            ->method('normalize')
            ->with($extRef->getHashes())
            ->willReturn([$normalizerFactory->getDocument()->createElement('FakeHash', 'dummy')]);

        $actual = $normalizer->normalize($extRef);

        self::assertStringEqualsDomNode(
            '<reference type="bom">'.
            '<url>someUrl</url>'.
            '<hashes><FakeHash>dummy</FakeHash></hashes>'.
            '</reference>',
            $actual
        );
    }

    public function testNormalizeHashesOmitIfEmpty(): void
    {
        $spec = $this->createConfiguredMock(_SpecProtocol::class, [
            'supportsExternalReferenceHashes' => true,
        ]);
        $HashDictionaryNormalizer = $this->createMock(Normalizers\HashDictionaryNormalizer::class);
        $normalizerFactory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $spec,
            'getDocument' => new DOMDocument(),
            'makeForHashDictionary' => $HashDictionaryNormalizer,
        ]);
        $normalizer = new Normalizers\ExternalReferenceNormalizer($normalizerFactory);
        $extRef = $this->createConfiguredMock(ExternalReference::class, [
            'getUrl' => 'someUrl',
            'getType' => ExternalReferenceType::BOM,
            'getComment' => null,
            'getHashes' => $this->createStub(HashDictionary::class),
        ]);

        $spec->method('isSupportedExternalReferenceType')
            ->with(ExternalReferenceType::BOM)
            ->willReturn(true);
        $HashDictionaryNormalizer->expects(self::never())
            ->method('normalize')
            ->with($extRef->getHashes());

        $actual = $normalizer->normalize($extRef);

        self::assertStringEqualsDomNode(
            '<reference type="bom"><url>someUrl</url></reference>',
            $actual
        );
    }

    public function testNormalizeHashesOmitIfNotSupported(): void
    {
        $spec = $this->createConfiguredMock(_SpecProtocol::class, [
            'supportsExternalReferenceHashes' => false,
        ]);
        $HashDictionaryNormalizer = $this->createMock(Normalizers\HashDictionaryNormalizer::class);
        $normalizerFactory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $spec,
            'getDocument' => new DOMDocument(),
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

        self::assertStringEqualsDomNode(
            '<reference type="bom"><url>someUrl</url></reference>',
            $actual
        );
    }

    // endregion normalize hashes

    #[DataProviderExternal(AnyUriData::class, 'dpEncodeAnyUri')]
    public function testNormalizeUrlEncodeAnyUri(string $rawUrl, string $encodedUrl): void
    {
        $spec = $this->createMock(_SpecProtocol::class);
        $normalizerFactory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getDocument' => new DOMDocument(),
            'getSpec' => $spec,
        ]);
        $normalizer = new Normalizers\ExternalReferenceNormalizer($normalizerFactory);
        $extRef = $this->createConfiguredMock(ExternalReference::class, [
            'getUrl' => $rawUrl,
            'getType' => ExternalReferenceType::Other,
            'getComment' => null,
            'getHashes' => $this->createStub(HashDictionary::class),
        ]);

        $spec->method('isSupportedExternalReferenceType')
            ->willReturn(true);

        $actual = $normalizer->normalize($extRef);

        self::assertStringEqualsDomNode(
            '<reference type="other"><url>'.htmlspecialchars($encodedUrl).'</url></reference>',
            $actual
        );
    }
}
