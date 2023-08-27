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

use CycloneDX\Core\Models\Property;
use CycloneDX\Core\Serialization\DOM\_BaseNormalizer;
use CycloneDX\Core\Serialization\DOM\NormalizerFactory;
use CycloneDX\Core\Serialization\DOM\Normalizers\PropertyNormalizer;
use CycloneDX\Core\Spec\_SpecProtocol;
use CycloneDX\Tests\_traits\DomNodeAssertionTrait;
use DomainException;
use DOMDocument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PropertyNormalizer::class)]
#[UsesClass(_BaseNormalizer::class)]
class PropertyNormalizerTest extends TestCase
{
    use DomNodeAssertionTrait;

    public function testNormalizeFull(): void
    {
        $property = $this->createConfiguredMock(
            Property::class,
            [
                'getName' => 'myName',
                'getValue' => 'myValue',
            ]
        );
        $spec = $this->createMock(_SpecProtocol::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'getDocument' => new DOMDocument(),
            ]
        );
        $normalizer = new PropertyNormalizer($factory);

        $actual = $normalizer->normalize($property);

        self::assertStringEqualsDomNode(
            '<property name="myName">myValue</property>',
            $actual
        );
    }

    public function testNormalizeThrowsWhenNameEmpty(): void
    {
        $property = $this->createConfiguredMock(
            Property::class,
            [
                'getName' => '',
                'getValue' => 'myValue',
            ]
        );
        $spec = $this->createMock(_SpecProtocol::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'getDocument' => new DOMDocument(),
            ]
        );
        $normalizer = new PropertyNormalizer($factory);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/name/');

        $normalizer->normalize($property);
    }
}
