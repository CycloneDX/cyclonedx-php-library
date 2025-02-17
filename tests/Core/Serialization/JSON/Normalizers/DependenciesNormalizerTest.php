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

use CycloneDX\Core\Models\Bom;
use CycloneDX\Core\Models\BomRef;
use CycloneDX\Core\Serialization\JSON\_BaseNormalizer;
use CycloneDX\Core\Serialization\JSON\NormalizerFactory;
use CycloneDX\Core\Serialization\JSON\Normalizers\DependenciesNormalizer;
use CycloneDX\Tests\Core\Serialization\_TestCommon;
use Exception;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(DependenciesNormalizer::class)]
#[CoversClass(_BaseNormalizer::class)]
#[UsesClass(BomRef::class)]
class DependenciesNormalizerTest extends TestCase
{
    private NormalizerFactory&MockObject $factory;

    private DependenciesNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->factory = $this->createMock(NormalizerFactory::class);
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
                [
                    'ref' => 'myRootComponent',
                    'dependsOn' => [
                        'ComponentWithDeps',
                        'ComponentWithoutDeps',
                    ],
                ],
                // $componentWithoutDeps
                ['ref' => 'ComponentWithoutDeps'],
                // $componentWithNoDeps
                ['ref' => 'ComponentWithNoDeps'],
                // $componentWithDeps
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
