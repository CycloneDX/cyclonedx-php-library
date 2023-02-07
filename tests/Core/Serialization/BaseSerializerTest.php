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

namespace CycloneDX\Tests\Core\Serialization;

use CycloneDX\Core\Collections\BomRefRepository;
use CycloneDX\Core\Collections\ComponentRepository;
use CycloneDX\Core\Models\Bom;
use CycloneDX\Core\Models\BomRef;
use CycloneDX\Core\Models\Component;
use CycloneDX\Core\Models\Metadata;
use CycloneDX\Core\Serialization\BaseSerializer;
use Exception;
use Generator;
use PHPUnit\Framework\TestCase;
use Throwable;

#[\PHPUnit\Framework\Attributes\CoversClass(\CycloneDX\Core\Serialization\BaseSerializer::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(\CycloneDX\Core\Serialization\BomRefDiscriminator::class)]
class BaseSerializerTest extends TestCase
{
    public function testSerialize(): void
    {
        $prettyPrint = [null, true, false][random_int(0, 2)];
        $normalized = uniqid('normalized', true);
        $serialized = uniqid('serialized', true);
        $bom = $this->createStub(Bom::class);
        $serializer = $this->getMockForAbstractClass(BaseSerializer::class);
        $serializer->expects(self::once())
            ->method('realNormalize')
            ->with($bom)
            ->willReturn($normalized);
        $serializer->expects(self::once())
            ->method('realSerialize')
            ->with($normalized, $prettyPrint)
            ->willReturn($serialized);

        $actual = $serializer->serialize($bom, $prettyPrint);

        self::assertSame($serialized, $actual);
    }

    public function testSerializeForwardsExceptionsFromRealNormalize(): void
    {
        $bom = $this->createStub(Bom::class);
        $exception = $this->createStub(Exception::class);
        $serializer = $this->getMockForAbstractClass(BaseSerializer::class);
        $serializer->expects(self::once())
            ->method('realNormalize')
            ->willThrowException($exception);
        $serializer->expects(self::never())
            ->method('realSerialize');

        $this->expectExceptionObject($exception);

        $serializer->serialize($bom);
    }

    public function testSerializeForwardsExceptionsFromRealSerializer(): void
    {
        $exception = $this->createStub(Exception::class);
        $bom = $this->createStub(Bom::class);
        $serializer = $this->getMockForAbstractClass(BaseSerializer::class);
        $serializer->expects(self::once())
            ->method('realNormalize');
        $serializer->expects(self::once())
            ->method('realSerialize')
            ->willThrowException($exception);

        $this->expectExceptionObject($exception);

        $serializer->serialize($bom);
    }

    /**
     * @param BomRef[] $allBomRefs
     *
     * @dataProvider dpBomWithRefs
     *
     *
     * @uses         \CycloneDX\Core\Models\BomRef
     */
    public function testSerializeUsesUniqueBomRefsAndResetThemAfterwards(Bom $bom, array $allBomRefs): void
    {
        $allBomRefsValuesOriginal = [];
        foreach ($allBomRefs as $bomRef) {
            $allBomRefsValuesOriginal[] = [$bomRef, $bomRef->getValue()];
        }
        $allBomRefsValuesOnNormalize = [];
        $normalized = uniqid('normalized', true);
        $serialized = uniqid('serialized', true);
        $serializer = $this->getMockForAbstractClass(BaseSerializer::class);
        $serializer->expects(self::once())
            ->method('realNormalize')
            ->with($bom)
            ->willReturnCallback(
                function () use ($allBomRefsValuesOriginal, &$allBomRefsValuesOnNormalize, $normalized) {
                    /**
                     * @var BomRef $bomRef
                     */
                    foreach ($allBomRefsValuesOriginal as [$bomRef]) {
                        $allBomRefsValuesOnNormalize[] = [$bomRef, $bomRef->getValue()];
                    }

                    return $normalized;
                }
            );
        $serializer->expects(self::once())
            ->method('realSerialize')
            ->with($normalized)
            ->willReturn($serialized);

        $actual = $serializer->serialize($bom);

        foreach ($allBomRefsValuesOriginal as [$bomRef, $bomRefValueOriginal]) {
            self::assertSame($bomRefValueOriginal, $bomRef->getValue());
        }
        $valuesOnNormalize = array_column($allBomRefsValuesOnNormalize, 1);
        self::assertSameSize(
            $valuesOnNormalize,
            array_unique($valuesOnNormalize, \SORT_STRING),
            'some values were found not unique in:'.\PHP_EOL.
            print_r($valuesOnNormalize, true)
        );
        self::assertSame($serialized, $actual);
    }

    /**
     * @param BomRef[] $allBomRefs
     *
     * @dataProvider dpBomWithRefs
     *
     *
     * @uses         \CycloneDX\Core\Models\BomRef
     */
    public function testSerializeUsesUniqueBomRefsAndResetThemOnThrow(Bom $bom, array $allBomRefs): void
    {
        $allBomRefsValuesOriginal = [];
        foreach ($allBomRefs as $bomRef) {
            $allBomRefsValuesOriginal[] = [$bomRef, $bomRef->getValue()];
        }
        $allBomRefsValuesOnNormalize = [];
        $exception = $this->createStub(Exception::class);
        $serializer = $this->getMockForAbstractClass(BaseSerializer::class);
        $serializer->expects(self::once())
            ->method('realNormalize')
            ->with($bom)
            ->willReturnCallback(
                function () use ($allBomRefsValuesOriginal, &$allBomRefsValuesOnNormalize, $exception): void {
                    /**
                     * @var BomRef $bomRef
                     */
                    foreach ($allBomRefsValuesOriginal as [$bomRef]) {
                        $allBomRefsValuesOnNormalize[] = [$bomRef, $bomRef->getValue()];
                    }

                    throw $exception;
                }
            );

        $caught = null;
        try {
            $serializer->serialize($bom);
        } catch (Throwable $caught) {
            // pass on
        }

        self::assertSame($exception, $caught);
        foreach ($allBomRefsValuesOriginal as [$bomRef, $bomRefValueOriginal]) {
            self::assertSame($bomRefValueOriginal, $bomRef->getValue());
        }
        $valuesOnNormalize = array_column($allBomRefsValuesOnNormalize, 1);
        self::assertSameSize(
            $valuesOnNormalize,
            array_unique($valuesOnNormalize, \SORT_STRING),
            'some values were found not unique in:'.\PHP_EOL.
            print_r($valuesOnNormalize, true)
        );
    }

    public function dpBomWithRefs(): Generator
    {
        $dependencies = $this->createStub(BomRefRepository::class);

        foreach (['null' => null, 'common string' => 'foo'] as $name => $bomRefValue) {
            $componentNullDeps = $this->createConfiguredMock(
                Component::class,
                [
                    'getBomRef' => new BomRef($bomRefValue),
                    'getDependencies' => $dependencies,
                ]
            );
            $componentEmptyDeps = $this->createConfiguredMock(
                Component::class,
                [
                    'getBomRef' => new BomRef($bomRefValue),
                    'getDependencies' => $this->createMock(BomRefRepository::class),
                ]
            );
            $componentKnownDeps = $this->createConfiguredMock(
                Component::class,
                [
                    'getBomRef' => new BomRef($bomRefValue),
                    'getDependencies' => $this->createConfiguredMock(
                        BomRefRepository::class,
                        [
                            'getItems' => [$componentNullDeps->getBomRef()],
                        ]
                    ),
                ]
            );
            $componentRoot = $this->createConfiguredMock(
                Component::class,
                [
                    'getBomRef' => new BomRef($bomRefValue),
                    'getDependencies' => $this->createConfiguredMock(
                        BomRefRepository::class,
                        [
                            'getItems' => [
                                $componentKnownDeps->getBomRef(),
                                $componentEmptyDeps->getBomRef(),
                            ],
                        ]
                    ),
                ]
            );

            yield "bom with components and meta: bomRef=$name" => [
                $this->createConfiguredMock(
                    Bom::class,
                    [
                        'getComponents' => $this->createConfiguredMock(
                            ComponentRepository::class,
                            [
                                'getItems' => [
                                    $componentNullDeps,
                                    $componentEmptyDeps,
                                    $componentKnownDeps,
                                ],
                            ]
                        ),
                        'getMetadata' => $this->createConfiguredMock(
                            Metadata::class,
                            [
                                'getComponent' => $componentRoot,
                            ]
                        ),
                    ]
                ),
                [
                    $componentRoot->getBomRef(),
                    $componentNullDeps->getBomRef(),
                    $componentEmptyDeps->getBomRef(),
                    $componentKnownDeps->getBomRef(),
                ],
            ];
        }
    }
}
