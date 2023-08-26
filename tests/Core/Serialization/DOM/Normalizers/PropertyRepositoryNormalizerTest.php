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
use CycloneDX\Core\Models\Property;
use CycloneDX\Core\Serialization\DOM\_BaseNormalizer;
use CycloneDX\Core\Serialization\DOM\NormalizerFactory;
use CycloneDX\Core\Serialization\DOM\Normalizers\PropertyNormalizer;
use CycloneDX\Core\Serialization\DOM\Normalizers\PropertyRepositoryNormalizer;
use CycloneDX\Core\Spec\_SpecProtocol;
use CycloneDX\Tests\_traits\DomNodeAssertionTrait;
use DomainException;
use DOMDocument;
use DOMElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PropertyRepositoryNormalizer::class)]
#[CoversClass(_BaseNormalizer::class)]
#[UsesClass(PropertyNormalizer::class)]
class PropertyRepositoryNormalizerTest extends TestCase
{
    use DomNodeAssertionTrait;

    public function testNormalizeEmpty(): void
    {
        $spec = $this->createStub(_SpecProtocol::class);
        $propertyNormalizer = $this->createMock(PropertyNormalizer::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $spec,
            'getDocument' => new DOMDocument(),
            'makeForProperty' => $propertyNormalizer,
        ]);

        $normalizer = new PropertyRepositoryNormalizer($factory);
        $properties = $this->createConfiguredMock(PropertyRepository::class, ['count' => 0]);

        $actual = $normalizer->normalize($properties);

        self::assertSame([], $actual);
    }

    public function testNormalize(): void
    {
        $spec = $this->createStub(_SpecProtocol::class);
        $propertyNormalizer = $this->createMock(PropertyNormalizer::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $spec,
            'getDocument' => new DOMDocument(),
            'makeForProperty' => $propertyNormalizer,
        ]);
        $normalizer = new PropertyRepositoryNormalizer($factory);
        $property = $this->createStub(Property::class);
        $properties = $this->createConfiguredMock(PropertyRepository::class, [
            'count' => 1,
            'getItems' => [$property],
        ]);

        $FakeProperty = $this->createStub(DOMElement::class);

        $propertyNormalizer->expects(self::once())->method('normalize')
            ->with($property)
            ->willReturn($FakeProperty);

        $actual = $normalizer->normalize($properties);

        self::assertSame([$FakeProperty], $actual);
    }

    public function testNormalizeSkippedWhenThrown(): void
    {
        $spec = $this->createStub(_SpecProtocol::class);
        $propertyNormalizer = $this->createMock(PropertyNormalizer::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $spec,
            'getDocument' => new DOMDocument(),
            'makeForProperty' => $propertyNormalizer,
        ]);
        $normalizer = new PropertyRepositoryNormalizer($factory);
        $property = $this->createStub(Property::class);
        $properties = $this->createConfiguredMock(PropertyRepository::class, [
            'count' => 1,
            'getItems' => [$property],
        ]);

        $propertyNormalizer->expects(self::once())->method('normalize')
            ->with($property)
            ->willThrowException(new DomainException());

        $actual = $normalizer->normalize($properties);

        self::assertSame([], $actual);
    }
}
