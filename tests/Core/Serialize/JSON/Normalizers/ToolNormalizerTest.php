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

use CycloneDX\Core\Models\Tool;
use CycloneDX\Core\Repositories\ExternalReferenceRepository;
use CycloneDX\Core\Repositories\HashRepository;
use CycloneDX\Core\Serialize\JSON\NormalizerFactory;
use CycloneDX\Core\Serialize\JSON\Normalizers\ExternalReferenceRepositoryNormalizer;
use CycloneDX\Core\Serialize\JSON\Normalizers\HashRepositoryNormalizer;
use CycloneDX\Core\Serialize\JSON\Normalizers\ToolNormalizer;
use CycloneDX\Core\Spec\SpecInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Serialize\JSON\Normalizers\ToolNormalizer
 *
 * @uses   \CycloneDX\Core\Serialize\JSON\AbstractNormalizer
 */
class ToolNormalizerTest extends TestCase
{
    public function testNormalizeEmpty(): void
    {
        $tool = $this->createMock(Tool::class);
        $spec = $this->createMock(SpecInterface::class);
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
                'getHashRepository' => $this->createConfiguredMock(HashRepository::class, ['count' => 2]),
                'getExternalReferenceRepository' => $this->createConfiguredMock(ExternalReferenceRepository::class, ['count' => 2]),
            ]
        );
        $spec = $this->createConfiguredMock(SpecInterface::class, [
            'supportsToolExternalReferences' => true,
        ]);
        $hashRepoNormalizer = $this->createMock(HashRepositoryNormalizer::class);
        $extRefRepoNormalizer = $this->createMock(ExternalReferenceRepositoryNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'makeForHashRepository' => $hashRepoNormalizer,
                'makeForExternalReferenceRepository' => $extRefRepoNormalizer,
            ]
        );
        $normalizer = new ToolNormalizer($factory);

        $hashRepoNormalizer->expects(self::once())
            ->method('normalize')
            ->with($tool->getHashRepository())
            ->willReturn(['FakeHash' => 'dummyHash']);
        $extRefRepoNormalizer->expects(self::once())
            ->method('normalize')
            ->with($tool->getExternalReferenceRepository())
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
                'getHashRepository' => null,
                'getExternalReferenceRepository' => null,
            ]
        );
        $spec = $this->createConfiguredMock(SpecInterface::class, [
            'supportsToolExternalReferences' => true,
        ]);
        $hashRepoNormalizer = $this->createMock(HashRepositoryNormalizer::class);
        $extRefRepoNormalizer = $this->createMock(ExternalReferenceRepositoryNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'makeForHashRepository' => $hashRepoNormalizer,
                'makeForExternalReferenceRepository' => $extRefRepoNormalizer,
            ]
        );
        $normalizer = new ToolNormalizer($factory);

        $hashRepoNormalizer->expects(self::never())
            ->method('normalize')
            ->willThrowException(new \BadMethodCallException());
        $extRefRepoNormalizer->expects(self::never())
            ->method('normalize')
            ->willThrowException(new \BadMethodCallException());

        $actual = $normalizer->normalize($tool);

        self::assertSame(
            [],
            $actual
        );
    }
}
