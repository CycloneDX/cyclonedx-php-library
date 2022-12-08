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

use CycloneDX\Core\Collections\PropertyRepository;
use CycloneDX\Core\Collections\ToolRepository;
use CycloneDX\Core\Models\Component;
use CycloneDX\Core\Models\Metadata;
use CycloneDX\Core\Serialization\DOM\NormalizerFactory;
use CycloneDX\Core\Serialization\DOM\Normalizers;
use CycloneDX\Core\Spec\Spec;
use CycloneDX\Tests\_traits\DomNodeAssertionTrait;
use DateTime;
use DomainException;
use DOMDocument;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Serialization\DOM\Normalizers\MetadataNormalizer
 * @covers \CycloneDX\Core\Serialization\DOM\_BaseNormalizer
 */
class MetadataNormalizerTest extends TestCase
{
    use DomNodeAssertionTrait;

    public function testNormalizeEmpty(): void
    {
        $metadata = $this->createMock(Metadata::class);
        $spec = $this->createMock(Spec::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            ['getSpec' => $spec, 'getDocument' => new DOMDocument()]
        );
        $normalizer = new Normalizers\MetadataNormalizer($factory);

        $actual = $normalizer->normalize($metadata);

        self::assertStringEqualsDomNode('<metadata></metadata>', $actual);
    }

    public function testNormalizeTimestamp(): void
    {
        $fakeDate = 'just-now';
        $timestamp = $this->createMock(DateTime::class);
        $metadata = $this->createConfiguredMock(
            Metadata::class,
            ['getTimestamp' => $timestamp]
        );
        $spec = $this->createMock(Spec::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'getDocument' => new DOMDocument(),
            ]
        );
        $normalizer = new Normalizers\MetadataNormalizer($factory);

        $timestamp->method('format')
            ->with('c')
            ->willReturn($fakeDate);

        $actual = $normalizer->normalize($metadata);

        self::assertStringEqualsDomNode(
            "<metadata><timestamp>$fakeDate</timestamp></metadata>",
            $actual
        );
    }

    public function testNormalizeTools(): void
    {
        $metadata = $this->createConfiguredMock(
            Metadata::class,
            [
                'getTools' => $this->createConfiguredMock(ToolRepository::class, ['count' => 2]),
            ]
        );
        $spec = $this->createMock(Spec::class);
        $toolsRepoFactory = $this->createMock(Normalizers\ToolRepositoryNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'getDocument' => new DOMDocument(),
                'makeForToolRepository' => $toolsRepoFactory,
            ]
        );
        $normalizer = new Normalizers\MetadataNormalizer($factory);

        $toolsRepoFactory->expects(self::once())
            ->method('normalize')
            ->with($metadata->getTools())
            ->willReturn([$factory->getDocument()->createElement('FakeTool', 'dummy')]);

        $actual = $normalizer->normalize($metadata);

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
        $metadata = $this->createConfiguredMock(
            Metadata::class,
            [
                'getComponent' => $this->createMock(Component::class),
            ]
        );
        $spec = $this->createMock(Spec::class);
        $componentNormalizer = $this->createMock(Normalizers\ComponentNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'getDocument' => new DOMDocument(),
                'makeForComponent' => $componentNormalizer,
            ]
        );
        $normalizer = new Normalizers\MetadataNormalizer($factory);

        $componentNormalizer->expects(self::once())
            ->method('normalize')
            ->with($metadata->getComponent())
            ->willReturn(
                $factory->getDocument()->createElement('FakeComponent', 'dummy'));

        $actual = $normalizer->normalize($metadata);

        self::assertStringEqualsDomNode(
            '<metadata><FakeComponent>dummy</FakeComponent></metadata>',
            $actual
        );
    }

    public function testNormalizeComponentUnsupported(): void
    {
        $metadata = $this->createConfiguredMock(
            Metadata::class,
            [
                'getComponent' => $this->createMock(Component::class),
            ]
        );
        $spec = $this->createMock(Spec::class);
        $componentNormalizer = $this->createMock(Normalizers\ComponentNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'getDocument' => new DOMDocument(),
                'makeForComponent' => $componentNormalizer,
            ]
        );
        $normalizer = new Normalizers\MetadataNormalizer($factory);

        $componentNormalizer->expects(self::once())
            ->method('normalize')
            ->with($metadata->getComponent())
            ->willThrowException(new DomainException());

        $actual = $normalizer->normalize($metadata);

        self::assertStringEqualsDomNode(
            '<metadata></metadata>',
            $actual
        );
    }

    public function testNormalizeProperties(): void
    {
        $metadata = $this->createConfiguredMock(
            Metadata::class,
            [
                'getProperties' => $this->createConfiguredMock(PropertyRepository::class, ['count' => 2]),
            ]
        );
        $spec = $this->createConfiguredMock(Spec::class, [
            'supportsMetadataProperties' => true,
        ]);
        $propertiesNormalizer = $this->createMock(Normalizers\PropertyRepositoryNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'getDocument' => new DOMDocument(),
                'makeForPropertyRepository' => $propertiesNormalizer,
            ]
        );
        $normalizer = new Normalizers\MetadataNormalizer($factory);

        $propertiesNormalizer->expects(self::once())
            ->method('normalize')
            ->with($metadata->getProperties())
            ->willReturn(
                [$factory->getDocument()->createElement('FakeProperties', 'dummy')]);

        $actual = $normalizer->normalize($metadata);

        self::assertStringEqualsDomNode(
            '<metadata><properties><FakeProperties>dummy</FakeProperties></properties></metadata>',
            $actual
        );
    }

    public function testNormalizePropertiesOmitEmpty(): void
    {
        $metadata = $this->createConfiguredMock(
            Metadata::class,
            [
                'getProperties' => $this->createConfiguredMock(PropertyRepository::class, ['count' => 0]),
            ]
        );
        $spec = $this->createConfiguredMock(Spec::class, [
            'supportsMetadataProperties' => true,
        ]);
        $propertiesNormalizer = $this->createMock(Normalizers\PropertyRepositoryNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'getDocument' => new DOMDocument(),
                'makeForPropertyRepository' => $propertiesNormalizer,
            ]
        );
        $normalizer = new Normalizers\MetadataNormalizer($factory);

        $actual = $normalizer->normalize($metadata);

        self::assertStringEqualsDomNode(
            '<metadata></metadata>',
            $actual
        );
    }
}
