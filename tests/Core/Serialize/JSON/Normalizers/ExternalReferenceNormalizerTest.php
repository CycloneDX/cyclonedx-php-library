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
        $spec = $this->createMock(SpecInterface::class);
        $normalizerFactory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSPec' => $spec,
        ]);
        $normalizer = new Normalizers\ExternalReferenceNormalizer($normalizerFactory);
        $extRef = $this->createConfiguredMock(ExternalReference::class, [
            'getUrl' => 'someUrl',
            'getType' => 'someType',
            'getComment' => null,
            'getHashRepository' => null,
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
        $spec = $this->createMock(SpecInterface::class);
        $normalizerFactory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSPec' => $spec,
        ]);
        $normalizer = new Normalizers\ExternalReferenceNormalizer($normalizerFactory);
        $extRef = $this->createConfiguredMock(ExternalReference::class, [
            'getUrl' => 'someUrl',
            'getType' => 'someType',
            'getComment' => null,
            'getHashRepository' => null,
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
        $spec = $this->createMock(SpecInterface::class);
        $normalizerFactory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSPec' => $spec,
        ]);
        $normalizer = new Normalizers\ExternalReferenceNormalizer($normalizerFactory);
        $extRef = $this->createConfiguredMock(ExternalReference::class, [
            'getUrl' => 'someUrl',
            'getType' => 'someType',
            'getComment' => 'someComment',
            'getHashRepository' => null,
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
        $spec = $this->createConfiguredMock(SpecInterface::class, [
            'supportsExternalReferenceHashes' => true,
        ]);
        $hashRepositoryNormalizer = $this->createMock(Normalizers\HashRepositoryNormalizer::class);
        $normalizerFactory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $spec,
            'makeForHashRepository' => $hashRepositoryNormalizer,
        ]);
        $normalizer = new Normalizers\ExternalReferenceNormalizer($normalizerFactory);
        $extRef = $this->createConfiguredMock(ExternalReference::class, [
            'getUrl' => 'someUrl',
            'getType' => 'someType',
            'getComment' => null,
            'getHashRepository' => $this->createConfiguredMock(HashRepository::class, ['count' => 1]),
        ]);

        $spec->method('isSupportedExternalReferenceType')
            ->with('someType')
            ->willReturn(true);
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
        $spec = $this->createConfiguredMock(SpecInterface::class, [
            'supportsExternalReferenceHashes' => true,
        ]);
        $hashRepositoryNormalizer = $this->createMock(Normalizers\HashRepositoryNormalizer::class);
        $normalizerFactory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $spec,
            'makeForHashRepository' => $hashRepositoryNormalizer,
        ]);
        $normalizer = new Normalizers\ExternalReferenceNormalizer($normalizerFactory);
        $extRef = $this->createConfiguredMock(ExternalReference::class, [
            'getUrl' => 'someUrl',
            'getType' => 'someType',
            'getComment' => null,
            'getHashRepository' => $this->createConfiguredMock(HashRepository::class, ['count' => 0]),
        ]);

        $spec->method('isSupportedExternalReferenceType')
            ->with('someType')
            ->willReturn(true);
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
        $spec = $this->createConfiguredMock(SpecInterface::class, [
            'supportsExternalReferenceHashes' => false,
        ]);
        $hashRepositoryNormalizer = $this->createMock(Normalizers\HashRepositoryNormalizer::class);
        $normalizerFactory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $spec,
            'makeForHashRepository' => $hashRepositoryNormalizer,
        ]);
        $normalizer = new Normalizers\ExternalReferenceNormalizer($normalizerFactory);
        $extRef = $this->createConfiguredMock(ExternalReference::class, [
            'getUrl' => 'someUrl',
            'getType' => 'someType',
            'getComment' => null,
            'getHashRepository' => $this->createConfiguredMock(HashRepository::class, ['count' => 1]),
        ]);

        $spec->method('isSupportedExternalReferenceType')
            ->with('someType')
            ->willReturn(true);
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
