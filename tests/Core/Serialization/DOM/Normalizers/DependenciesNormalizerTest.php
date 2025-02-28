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

use CycloneDX\Core\Collections\BomRefRepository;
use CycloneDX\Core\Collections\ComponentRepository;
use CycloneDX\Core\Models\Bom;
use CycloneDX\Core\Models\BomRef;
use CycloneDX\Core\Models\Component;
use CycloneDX\Core\Models\Metadata;
use CycloneDX\Core\Serialization\DOM\_BaseNormalizer;
use CycloneDX\Core\Serialization\DOM\NormalizerFactory;
use CycloneDX\Core\Serialization\DOM\Normalizers\DependenciesNormalizer;
use CycloneDX\Tests\_traits\DomNodeAssertionTrait;
use CycloneDX\Tests\Core\Serialization\_TestCommon;
use DOMDocument;
use Exception;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(DependenciesNormalizer::class)]
#[CoversClass(_BaseNormalizer::class)]
#[UsesClass(Bom::class)]
#[UsesClass(BomRef::class)]
#[UsesClass(BomRefRepository::class)]
#[UsesClass(Component::class)]
#[UsesClass(ComponentRepository::class)]
#[UsesClass(Metadata::class)]
class DependenciesNormalizerTest extends TestCase
{
    use DomNodeAssertionTrait;

    private NormalizerFactory&MockObject $factory;

    private DependenciesNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getDocument' => new DOMDocument(),
            ]
        );
        $this->normalizer = new DependenciesNormalizer($this->factory);
    }

    /**
     * @param string[] $expecteds
     */
    #[DataProvider('dpNormalize')]
    public function testNormalize(Bom $bom, array $expecteds): void
    {
        $actuals = $this->normalizer->normalize($bom);

        self::assertSameSize($expecteds, $actuals);

        $missing = [];
        foreach ($expecteds as $expected) {
            foreach ($actuals as $actual) {
                try {
                    self::assertStringEqualsDomNode($expected, $actual);
                    continue 2; // expected was found
                } catch (Exception $exception) {
                    // pass
                }
            }
            $missing[] = $expected;
        }

        self::assertCount(
            0,
            $missing,
            \sprintf("missing:\n%s\nin:\n%s",
                print_r($missing, true),
                print_r($actuals, true),
            )
        );
    }

    public static function dpNormalize(): Generator
    {
        $boms = iterator_to_array(_TestCommon::BomsForDpNormalize());

        yield 'with metadata' => [
            $boms['with metadata'],
            [
                // $rootComponent
                '<dependency ref="myRootComponent">'.
                '<dependency ref="ComponentWithDeps"></dependency>'.
                '<dependency ref="ComponentWithoutDeps"></dependency>'.
                '</dependency>',
                // $componentWithoutDeps
                '<dependency ref="ComponentWithoutDeps"></dependency>',
                // $componentWithNoDeps
                '<dependency ref="ComponentWithNoDeps"></dependency>',
                // $componentWithDeps
                '<dependency ref="ComponentWithDeps">'.
                '<dependency ref="ComponentWithoutDeps"></dependency>'.
                '<dependency ref="ComponentWithNoDeps"></dependency>'.
                '</dependency>',
                // $componentWithoutBomRefValue is expected to be skipped
            ],
        ];
    }
}
