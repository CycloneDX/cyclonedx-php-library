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

use CycloneDX\Core\Collections\ToolRepository;
use CycloneDX\Core\Models\Tool;
use CycloneDX\Core\Serialization\JSON\_BaseNormalizer;
use CycloneDX\Core\Serialization\JSON\NormalizerFactory;
use CycloneDX\Core\Serialization\JSON\Normalizers\ToolNormalizer;
use CycloneDX\Core\Serialization\JSON\Normalizers\ToolRepositoryNormalizer;
use CycloneDX\Core\Spec\_SpecProtocol;
use DomainException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ToolRepositoryNormalizer::class)]
#[CoversClass(_BaseNormalizer::class)]
#[UsesClass(ToolNormalizer::class)]
class ToolRepositoryNormalizerTest extends TestCase
{
    public function testNormalizeEmpty(): void
    {
        $spec = $this->createStub(_SpecProtocol::class);
        $toolNormalizer = $this->createMock(ToolNormalizer::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $spec,
            'makeForTool' => $toolNormalizer,
        ]);

        $normalizer = new ToolRepositoryNormalizer($factory);
        $tools = $this->createConfiguredMock(ToolRepository::class, ['count' => 0]);

        $actual = $normalizer->normalize($tools);

        self::assertSame([], $actual);
    }

    public function testNormalize(): void
    {
        $spec = $this->createStub(_SpecProtocol::class);
        $toolNormalizer = $this->createMock(ToolNormalizer::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $spec,
            'makeForTool' => $toolNormalizer,
        ]);
        $normalizer = new ToolRepositoryNormalizer($factory);
        $tool = $this->createStub(Tool::class);
        $tools = $this->createConfiguredMock(ToolRepository::class, [
            'count' => 1,
            'getItems' => [$tool],
        ]);

        $toolNormalizer->expects(self::once())->method('normalize')
            ->with($tool)
            ->willReturn(['FakeTool' => 'dummy']);

        $actual = $normalizer->normalize($tools);

        self::assertSame([['FakeTool' => 'dummy']], $actual);
    }

    public function testNormalizeSkipsOnThrow(): void
    {
        $spec = $this->createStub(_SpecProtocol::class);
        $toolNormalizer = $this->createMock(ToolNormalizer::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $spec,
            'makeForTool' => $toolNormalizer,
        ]);
        $normalizer = new ToolRepositoryNormalizer($factory);
        $tool1 = $this->createStub(Tool::class);
        $tool2 = $this->createStub(Tool::class);
        $tools = $this->createConfiguredMock(ToolRepository::class, [
            'count' => 1,
            'getItems' => [$tool1, $tool2],
        ]);

        $toolNormalizer->expects(self::exactly(2))
            ->method('normalize')
            ->willThrowException(new DomainException());

        $actual = $normalizer->normalize($tools);

        self::assertSame([], $actual);
    }
}
