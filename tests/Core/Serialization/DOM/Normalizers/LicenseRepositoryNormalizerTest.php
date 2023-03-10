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

use CycloneDX\Core\Collections\LicenseRepository;
use CycloneDX\Core\Models\License\NamedLicense;
use CycloneDX\Core\Serialization\DOM\_BaseNormalizer;
use CycloneDX\Core\Serialization\DOM\NormalizerFactory;
use CycloneDX\Core\Serialization\DOM\Normalizers\LicenseNormalizer;
use CycloneDX\Core\Serialization\DOM\Normalizers\LicenseRepositoryNormalizer;
use CycloneDX\Core\Spec\Spec;
use CycloneDX\Tests\_traits\DomNodeAssertionTrait;
use DOMElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LicenseRepositoryNormalizer::class)]
#[CoversClass(_BaseNormalizer::class)]
class LicenseRepositoryNormalizerTest extends TestCase
{
    use DomNodeAssertionTrait;

    public function testNormalizeEmpty(): void
    {
        $spec = $this->createMock(Spec::class);
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
        $spec = $this->createMock(Spec::class);
        $licenseNormalizer = $this->createMock(LicenseNormalizer::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $spec,
            'makeForLicense' => $licenseNormalizer,
        ]);
        $normalizer = new LicenseRepositoryNormalizer($factory);
        $license = $this->createMock(NamedLicense::class);
        $licenses = $this->createConfiguredMock(LicenseRepository::class, [
            'count' => 1,
            'getItems' => [$license],
        ]);
        $FakeLicense = $this->createMock(DOMElement::class);

        $licenseNormalizer->expects(self::once())->method('normalize')
            ->with($license)
            ->willReturn($FakeLicense);

        $actual = $normalizer->normalize($licenses);

        self::assertSame([$FakeLicense], $actual);
    }
}
