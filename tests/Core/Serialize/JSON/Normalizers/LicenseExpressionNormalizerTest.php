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

namespace CycloneDX\Tests\Core\Serialize\JSON\Normalizers;

use CycloneDX\Core\Models\License\LicenseExpression;
use CycloneDX\Core\Serialize\JSON\NormalizerFactory;
use CycloneDX\Core\Serialize\JSON\Normalizers\LicenseExpressionNormalizer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Serialize\JSON\Normalizers\LicenseExpressionNormalizer
 * @covers \CycloneDX\Core\Serialize\JSON\AbstractNormalizer
 */
class LicenseExpressionNormalizerTest extends TestCase
{
    public function testConstructor(): LicenseExpressionNormalizer
    {
        $factory = $this->createStub(NormalizerFactory::class);

        $normalizer = new LicenseExpressionNormalizer($factory);
        self::assertSame($factory, $normalizer->getNormalizerFactory());

        return $normalizer;
    }

    /**
     * @depends testConstructor
     */
    public function testNormalize(LicenseExpressionNormalizer $normalizer): void
    {
        $license = $this->createMock(LicenseExpression::class);
        $license->method('getExpression')->willReturn('foo');

        $normalized = $normalizer->normalize($license);

        self::assertSame(['expression' => 'foo'], $normalized);
    }
}
