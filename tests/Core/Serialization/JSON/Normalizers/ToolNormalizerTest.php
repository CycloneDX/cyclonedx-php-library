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

use BadMethodCallException;
use CycloneDX\Core\Collections\ExternalReferenceRepository;
use CycloneDX\Core\Collections\HashDictionary;
use CycloneDX\Core\Models\Tool;
use CycloneDX\Core\Serialization\JSON\NormalizerFactory;
use CycloneDX\Core\Serialization\JSON\Normalizers\ExternalReferenceRepositoryNormalizer;
use CycloneDX\Core\Serialization\JSON\Normalizers\HashDictionaryNormalizer;
use CycloneDX\Core\Serialization\JSON\Normalizers\ToolNormalizer;
use CycloneDX\Core\Spec\Spec;
use PHPUnit\Framework\TestCase;

/**
 *
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\CycloneDX\Core\Serialization\JSON\Normalizers\ToolNormalizer::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(\CycloneDX\Core\Serialization\JSON\_BaseNormalizer::class)]

class ToolNormalizerTest extends TestCase
{
    public function testNormalizeEmpty(): void
    {
        $tool = $this->createMock(Tool::class);
        $spec = $this->createMock(Spec::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, ['getSpec' => $spec]);
        $normalizer = new ToolNormalizer($factory);

        $actual = $normalizer->normalize($tool);

        self::assertSame([], $actual);
    }

    public function testNormalizeFull(): void
    {
        $tool = $this->createConfiguredMock(
            Tool::class,
            [
                'getVendor' => 'myVendor',
                'getName' => 'myName',
                'getVersion' => 'myVersion',
                'getHashes' => $this->createConfiguredMock(HashDictionary::class, ['count' => 2]),
                'getExternalReferences' => $this->createConfiguredMock(ExternalReferenceRepository::class, ['count' => 2]),
            ]
        );
        $spec = $this->createConfiguredMock(Spec::class, [
            'supportsToolExternalReferences' => true,
        ]);
        $hashDictNormalizer = $this->createMock(HashDictionaryNormalizer::class);
        $extRefRepoNormalizer = $this->createMock(ExternalReferenceRepositoryNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'makeForHashDictionary' => $hashDictNormalizer,
                'makeForExternalReferenceRepository' => $extRefRepoNormalizer,
            ]
        );
        $normalizer = new ToolNormalizer($factory);

        $hashDictNormalizer->expects(self::once())
            ->method('normalize')
            ->with($tool->getHashes())
            ->willReturn(['FakeHash' => 'dummyHash']);
        $extRefRepoNormalizer->expects(self::once())
            ->method('normalize')
            ->with($tool->getExternalReferences())
            ->willReturn(['FakeExtRefs' => 'dummyRef']);

        $actual = $normalizer->normalize($tool);

        self::assertSame(
            [
                'vendor' => 'myVendor',
                'name' => 'myName',
                'version' => 'myVersion',
                'hashes' => ['FakeHash' => 'dummyHash'],
                'externalReferences' => ['FakeExtRefs' => 'dummyRef'],
            ],
            $actual
        );
    }

    public function testNormalizeMinimal(): void
    {
        $tool = $this->createConfiguredMock(
            Tool::class,
            [
                'getVendor' => null,
                'getName' => null,
                'getVersion' => null,
                'getHashes' => $this->createMock(HashDictionary::class),
                'getExternalReferences' => $this->createMock(ExternalReferenceRepository::class),
            ]
        );
        $spec = $this->createConfiguredMock(Spec::class, [
            'supportsToolExternalReferences' => true,
        ]);
        $hashDictNormalizer = $this->createMock(HashDictionaryNormalizer::class);
        $extRefRepoNormalizer = $this->createMock(ExternalReferenceRepositoryNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'makeForHashDictionary' => $hashDictNormalizer,
                'makeForExternalReferenceRepository' => $extRefRepoNormalizer,
            ]
        );
        $normalizer = new ToolNormalizer($factory);

        $hashDictNormalizer->expects(self::never())
            ->method('normalize')
            ->willThrowException(new BadMethodCallException());
        $extRefRepoNormalizer->expects(self::never())
            ->method('normalize')
            ->willThrowException(new BadMethodCallException());

        $actual = $normalizer->normalize($tool);

        self::assertSame(
            [],
            $actual
        );
    }
}
