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

use CycloneDX\Core\Collections\PropertyRepository;
use CycloneDX\Core\Collections\ToolRepository;
use CycloneDX\Core\Models\Component;
use CycloneDX\Core\Models\Metadata;
use CycloneDX\Core\Serialization\JSON\NormalizerFactory;
use CycloneDX\Core\Serialization\JSON\Normalizers;
use CycloneDX\Core\Spec\Spec;
use DateTime;
use DateTimeZone;
use DomainException;
use PHPUnit\Framework\TestCase;


#[\PHPUnit\Framework\Attributes\CoversClass(\CycloneDX\Core\Serialization\JSON\Normalizers\MetadataNormalizer::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\CycloneDX\Core\Serialization\JSON\_BaseNormalizer::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(\CycloneDX\Core\Serialization\JSON\Normalizers\ComponentNormalizer::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(\CycloneDX\Core\Serialization\JSON\Normalizers\PropertyRepositoryNormalizer::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(\CycloneDX\Core\Serialization\JSON\Normalizers\PropertyRepositoryNormalizer::class)]

class MetadataNormalizerTest extends TestCase
{
    public function testNormalizeEmpty(): void
    {
        $metadata = $this->createMock(Metadata::class);
        $spec = $this->createMock(Spec::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, ['getSpec' => $spec]);
        $normalizer = new Normalizers\MetadataNormalizer($factory);

        $actual = $normalizer->normalize($metadata);

        self::assertSame([], $actual);
    }

    public function testNormalizeTimestamp(): void
    {
        $timeZone = new DateTimeZone('Pacific/Nauru');
        $timestamp = new DateTime('2000-01-01 00:00:00', $timeZone);
        $metadata = $this->createConfiguredMock(
            Metadata::class,
            ['getTimestamp' => $timestamp]
        );
        $spec = $this->createMock(Spec::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            ['getSpec' => $spec]
        );
        $normalizer = new Normalizers\MetadataNormalizer($factory);

        $actual = $normalizer->normalize($metadata);

        self::assertSame(
            ['timestamp' => '1999-12-31T12:00:00Z'],
            $actual,
            'not the expected Zulu time'
        );
        self::assertSame(
            '2000-01-01T00:00:00+12:00',
            $timestamp->format('c'),
            'timestamp was modified'
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
                'makeForToolRepository' => $toolsRepoFactory,
            ]
        );
        $normalizer = new Normalizers\MetadataNormalizer($factory);

        $toolsRepoFactory->expects(self::once())
            ->method('normalize')
            ->with($metadata->getTools())
            ->willReturn(['FakeTool' => 'dummy']);

        $actual = $normalizer->normalize($metadata);

        self::assertSame(
            ['tools' => ['FakeTool' => 'dummy']],
            $actual
        );
    }


    public function testNormalizeComponent(): void
    {
        $metadata = $this->createConfiguredMock(
            Metadata::class,
            [
                'getComponent' => $this->createMock(Component::class),
            ]
        );
        $spec = $this->createMock(Spec::class);
        $componentFactory = $this->createMock(Normalizers\ComponentNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'makeForComponent' => $componentFactory,
            ]
        );
        $normalizer = new Normalizers\MetadataNormalizer($factory);

        $componentFactory->expects(self::once())
            ->method('normalize')
            ->with($metadata->getComponent())
            ->willReturn(['FakeComponent' => 'dummy']);

        $actual = $normalizer->normalize($metadata);

        self::assertSame(
            ['component' => ['FakeComponent' => 'dummy']],
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
        $componentFactory = $this->createMock(Normalizers\ComponentNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'makeForComponent' => $componentFactory,
            ]
        );
        $normalizer = new Normalizers\MetadataNormalizer($factory);

        $componentFactory->expects(self::once())
            ->method('normalize')
            ->with($metadata->getComponent())
            ->willThrowException(new DomainException());

        $actual = $normalizer->normalize($metadata);

        self::assertSame([], $actual);
    }

    /**
     */
    public function testNormalizeProperties(): void
    {
        $metadata = $this->createConfiguredMock(
            Metadata::class,
            [
                'getProperties' => $this->createConfiguredMock(PropertyRepository::class, ['count' => 2]),
            ]
        );
        $spec = $this->createConfiguredMock(Spec::class, ['supportsMetadataProperties' => true]);
        $repoNormalizer = $this->createMock(Normalizers\PropertyRepositoryNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'makeForPropertyRepository' => $repoNormalizer,
            ]
        );
        $normalizer = new Normalizers\MetadataNormalizer($factory);

        $repoNormalizer->expects(self::once())
            ->method('normalize')
            ->with($metadata->getProperties())
            ->willReturn([['FakeProperty' => 'dummy']]);

        $actual = $normalizer->normalize($metadata);

        self::assertSame(
            ['properties' => [['FakeProperty' => 'dummy']]],
            $actual
        );
    }

    /**
     */
    public function testNormalizePropertiesOmitWhenEmpty(): void
    {
        $metadata = $this->createConfiguredMock(
            Metadata::class,
            [
                'getProperties' => $this->createConfiguredMock(PropertyRepository::class, ['count' => 0]),
            ]
        );
        $spec = $this->createConfiguredMock(Spec::class, ['supportsMetadataProperties' => true]);
        $repoNormalizer = $this->createMock(Normalizers\PropertyRepositoryNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'makeForPropertyRepository' => $repoNormalizer,
            ]
        );
        $normalizer = new Normalizers\MetadataNormalizer($factory);

        $actual = $normalizer->normalize($metadata);

        self::assertSame([], $actual);
    }
}
