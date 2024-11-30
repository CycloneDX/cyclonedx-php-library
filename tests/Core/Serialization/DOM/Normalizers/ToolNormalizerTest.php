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

use CycloneDX\Core\Collections\ExternalReferenceRepository;
use CycloneDX\Core\Collections\HashDictionary;
use CycloneDX\Core\Models\Tool;
use CycloneDX\Core\Serialization\DOM\_BaseNormalizer;
use CycloneDX\Core\Serialization\DOM\NormalizerFactory;
use CycloneDX\Core\Serialization\DOM\Normalizers\ExternalReferenceRepositoryNormalizer;
use CycloneDX\Core\Serialization\DOM\Normalizers\HashDictionaryNormalizer;
use CycloneDX\Core\Serialization\DOM\Normalizers\ToolNormalizer;
use CycloneDX\Core\Spec\_SpecProtocol;
use CycloneDX\Tests\_traits\DomNodeAssertionTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ToolNormalizer::class)]
#[UsesClass(_BaseNormalizer::class)]
#[UsesClass(HashDictionaryNormalizer::class)]
class ToolNormalizerTest extends TestCase
{
    use DomNodeAssertionTrait;

    public function testNormalizeEmpty(): void
    {
        $tool = $this->createMock(Tool::class);
        $spec = $this->createMock(_SpecProtocol::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'getDocument' => new \DOMDocument(),
            ]
        );
        $normalizer = new ToolNormalizer($factory);

        $actual = $normalizer->normalize($tool);

        self::assertStringEqualsDomNode(
            '<tool></tool>',
            $actual
        );
    }

    public function testNormalizeFull(): void
    {
        $tool = $this->createConfiguredMock(
            Tool::class,
            [
                'getVendor' => 'myVendor',
                'getName' => 'myName',
                'getVersion' => 'myVersion',
                'getHashes' => $this->createConfiguredMock(HashDictionary::class, ['count' => 2]),
                'getExternalReferences' => $this->createConfiguredMock(ExternalReferenceRepository::class, ['count' => 2]),
            ]
        );
        $spec = $this->createConfiguredMock(_SpecProtocol::class, [
            'supportsToolExternalReferences' => true,
        ]);
        $HashDictNormalizer = $this->createMock(HashDictionaryNormalizer::class);
        $extRefRepoNormalizer = $this->createMock(ExternalReferenceRepositoryNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'getDocument' => new \DOMDocument(),
                'makeForHashDictionary' => $HashDictNormalizer,
                'makeForExternalReferenceRepository' => $extRefRepoNormalizer,
            ]
        );
        $normalizer = new ToolNormalizer($factory);

        $HashDictNormalizer->expects(self::once())
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
                'getHashes' => $this->createStub(HashDictionary::class),
                'getExternalReferences' => $this->createStub(ExternalReferenceRepository::class),
            ]
        );
        $spec = $this->createConfiguredMock(_SpecProtocol::class, [
            'supportsToolExternalReferences' => true,
        ]);
        $HashDictNormalizer = $this->createMock(HashDictionaryNormalizer::class);
        $extRefRepoNormalizer = $this->createMock(ExternalReferenceRepositoryNormalizer::class);
        $factory = $this->createConfiguredMock(
            NormalizerFactory::class,
            [
                'getSpec' => $spec,
                'getDocument' => new \DOMDocument(),
                'makeForHashDictionary' => $HashDictNormalizer,
                'makeForExternalReferenceRepository' => $extRefRepoNormalizer,
            ]
        );
        $normalizer = new ToolNormalizer($factory);

        $HashDictNormalizer->expects(self::never())
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
