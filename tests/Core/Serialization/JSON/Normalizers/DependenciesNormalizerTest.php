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

use CycloneDX\Core\Collections\BomRefRepository;
use CycloneDX\Core\Collections\ComponentRepository;
use CycloneDX\Core\Models\Bom;
use CycloneDX\Core\Models\BomRef;
use CycloneDX\Core\Models\Component;
use CycloneDX\Core\Models\Metadata;
use CycloneDX\Core\Serialization\JSON\NormalizerFactory;
use CycloneDX\Core\Serialization\JSON\Normalizers\DependenciesNormalizer;
use Exception;
use Generator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Serialization\JSON\Normalizers\DependenciesNormalizer
 * @covers \CycloneDX\Core\Serialization\JSON\_BaseNormalizer
 */
class DependenciesNormalizerTest extends TestCase
{
    /**
     * @var NormalizerFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $factory;

    /**
     * @var DependenciesNormalizer
     */
    private $normalizer;

    protected function setUp(): void
    {
        $this->factory = $this->createMock(NormalizerFactory::class);
        $this->normalizer = new DependenciesNormalizer($this->factory);
    }

    /**
     * @param string[] $expecteds
     *
     * @dataProvider dpNormalize
     *
     * @uses         \CycloneDX\Core\Models\BomRef
     */
    public function testNormalize(Bom $bom, array $expecteds): void
    {
        $actuals = $this->normalizer->normalize($bom);

        self::assertSameSize($expecteds, $actuals);

        $missing = [];
        foreach ($expecteds as $expected) {
            foreach ($actuals as $actual) {
                try {
                    self::assertEquals($expected, $actual);
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
            sprintf("missing:\n%s\nin:\n%s",
                print_r($missing, true),
                print_r($actuals, true),
            )
        );
    }

    public function dpNormalize(): Generator
    {
        $dependencies = $this->createStub(BomRefRepository::class);

        $componentWithoutBomRefValue = $this->createConfiguredMock(
            Component::class,
            [
                'getBomRef' => new BomRef(null),
                'getDependencies' => $dependencies,
            ]
        );

        $componentWithoutDeps = $this->createConfiguredMock(
            Component::class,
            [
                'getBomRef' => new BomRef('ComponentWithoutDeps'),
                'getDependencies' => $dependencies,
            ]
        );
        $componentWithNoDeps = $this->createConfiguredMock(
            Component::class,
            [
                'getBomRef' => new BomRef('ComponentWithNoDeps'),
                'getDependencies' => $this->createConfiguredMock(
                    BomRefRepository::class,
                    ['getItems' => []]
                ),
            ]
        );
        $componentWithDeps = $this->createConfiguredMock(
            Component::class,
            [
                'getBomRef' => new BomRef('ComponentWithDeps'),
                'getDependencies' => $this->createConfiguredMock(
                    BomRefRepository::class,
                    [
                        'getItems' => [
                            $componentWithoutDeps->getBomRef(),
                            $componentWithNoDeps->getBomRef(),
                        ],
                    ]
                ),
            ]
        );
        $rootComponent = $this->createConfiguredMock(
            Component::class,
            [
                'getBomRef' => new BomRef('myRootComponent'),
                'getDependencies' => $this->createConfiguredMock(
                    BomRefRepository::class,
                    [
                        'getItems' => [
                            $componentWithDeps->getBomRef(),
                            $componentWithoutDeps->getBomRef(),
                            $componentWithoutBomRefValue->getBomRef(),
                            new BomRef('SomethingOutsideOfTheActualBom'),
                        ],
                    ]
                ),
            ]
        );

        $bom = $this->createConfiguredMock(
            Bom::class,
            [
                'getComponents' => $this->createConfiguredMock(
                    ComponentRepository::class,
                    [
                        'getItems' => [
                            $componentWithoutDeps,
                            $componentWithNoDeps,
                            $componentWithDeps,
                            $componentWithoutBomRefValue,
                        ],
                    ]
                ),
                'getMetadata' => $this->createConfiguredMock(
                    Metadata::class,
                    [
                        'getComponent' => $rootComponent,
                    ]
                ),
            ]
        );

        yield 'with metadata' => [
            $bom,
            [
                [
                    'ref' => 'myRootComponent',
                    'dependsOn' => [
                        'ComponentWithDeps',
                        'ComponentWithoutDeps',
                    ],
                ],
                ['ref' => 'ComponentWithoutDeps'],
                ['ref' => 'ComponentWithNoDeps'],
                [
                    'ref' => 'ComponentWithDeps',
                    'dependsOn' => [
                        'ComponentWithoutDeps',
                        'ComponentWithNoDeps',
                    ],
                ],
                // $componentWithoutBomRefValue is expected to be skipped
            ],
        ];
    }
}
