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
use CycloneDX\Core\Models\ExternalReference;
use CycloneDX\Core\Serialization\DOM\NormalizerFactory;
use CycloneDX\Core\Serialization\DOM\Normalizers;
use CycloneDX\Core\Spec\Spec;
use CycloneDX\Tests\_traits\DomNodeAssertionTrait;
use DomainException;
use DOMDocument;
use Exception;

/**
 * @covers \CycloneDX\Core\Serialization\DOM\Normalizers\ExternalReferenceNormalizer
 * @covers \CycloneDX\Core\Serialization\DOM\_BaseNormalizer
 */
class ExternalReferenceNormalizerTest extends \PHPUnit\Framework\TestCase
{
    use DomNodeAssertionTrait;

    public function testNormalizeTypeAndUrl(): void
    {
        $spec = $this->createMock(Spec::class);
        $normalizerFactory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getDocument' => new DOMDocument(),
            'getSPec' => $spec,
        ]);
        $normalizer = new Normalizers\ExternalReferenceNormalizer($normalizerFactory);
        $extRef = $this->createConfiguredMock(ExternalReference::class, [
            'getUrl' => 'someUrl',
            'getType' => 'someType',
            'getComment' => null,
            'getHashes' => $this->createStub(HashDictionary::class),
        ]);

        $spec->method('isSupportedExternalReferenceType')
            ->with('someType')
            ->willReturn(true);

        $actual = $normalizer->normalize($extRef);

        self::assertStringEqualsDomNode(
            '<reference type="someType"><url>someUrl</url></reference>',
            $actual
        );
    }

    public function testThrowOnUnsupportedRefType(): void
    {
        $spec = $this->createMock(Spec::class);
        $normalizerFactory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getDocument' => new DOMDocument(),
            'getSpec' => $spec,
        ]);
        $normalizer = new Normalizers\ExternalReferenceNormalizer($normalizerFactory);
        $extRef = $this->createConfiguredMock(ExternalReference::class, [
            'getUrl' => '..',
            'getType' => 'someType',
            'getComment' => null,
            'getHashes' => $this->createStub(HashDictionary::class),
        ]);

        $spec->expects(self::exactly(2))
            ->method('isSupportedExternalReferenceType')
            ->withConsecutive(['someType'], ['other'])
            ->willReturn(false);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('ExternalReference has unsupported type: someType');

        $normalizer->normalize($extRef);
    }

    public function testNormalizeTypeConvertIfNotSupported(): void
    {
        $spec = $this->createMock(Spec::class);
        $normalizerFactory = $this->createConfiguredMock(\CycloneDX\Core\Serialization\DOM\NormalizerFactory::class, [
            'getDocument' => new DOMDocument(),
            'getSPec' => $spec,
        ]);
        $normalizer = new Normalizers\ExternalReferenceNormalizer($normalizerFactory);
        $extRef = $this->createConfiguredMock(ExternalReference::class, [
            'getType' => 'someType',
            'getUrl' => 'someUrl',
        ]);

        $spec->expects(self::exactly(2))
            ->method('isSupportedExternalReferenceType')
            ->withConsecutive(['someType'], ['other'])
            ->willReturnMap([
                ['someType', false],
                ['other', true],
            ]);

        $actual = $normalizer->normalize($extRef);

        self::assertStringEqualsDomNode(
            '<reference type="other"><url>someUrl</url></reference>',
            $actual
        );
    }

    public function testNormalizeComment(): void
    {
        $spec = $this->createMock(Spec::class);
        $normalizerFactory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getDocument' => new DOMDocument(),
            'getSPec' => $spec,
        ]);
        $normalizer = new Normalizers\ExternalReferenceNormalizer($normalizerFactory);
        $extRef = $this->createConfiguredMock(ExternalReference::class, [
            'getUrl' => 'someUrl',
            'getType' => 'someType',
            'getComment' => 'someComment',
            'getHashes' => $this->createStub(HashDictionary::class),
        ]);

        $spec->method('isSupportedExternalReferenceType')
            ->with('someType')
            ->willReturn(true);

        $actual = $normalizer->normalize($extRef);

        self::assertStringEqualsDomNode(
            '<reference type="someType"><url>someUrl</url><comment>someComment</comment></reference>',
            $actual
        );
    }

    // region normalize hashes

    public function testNormalizeHashes(): void
    {
        $spec = $this->createConfiguredMock(Spec::class, [
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
            'getType' => 'someType',
            'getComment' => null,
            'getHashes' => $this->createConfiguredMock(HashDictionary::class, ['count' => 1]),
        ]);

        $spec->method('isSupportedExternalReferenceType')
            ->with('someType')
            ->willReturn(true);
        $HashDictionaryNormalizer->expects(self::once())
            ->method('normalize')
            ->with($extRef->getHashes())
            ->willReturn([$normalizerFactory->getDocument()->createElement('FakeHash', 'dummy')]);

        $actual = $normalizer->normalize($extRef);

        self::assertStringEqualsDomNode(
            '<reference type="someType">'.
            '<url>someUrl</url>'.
            '<hashes><FakeHash>dummy</FakeHash></hashes>'.
            '</reference>',
            $actual
        );
    }

    /**
     * @throws Exception on assertion error
     */
    public function testNormalizeHashesOmitIfEmpty(): void
    {
        $spec = $this->createConfiguredMock(Spec::class, [
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
            'getType' => 'someType',
            'getComment' => null,
            'getHashes' => $this->createStub(HashDictionary::class),
        ]);

        $spec->method('isSupportedExternalReferenceType')
            ->with('someType')
            ->willReturn(true);
        $HashDictionaryNormalizer->expects(self::never())
            ->method('normalize')
            ->with($extRef->getHashes());

        $actual = $normalizer->normalize($extRef);

        self::assertStringEqualsDomNode(
            '<reference type="someType"><url>someUrl</url></reference>',
            $actual
        );
    }

    /**
     * @throws Exception on assertion error
     */
    public function testNormalizeHashesOmitIfNotSupported(): void
    {
        $spec = $this->createConfiguredMock(Spec::class, [
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

        self::assertStringEqualsDomNode(
            '<reference type="someType"><url>someUrl</url></reference>',
            $actual
        );
    }

    // endregion normalize hashes

    /**
     * @dataProvider \CycloneDX\Tests\_data\XmlAnyUriData::dpEncodeAnyUri()
     */
    public function testNormalizeUrlEncodeAnyUri(string $rawUrl, string $encodedUrl): void
    {
        $spec = $this->createMock(Spec::class);
        $normalizerFactory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getDocument' => new DOMDocument(),
            'getSpec' => $spec,
        ]);
        $normalizer = new Normalizers\ExternalReferenceNormalizer($normalizerFactory);
        $extRef = $this->createConfiguredMock(ExternalReference::class, [
            'getUrl' => $rawUrl,
            'getType' => 'someType',
            'getComment' => null,
            'getHashes' => $this->createStub(HashDictionary::class),
        ]);

        $spec->expects(self::atLeastOnce())
            ->method('isSupportedExternalReferenceType')
            ->with('someType')
            ->willReturn(true);

        $actual = $normalizer->normalize($extRef);

        self::assertStringEqualsDomNode(
            '<reference type="someType"><url>'.htmlspecialchars($encodedUrl).'</url></reference>',
            $actual
        );
    }
}
