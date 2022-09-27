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

use CycloneDX\Core\Models\Bom;
use CycloneDX\Core\Serialization\JsonSerializer;
use CycloneDX\Core\Spec\Spec;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Serialization\JsonSerializer
 *
 * @uses   \CycloneDX\Core\Serialization\BaseSerializer
 */
class JsonSerializerTest extends TestCase
{
    /**
     * @uses   \CycloneDX\Core\Serialization\JSON\_BaseNormalizer
     * @uses   \CycloneDX\Core\Serialization\JSON\NormalizerFactory
     * @uses   \CycloneDX\Core\Serialization\JSON\Normalizers\BomNormalizer
     * @uses   \CycloneDX\Core\Serialization\JSON\Normalizers\ComponentRepositoryNormalizer
     * @uses   \CycloneDX\Core\Serialization\JSON\Normalizers\ComponentNormalizer
     * @uses   \CycloneDX\Core\Serialization\BomRefDiscriminator
     */
    public function testSerialize12(): void
    {
        $spec = $this->createConfiguredMock(
            Spec::class,
            [
                'getVersion' => '1.2',
                'isSupportedFormat' => true,
            ]
        );
        $serializer = new JsonSerializer($spec);
        $bom = $this->createStub(Bom::class);

        $actual = $serializer->serialize($bom);

        self::assertJsonStringEqualsJsonString(
            <<<'JSON'
                {
                    "$schema": "http://cyclonedx.org/schema/bom-1.2a.schema.json",
                    "bomFormat": "CycloneDX",
                    "specVersion": "1.2",
                    "version": 0,
                    "components": []
                }
                JSON,
            $actual
        );
    }

    /**
     * @uses   \CycloneDX\Core\Serialization\JSON\_BaseNormalizer
     * @uses   \CycloneDX\Core\Serialization\JSON\NormalizerFactory
     * @uses   \CycloneDX\Core\Serialization\JSON\Normalizers\BomNormalizer
     * @uses   \CycloneDX\Core\Serialization\JSON\Normalizers\ComponentRepositoryNormalizer
     * @uses   \CycloneDX\Core\Serialization\JSON\Normalizers\ComponentNormalizer
     * @uses   \CycloneDX\Core\Serialization\BomRefDiscriminator
     */
    public function testSerialize13(): void
    {
        $spec = $this->createConfiguredMock(
            Spec::class,
            [
                'getVersion' => '1.3',
                'isSupportedFormat' => true,
            ]
        );
        $serializer = new JsonSerializer($spec);
        $bom = $this->createStub(Bom::class);

        $actual = $serializer->serialize($bom);

        self::assertJsonStringEqualsJsonString(
            <<<'JSON'
                {
                    "$schema": "http://cyclonedx.org/schema/bom-1.3.schema.json",
                    "bomFormat": "CycloneDX",
                    "specVersion": "1.3",
                    "version": 0,
                    "components": []
                }
                JSON,
            $actual
        );
    }

    /**
     * @uses   \CycloneDX\Core\Serialization\JSON\_BaseNormalizer
     * @uses   \CycloneDX\Core\Serialization\JSON\NormalizerFactory
     * @uses   \CycloneDX\Core\Serialization\JSON\Normalizers\BomNormalizer
     * @uses   \CycloneDX\Core\Serialization\JSON\Normalizers\ComponentRepositoryNormalizer
     * @uses   \CycloneDX\Core\Serialization\JSON\Normalizers\ComponentNormalizer
     * @uses   \CycloneDX\Core\Serialization\BomRefDiscriminator
     */
    public function testSerialize14(): void
    {
        $spec = $this->createConfiguredMock(
            Spec::class,
            [
                'getVersion' => '1.4',
                'isSupportedFormat' => true,
            ]
        );
        $serializer = new JsonSerializer($spec);
        $bom = $this->createStub(Bom::class);

        $actual = $serializer->serialize($bom);

        self::assertJsonStringEqualsJsonString(
            <<<'JSON'
                {
                    "$schema": "http://cyclonedx.org/schema/bom-1.4.schema.json",
                    "bomFormat": "CycloneDX",
                    "specVersion": "1.4",
                    "version": 0,
                    "components": []
                }
                JSON,
            $actual
        );
    }
}