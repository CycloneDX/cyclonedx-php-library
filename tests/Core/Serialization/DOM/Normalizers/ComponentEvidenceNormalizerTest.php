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

use CycloneDX\Core\_helpers\SimpleDOM;
use CycloneDX\Core\Collections\CopyrightRepository;
use CycloneDX\Core\Collections\LicenseRepository;
use CycloneDX\Core\Models\ComponentEvidence;
use CycloneDX\Core\Models\License\NamedLicense;
use CycloneDX\Core\Serialization\DOM\_BaseNormalizer;
use CycloneDX\Core\Serialization\DOM\NormalizerFactory;
use CycloneDX\Core\Serialization\DOM\Normalizers;
use CycloneDX\Core\Spec\_SpecProtocol;
use CycloneDX\Tests\_traits\DomNodeAssertionTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Normalizers\ComponentEvidenceNormalizer::class)]
#[CoversClass(_BaseNormalizer::class)]
#[UsesClass(SimpleDOM::class)]
class ComponentEvidenceNormalizerTest extends TestCase
{
    use DomNodeAssertionTrait;

    public function testNormalizeMinimal(): void
    {
        $evidence = $this->createConfiguredMock(
            ComponentEvidence::class,
            [
                'getLicenses' => $this->createMock(LicenseRepository::class),
                'getCopyright' => $this->createMock(CopyrightRepository::class),
            ]
        );
        $spec = $this->createMock(_SpecProtocol::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            ['getSpec' => $spec, 'getDocument' => new \DOMDocument()]
        );
        $normalizer = new Normalizers\ComponentEvidenceNormalizer($factory);

        $actual = $normalizer->normalize($evidence);

        self::assertStringEqualsDomNode('<evidence></evidence>', $actual);
    }

    public function testNormalizeFull(): void
    {
        $evidence = $this->createConfiguredMock(
            ComponentEvidence::class,
            [
                'getLicenses' => $this->createConfiguredMock(LicenseRepository::class, ['count' => 1, 'getItems' => [$this->createMock(NamedLicense::class)]]),
                'getCopyright' => $this->createConfiguredMock(CopyrightRepository::class, ['count' => 1, 'getItems' => ['some copyright']]),
            ]
        );
        $spec = $this->createMock(_SpecProtocol::class);
        $licenseRepoNormalizer = $this->createMock(Normalizers\LicenseRepositoryNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'getDocument' => new \DOMDocument(),
                'makeForLicenseRepository' => $licenseRepoNormalizer,
            ]
        );
        $normalizer = new Normalizers\ComponentEvidenceNormalizer($factory);

        $licenseRepoNormalizer->expects(self::once())
            ->method('normalize')
            ->with($evidence->getLicenses())
            ->willReturn([$factory->getDocument()->createElement('FakeLicense', 'dummy')]);

        $actual = $normalizer->normalize($evidence);

        self::assertStringEqualsDomNode(
            '<evidence>'.
            '<licenses><FakeLicense>dummy</FakeLicense></licenses>'.
            '<copyright><text>some copyright</text></copyright>'.
            '</evidence>',
            $actual
        );
    }
}
