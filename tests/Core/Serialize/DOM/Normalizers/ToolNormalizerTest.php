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

namespace CycloneDX\Tests\Core\Serialize\DOM\Normalizers;

use CycloneDX\Core\Models\Tool;
use CycloneDX\Core\Collections\ExternalReferenceRepository;
use CycloneDX\Core\Collections\HashDictionary;
use CycloneDX\Core\Serialize\DOM\NormalizerFactory;
use CycloneDX\Core\Serialize\DOM\Normalizers\ExternalReferenceRepositoryNormalizer;
use CycloneDX\Core\Serialize\DOM\Normalizers\HashRepositoryNormalizer;
use CycloneDX\Core\Serialize\DOM\Normalizers\ToolNormalizer;
use CycloneDX\Core\Spec\SpecInterface;
use CycloneDX\Tests\_traits\DomNodeAssertionTrait;
use DOMDocument;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Serialize\DOM\Normalizers\ToolNormalizer
 *
 * @uses   \CycloneDX\Core\Serialize\DOM\AbstractNormalizer
 */
class ToolNormalizerTest extends TestCase
{
    use DomNodeAssertionTrait;

    public function testNormalizeEmpty(): void
    {
        $tool = $this->createMock(Tool::class);
        $spec = $this->createMock(SpecInterface::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'getDocument' => new DOMDocument(),
            ]
        );
        $normalizer = new ToolNormalizer($factory);

        $actual = $normalizer->normalize($tool);

        self::assertStringEqualsDomNode(
            '<tool></tool>',
            $actual
        );
    }

    /**
     * @uses \CycloneDX\Core\Serialize\DOM\Normalizers\HashRepositoryNormalizer
     */
    public function testNormalizeFull(): void
    {
        $tool = $this->createConfiguredMock(
            Tool::class,
            [
                'getVendor' => 'myVendor',
                'getName' => 'myName',
                'getVersion' => 'myVersion',
                'getHashRepository' => $this->createConfiguredMock(HashDictionary::class, ['count' => 2]),
                'getExternalReferenceRepository' => $this->createConfiguredMock(ExternalReferenceRepository::class, ['count' => 2]),
            ]
        );
        $spec = $this->createConfiguredMock(SpecInterface::class, [
            'supportsToolExternalReferences' => true,
        ]);
        $hashRepoNormalizer = $this->createMock(HashRepositoryNormalizer::class);
        $extRefRepoNormalizer = $this->createMock(ExternalReferenceRepositoryNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'getDocument' => new DOMDocument(),
                'makeForHashRepository' => $hashRepoNormalizer,
                'makeForExternalReferenceRepository' => $extRefRepoNormalizer,
            ]
        );
        $normalizer = new ToolNormalizer($factory);

        $hashRepoNormalizer->expects(self::once())
            ->method('normalize')
            ->with($tool->getHashes())
            ->willReturn([$factory->getDocument()->createElement('FakeHash', 'dummyHash')]);
        $extRefRepoNormalizer->expects(self::once())
            ->method('normalize')
            ->with($tool->getExternalReferences())
            ->willReturn([$factory->getDocument()->createElement('FakeExtRefs', 'dummyRef')]);

        $actual = $normalizer->normalize($tool);

        self::assertStringEqualsDomNode(
            '<tool>'.
            '<vendor>myVendor</vendor>'.
            '<name>myName</name>'.
            '<version>myVersion</version>'.
            '<hashes><FakeHash>dummyHash</FakeHash></hashes>'.
            '<externalReferences><FakeExtRefs>dummyRef</FakeExtRefs></externalReferences>'.
            '</tool>',
            $actual
        );
    }

    public function testNormalizeMinimal(): void
    {
        $tool = $this->createConfiguredMock(
            Tool::class,
            [
                'getVendor' => null,
                'getName' => null,
                'getVersion' => null,
                'getHashRepository' => null,
                'getExternalReferenceRepository' => null,
            ]
        );
        $spec = $this->createConfiguredMock(SpecInterface::class, [
            'supportsToolExternalReferences' => true,
        ]);
        $hashRepoNormalizer = $this->createMock(HashRepositoryNormalizer::class);
        $extRefRepoNormalizer = $this->createMock(ExternalReferenceRepositoryNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'getDocument' => new DOMDocument(),
                'makeForHashRepository' => $hashRepoNormalizer,
                'makeForExternalReferenceRepository' => $extRefRepoNormalizer,
            ]
        );
        $normalizer = new ToolNormalizer($factory);

        $hashRepoNormalizer->expects(self::never())
            ->method('normalize')
            ->willThrowException(new \BadMethodCallException());
        $extRefRepoNormalizer->expects(self::never())
            ->method('normalize')
            ->willThrowException(new \BadMethodCallException());

        $actual = $normalizer->normalize($tool);

        self::assertStringEqualsDomNode(
            '<tool></tool>',
            $actual
        );
    }
}
