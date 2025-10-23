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

namespace CycloneDX\Tests\Core;

use CycloneDX\Core\Models\Bom;
use CycloneDX\Core\Serialization\JSON;
use CycloneDX\Core\Serialization\JsonSerializer;
use CycloneDX\Core\Spec\SpecFactory;
use CycloneDX\Core\Validation\Validators\JsonStrictValidator;
use CycloneDX\Tests\_data\BomModelProvider;
use CycloneDX\Tests\_traits\SnapshotTrait;
use DomainException;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\TestCase;

/**
 * This test might be slow.
 * This test might require online-connectivity.
 * Tests functionality.
 */
#[Large]
#[CoversNothing]
class SerializeToJsonIntegrationTest extends TestCase
{
    use SnapshotTrait;

    // region Spec 1.0
    // Spec 1.0 is not implemented
    // endregion Spec 1.0

    // region Spec 1.1

    /**
     * Schema 1.1 is not specified as JSON.
     */
    public function testSerialization11(): void
    {
        $spec = SpecFactory::make1dot1();

        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/unsupported format "JSON"/i');

        $serializer = new JsonSerializer(new JSON\NormalizerFactory($spec));

        $serializer->serialize(new Bom());
    }

    // endregion Spec 1.1

    // region Spec 1.2

    #[DataProviderExternal(BomModelProvider::class, 'allBomTestData')]
    public function testSchema12(Bom $bom): void
    {
        $spec = SpecFactory::make1dot2();
        $serializer = new JsonSerializer(new JSON\NormalizerFactory($spec));
        $validator = new JsonStrictValidator($spec);

        $json = $serializer->serialize($bom, true);
        $validationErrors = $validator->validateString($json);

        self::assertNull($validationErrors);
        if (!str_contains($this->dataName(), 'random')) {
            self::assertStringEqualsSnapshot(__CLASS__.'-'.$this->name().'-'.$this->dataName().'.json', $json);
        }
    }

    // endregion Spec 1.2

    // region Spec 1.3

    #[DataProviderExternal(BomModelProvider::class, 'allBomTestData')]
    public function testSchema13(Bom $bom): void
    {
        $spec = SpecFactory::make1dot3();
        $serializer = new JsonSerializer(new JSON\NormalizerFactory($spec));
        $validator = new JsonStrictValidator($spec);

        $json = $serializer->serialize($bom, true);
        $validationErrors = $validator->validateString($json);

        self::assertNull($validationErrors);
        if (!str_contains($this->dataName(), 'random')) {
            self::assertStringEqualsSnapshot(__CLASS__.'-'.$this->name().'-'.$this->dataName().'.json', $json);
        }
    }

    // endregion Spec 1.3

    // region Spec 1.4

    #[DataProviderExternal(BomModelProvider::class, 'allBomTestData')]
    public function testSchema14(Bom $bom): void
    {
        $spec = SpecFactory::make1dot4();
        $serializer = new JsonSerializer(new JSON\NormalizerFactory($spec));
        $validator = new JsonStrictValidator($spec);

        $json = $serializer->serialize($bom, true);
        $validationErrors = $validator->validateString($json);

        self::assertNull($validationErrors);
        if (!str_contains($this->dataName(), 'random')) {
            self::assertStringEqualsSnapshot(__CLASS__.'-'.$this->name().'-'.$this->dataName().'.json', $json);
        }
    }

    // endregion Spec 1.4

    // region Spec 1.5

    #[DataProviderExternal(BomModelProvider::class, 'allBomTestData')]
    public function testSchema15(Bom $bom): void
    {
        $spec = SpecFactory::make1dot5();
        $serializer = new JsonSerializer(new JSON\NormalizerFactory($spec));
        $validator = new JsonStrictValidator($spec);

        $json = $serializer->serialize($bom, true);
        $validationErrors = $validator->validateString($json);

        self::assertNull($validationErrors);
        if (!str_contains($this->dataName(), 'random')) {
            self::assertStringEqualsSnapshot(__CLASS__.'-'.$this->name().'-'.$this->dataName().'.json', $json);
        }
    }

    // endregion Spec 1.5

    // region Spec 1.6

    #[DataProviderExternal(BomModelProvider::class, 'allBomTestData')]
    public function testSchema16(Bom $bom): void
    {
        $spec = SpecFactory::make1dot6();
        $serializer = new JsonSerializer(new JSON\NormalizerFactory($spec));
        $validator = new JsonStrictValidator($spec);

        $json = $serializer->serialize($bom, true);
        $validationErrors = $validator->validateString($json);

        self::assertNull($validationErrors);
        if (!str_contains($this->dataName(), 'random')) {
            self::assertStringEqualsSnapshot(__CLASS__.'-'.$this->name().'-'.$this->dataName().'.json', $json);
        }
    }

    // endregion Spec 1.6

    // region Spec 1.7

    #[DataProviderExternal(BomModelProvider::class, 'allBomTestData')]
    public function testSchema17(Bom $bom): void
    {
        $spec = SpecFactory::make1dot7();
        $serializer = new JsonSerializer(new JSON\NormalizerFactory($spec));
        $validator = new JsonStrictValidator($spec);

        $json = $serializer->serialize($bom, true);
        $validationErrors = $validator->validateString($json);

        self::assertNull($validationErrors);
        if (!str_contains($this->dataName(), 'random')) {
            self::assertStringEqualsSnapshot(__CLASS__.'-'.$this->name().'-'.$this->dataName().'.json', $json);
        }
    }

    // endregion Spec 1.7
}
