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

use CycloneDX\Core\Collections\LicenseRepository;
use CycloneDX\Core\Models\License\LicenseExpression;
use CycloneDX\Core\Models\License\NamedLicense;
use CycloneDX\Core\Models\License\SpdxLicense;
use CycloneDX\Core\Serialization\JSON\_BaseNormalizer;
use CycloneDX\Core\Serialization\JSON\NormalizerFactory;
use CycloneDX\Core\Serialization\JSON\Normalizers\LicenseNormalizer;
use CycloneDX\Core\Serialization\JSON\Normalizers\LicenseRepositoryNormalizer;
use CycloneDX\Core\Spec\_SpecProtocol;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LicenseRepositoryNormalizer::class)]
#[CoversClass(_BaseNormalizer::class)]
class LicenseRepositoryNormalizerTest extends TestCase
{
    public function testNormalizeEmpty(): void
    {
        $spec = $this->createStub(_SpecProtocol::class);
        $licenseNormalizer = $this->createMock(LicenseNormalizer::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $spec,
            'makeForLicense' => $licenseNormalizer,
        ]);
        $normalizer = new LicenseRepositoryNormalizer($factory);
        $repo = $this->createConfiguredMock(LicenseRepository::class, ['count' => 0]);

        $actual = $normalizer->normalize($repo);

        self::assertSame([], $actual);
    }

    public function testNormalize(): void
    {
        $spec = $this->createStub(_SpecProtocol::class);
        $licenseNormalizer = $this->createMock(LicenseNormalizer::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $spec,
            'makeForLicense' => $licenseNormalizer,
        ]);
        $normalizer = new LicenseRepositoryNormalizer($factory);
        $license = $this->createStub(NamedLicense::class);
        $licenses = $this->createConfiguredMock(LicenseRepository::class, [
            'count' => 1,
            'getItems' => [$license],
        ]);

        $licenseNormalizer->method('normalize')
            ->with($license)
            ->willReturn(['FakeLicense' => true]);

        $actual = $normalizer->normalize($licenses);

        self::assertSame([['FakeLicense' => true]], $actual);
    }

    public function testNormalizePreferExpression(): void
    {
        $spec = $this->createStub(_SpecProtocol::class);
        $licenseNormalizer = $this->createMock(LicenseNormalizer::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $spec,
            'makeForLicense' => $licenseNormalizer,
        ]);
        $normalizer = new LicenseRepositoryNormalizer($factory);
        $licenseNamed = $this->createStub(NamedLicense::class);
        $licenseSpdx = $this->createStub(SpdxLicense::class);
        $licenseExpression = $this->createStub(LicenseExpression::class);
        $licenses = $this->createConfiguredMock(LicenseRepository::class, [
            'count' => 1,
            'getItems' => [$licenseSpdx, $licenseNamed, $licenseExpression],
        ]);

        $licenseNormalizer->method('normalize')
            ->with($licenseExpression)
            ->willReturn(['FakeLicenseExpression' => true]);

        $actual = $normalizer->normalize($licenses);

        self::assertSame([['FakeLicenseExpression' => true]], $actual);
    }
}
