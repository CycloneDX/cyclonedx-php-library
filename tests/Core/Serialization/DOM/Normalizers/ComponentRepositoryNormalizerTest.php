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

use CycloneDX\Core\Collections\ComponentRepository;
use CycloneDX\Core\Models\Component;
use CycloneDX\Core\Serialization\DOM\_BaseNormalizer;
use CycloneDX\Core\Serialization\DOM\NormalizerFactory;
use CycloneDX\Core\Serialization\DOM\Normalizers\ComponentNormalizer;
use CycloneDX\Core\Serialization\DOM\Normalizers\ComponentRepositoryNormalizer;
use CycloneDX\Core\Spec\_SpecProtocol;
use DomainException;
use DOMElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ComponentRepositoryNormalizer::class)]
#[CoversClass(_BaseNormalizer::class)]
class ComponentRepositoryNormalizerTest extends TestCase
{
    public function testNormalizeEmpty(): void
    {
        $spec = $this->createStub(_SpecProtocol::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, ['getSpec' => $spec]);
        $normalizer = new ComponentRepositoryNormalizer($factory);
        $components = $this->createConfiguredMock(ComponentRepository::class, ['count' => 0]);

        $got = $normalizer->normalize($components);

        self::assertSame([], $got);
    }

    public function testNormalize(): void
    {
        $spec = $this->createStub(_SpecProtocol::class);
        $componentNormalizer = $this->createMock(ComponentNormalizer::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $spec,
            'makeForComponent' => $componentNormalizer,
        ]);
        $normalizer = new ComponentRepositoryNormalizer($factory);
        $component = $this->createStub(Component::class);
        $components = $this->createConfiguredMock(ComponentRepository::class, [
            'count' => 1,
            'getItems' => [$component],
        ]);
        $FakeComponent = $this->createStub(DOMElement::class);

        $componentNormalizer->expects(self::once())->method('normalize')
            ->with($component)
            ->willReturn($FakeComponent);

        $got = $normalizer->normalize($components);

        self::assertSame([$FakeComponent], $got);
    }

    public function testNormalizeSkipsOnThrow(): void
    {
        $spec = $this->createStub(_SpecProtocol::class);
        $componentNormalizer = $this->createMock(ComponentNormalizer::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $spec,
            'makeForComponent' => $componentNormalizer,
        ]);
        $normalizer = new ComponentRepositoryNormalizer($factory);
        $component1 = $this->createStub(Component::class);
        $component2 = $this->createStub(Component::class);
        $components = $this->createConfiguredMock(ComponentRepository::class, [
            'count' => 1,
            'getItems' => [$component1, $component2],
        ]);

        $componentNormalizer->expects(self::exactly(2))
            ->method('normalize')
            ->willThrowException(new DomainException());

        $got = $normalizer->normalize($components);

        self::assertSame([], $got);
    }
}
