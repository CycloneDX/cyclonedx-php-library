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
 * Copyright (c) Steve Springett. All Rights Reserved.
 */

namespace CycloneDX\Tests\Core\Serialize\DOM\Normalizers;

use CycloneDX\Core\Models\ExternalReference;
use CycloneDX\Core\Repositories\HashRepository;
use CycloneDX\Core\Serialize\DOM\NormalizerFactory;
use CycloneDX\Core\Serialize\DOM\Normalizers;
use CycloneDX\Core\Spec\SpecInterface;
use CycloneDX\Tests\_traits\DomNodeAssertionTrait;

/**
 * @covers \CycloneDX\Core\Serialize\DOM\Normalizers\ExternalReferenceNormalizer
 * @covers \CycloneDX\Core\Serialize\DOM\AbstractNormalizer
 */
class ExternalReferenceNormalizerTest extends \PHPUnit\Framework\TestCase
{
    use DomNodeAssertionTrait;

    /**
     * @throws \Exception on assertion error
     */
    public function testNormalizeTypeAndUrl(): void
    {
        $normalizerFactory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getDocument' => new \DOMDocument(),
        ]);
        $normalizer = new Normalizers\ExternalReferenceNormalizer($normalizerFactory);
        $extRef = $this->createConfiguredMock(ExternalReference::class, [
            'getUrl' => 'someUrl',
            'getType' => 'someType',
            'getComment' => null,
            'getHashRepository' => null,
        ]);

        $actual = $normalizer->normalize($extRef);

        self::assertStringEqualsDomNode(
            '<reference type="someType"><url>someUrl</url></reference>',
            $actual
        );
    }

    /**
     * @throws \Exception on assertion error
     */
    public function testNormalizeComment(): void
    {
        $normalizerFactory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getDocument' => new \DOMDocument(),
        ]);
        $normalizer = new Normalizers\ExternalReferenceNormalizer($normalizerFactory);
        $extRef = $this->createConfiguredMock(ExternalReference::class, [
            'getUrl' => 'someUrl',
            'getType' => 'someType',
            'getComment' => 'someComment',
            'getHashRepository' => null,
        ]);

        $actual = $normalizer->normalize($extRef);

        self::assertStringEqualsDomNode(
            '<reference type="someType"><url>someUrl</url><comment>someComment</comment></reference>',
            $actual
        );
    }

    // region normalize hashes

    /**
     * @throws \Exception on assertion error
     */
    public function testNormalizeHashes(): void
    {
        $hashRepositoryNormalizer = $this->createMock(Normalizers\HashRepositoryNormalizer::class);
        $normalizerFactory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $this->createConfiguredMock(SpecInterface::class, [
                'supportsExternalReferenceHashes' => true,
            ]),
            'getDocument' => new \DOMDocument(),
            'makeForHashRepository' => $hashRepositoryNormalizer,
        ]);
        $normalizer = new Normalizers\ExternalReferenceNormalizer($normalizerFactory);
        $extRef = $this->createConfiguredMock(ExternalReference::class, [
            'getUrl' => 'someUrl',
            'getType' => 'someType',
            'getComment' => null,
            'getHashRepository' => $this->createConfiguredMock(HashRepository::class, ['count' => 1]),
        ]);

        $hashRepositoryNormalizer->expects(self::once())
            ->method('normalize')
            ->with($extRef->getHashRepository())
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
     * @throws \Exception on assertion error
     */
    public function testNormalizeHashesOmitIfEmpty(): void
    {
        $hashRepositoryNormalizer = $this->createMock(Normalizers\HashRepositoryNormalizer::class);
        $normalizerFactory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $this->createConfiguredMock(SpecInterface::class, [
                'supportsExternalReferenceHashes' => true,
            ]),
            'getDocument' => new \DOMDocument(),
            'makeForHashRepository' => $hashRepositoryNormalizer,
        ]);
        $normalizer = new Normalizers\ExternalReferenceNormalizer($normalizerFactory);
        $extRef = $this->createConfiguredMock(ExternalReference::class, [
            'getUrl' => 'someUrl',
            'getType' => 'someType',
            'getComment' => null,
            'getHashRepository' => $this->createConfiguredMock(HashRepository::class, ['count' => 0]),
        ]);

        $hashRepositoryNormalizer->expects(self::never())
            ->method('normalize')
            ->with($extRef->getHashRepository());

        $actual = $normalizer->normalize($extRef);

        self::assertStringEqualsDomNode(
            '<reference type="someType"><url>someUrl</url></reference>',
            $actual
        );
    }

    /**
     * @throws \Exception on assertion error
     */
    public function testNormalizeHashesOmitIfNotSupported(): void
    {
        $hashRepositoryNormalizer = $this->createMock(Normalizers\HashRepositoryNormalizer::class);
        $normalizerFactory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $this->createConfiguredMock(SpecInterface::class, [
                'supportsExternalReferenceHashes' => false,
            ]),
            'getDocument' => new \DOMDocument(),
            'makeForHashRepository' => $hashRepositoryNormalizer,
        ]);
        $normalizer = new Normalizers\ExternalReferenceNormalizer($normalizerFactory);
        $extRef = $this->createConfiguredMock(ExternalReference::class, [
            'getUrl' => 'someUrl',
            'getType' => 'someType',
            'getComment' => null,
            'getHashRepository' => $this->createConfiguredMock(HashRepository::class, ['count' => 1]),
        ]);

        $hashRepositoryNormalizer->expects(self::never())
            ->method('normalize')
            ->with($extRef->getHashRepository());

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
        $normalizerFactory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getDocument' => new \DOMDocument(),
        ]);
        $normalizer = new Normalizers\ExternalReferenceNormalizer($normalizerFactory);
        $extRef = $this->createConfiguredMock(ExternalReference::class, [
            'getUrl' => $rawUrl,
            'getType' => 'someType',
            'getComment' => null,
            'getHashRepository' => null,
        ]);

        $actual = $normalizer->normalize($extRef);

        self::assertStringEqualsDomNode(
            '<reference type="someType"><url>'.htmlspecialchars($encodedUrl).'</url></reference>',
            $actual
        );
    }
}
