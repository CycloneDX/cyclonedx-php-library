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

use CycloneDX\Core\Collections\ToolRepository;
use CycloneDX\Core\Models\Component;
use CycloneDX\Core\Models\Metadata;
use CycloneDX\Core\Serialization\DOM\NormalizerFactory;
use CycloneDX\Core\Serialization\DOM\Normalizers\ComponentNormalizer;
use CycloneDX\Core\Serialization\DOM\Normalizers\MetaDataNormalizer;
use CycloneDX\Core\Serialization\DOM\Normalizers\ToolRepositoryNormalizer;
use CycloneDX\Core\Spec\Spec;
use CycloneDX\Tests\_traits\DomNodeAssertionTrait;
use DomainException;
use DOMDocument;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Serialization\DOM\Normalizers\MetaDataNormalizer
 * @covers \CycloneDX\Core\Serialization\DOM\_BaseNormalizer
 */
class MetadataNormalizerTest extends TestCase
{
    use DomNodeAssertionTrait;

    public function testNormalizeEmpty(): void
    {
        $metaData = $this->createMock(Metadata::class);
        $spec = $this->createMock(Spec::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            ['getSpec' => $spec, 'getDocument' => new DOMDocument()]
        );
        $normalizer = new MetaDataNormalizer($factory);

        $actual = $normalizer->normalize($metaData);

        self::assertStringEqualsDomNode('<metadata></metadata>', $actual);
    }

    public function testNormalizeTools(): void
    {
        $metaData = $this->createConfiguredMock(
            Metadata::class,
            [
                'getTools' => $this->createConfiguredMock(ToolRepository::class, ['count' => 2]),
            ]
        );
        $spec = $this->createMock(Spec::class);
        $toolsRepoFactory = $this->createMock(ToolRepositoryNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'getDocument' => new DOMDocument(),
                'makeForToolRepository' => $toolsRepoFactory,
            ]
        );
        $normalizer = new MetaDataNormalizer($factory);

        $toolsRepoFactory->expects(self::once())
            ->method('normalize')
            ->with($metaData->getTools())
            ->willReturn([$factory->getDocument()->createElement('FakeTool', 'dummy')]);

        $actual = $normalizer->normalize($metaData);

        self::assertStringEqualsDomNode(
            '<metadata><tools><FakeTool>dummy</FakeTool></tools></metadata>',
            $actual
        );
    }

    /**
     * @uses \CycloneDX\Core\Serialization\DOM\Normalizers\ComponentNormalizer
     */
    public function testNormalizeComponent(): void
    {
        $metaData = $this->createConfiguredMock(
            Metadata::class,
            [
                'getComponent' => $this->createMock(Component::class),
            ]
        );
        $spec = $this->createMock(Spec::class);
        $componentFactory = $this->createMock(ComponentNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'getDocument' => new DOMDocument(),
                'makeForComponent' => $componentFactory,
            ]
        );
        $normalizer = new MetaDataNormalizer($factory);

        $componentFactory->expects(self::once())
            ->method('normalize')
            ->with($metaData->getComponent())
            ->willReturn($factory->getDocument()->createElement('FakeComponent', 'dummy'));

        $actual = $normalizer->normalize($metaData);

        self::assertStringEqualsDomNode(
            '<metadata><FakeComponent>dummy</FakeComponent></metadata>',
            $actual
        );
    }

    public function testNormalizeComponentUnsupported(): void
    {
        $metaData = $this->createConfiguredMock(
            Metadata::class,
            [
                'getComponent' => $this->createMock(Component::class),
            ]
        );
        $spec = $this->createMock(Spec::class);
        $componentFactory = $this->createMock(ComponentNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'getDocument' => new DOMDocument(),
                'makeForComponent' => $componentFactory,
            ]
        );
        $normalizer = new MetaDataNormalizer($factory);

        $componentFactory->expects(self::once())
            ->method('normalize')
            ->with($metaData->getComponent())
            ->willThrowException(new DomainException());

        $actual = $normalizer->normalize($metaData);

        self::assertStringEqualsDomNode(
            '<metadata></metadata>',
            $actual
        );
    }
}
