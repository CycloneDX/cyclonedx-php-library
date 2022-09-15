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

namespace CycloneDX\Tests\Core\Serialize\JSON\Normalizers;

use CycloneDX\Core\Models\ExternalReference;
use CycloneDX\Core\Repositories\HashRepository;
use CycloneDX\Core\Serialize\JSON\NormalizerFactory;
use CycloneDX\Core\Serialize\JSON\Normalizers;
use CycloneDX\Core\Spec\SpecInterface;

/**
 * @covers \CycloneDX\Core\Serialize\JSON\Normalizers\ExternalReferenceNormalizer
 * @covers \CycloneDX\Core\Serialize\JSON\AbstractNormalizer
 */
class ExternalReferenceNormalizerTest extends \PHPUnit\Framework\TestCase
{
    public function testNormalizeTypeAndUrl(): void
    {
        $normalizerFactory = $this->createStub(NormalizerFactory::class);
        $normalizer = new Normalizers\ExternalReferenceNormalizer($normalizerFactory);
        $extRef = $this->createConfiguredMock(ExternalReference::class, [
            'getUrl' => 'someUrl',
            'getType' => 'someType',
            'getComment' => null,
            'getHashRepository' => null,
        ]);

        $actual = $normalizer->normalize($extRef);

        self::assertSame([
            'type' => 'someType',
            'url' => 'someUrl',
            // comment omitted
            // hashes omitted
        ], $actual);
    }

    public function testNormalizeComment(): void
    {
        $normalizerFactory = $this->createStub(NormalizerFactory::class);
        $normalizer = new Normalizers\ExternalReferenceNormalizer($normalizerFactory);
        $extRef = $this->createConfiguredMock(ExternalReference::class, [
            'getUrl' => 'someUrl',
            'getType' => 'someType',
            'getComment' => 'someComment',
            'getHashRepository' => null,
        ]);

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
        $hashRepositoryNormalizer = $this->createMock(Normalizers\HashRepositoryNormalizer::class);
        $normalizerFactory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $this->createConfiguredMock(SpecInterface::class, [
                'supportsExternalReferenceHashes' => true,
            ]),
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
            ->willReturn(['NormalizedHashRepoFake']);

        $actual = $normalizer->normalize($extRef);

        self::assertSame([
            'type' => 'someType',
            'url' => 'someUrl',
            'hashes' => ['NormalizedHashRepoFake'],
        ], $actual);
    }

    public function testNormalizeHashesOmitIfEmpty(): void
    {
        $hashRepositoryNormalizer = $this->createMock(Normalizers\HashRepositoryNormalizer::class);
        $normalizerFactory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $this->createConfiguredMock(SpecInterface::class, [
                'supportsExternalReferenceHashes' => true,
            ]),
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

        self::assertSame([
            'type' => 'someType',
            'url' => 'someUrl',
            // hashes is omitted
        ], $actual);
    }

    public function testNormalizeHashesOmitIfNotSupported(): void
    {
        $hashRepositoryNormalizer = $this->createMock(Normalizers\HashRepositoryNormalizer::class);
        $normalizerFactory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $this->createConfiguredMock(SpecInterface::class, [
                'supportsExternalReferenceHashes' => false,
            ]),
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

        self::assertSame([
            'type' => 'someType',
            'url' => 'someUrl',
            // hashes is omitted
        ], $actual);
    }

    // endregion normalize hashes
}
