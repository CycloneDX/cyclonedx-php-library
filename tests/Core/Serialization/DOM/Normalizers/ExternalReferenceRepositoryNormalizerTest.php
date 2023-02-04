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
use CycloneDX\Core\Models\ExternalReference;
use CycloneDX\Core\Serialization\DOM\NormalizerFactory;
use CycloneDX\Core\Serialization\DOM\Normalizers;
use CycloneDX\Core\Spec\Spec;
use DomainException;
use DOMElement;
use Generator;
use UnexpectedValueException;

/**
 * @covers \CycloneDX\Core\Serialization\DOM\Normalizers\ExternalReferenceRepositoryNormalizer
 * @covers \CycloneDX\Core\Serialization\DOM\_BaseNormalizer
 *
 * @uses \CycloneDX\Core\Serialization\DOM\Normalizers\ExternalReferenceNormalizer
 */
class ExternalReferenceRepositoryNormalizerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @uses \CycloneDX\Core\Serialization\DOM\Normalizers\ToolNormalizer
     */
    public function testNormalizeEmpty(): void
    {
        $spec = $this->createStub(Spec::class);
        $externalReferenceNormalizer = $this->createMock(Normalizers\ExternalReferenceNormalizer::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $spec,
            'makeForExternalReference' => $externalReferenceNormalizer,
        ]);

        $normalizer = new Normalizers\ExternalReferenceRepositoryNormalizer($factory);
        $repo = $this->createConfiguredMock(ExternalReferenceRepository::class, ['count' => 0]);

        $actual = $normalizer->normalize($repo);

        self::assertSame([], $actual);
    }

    public function testNormalize(): void
    {
        $spec = $this->createStub(Spec::class);
        $externalReferenceNormalizer = $this->createMock(Normalizers\ExternalReferenceNormalizer::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $spec,
            'makeForExternalReference' => $externalReferenceNormalizer,
        ]);
        $normalizer = new Normalizers\ExternalReferenceRepositoryNormalizer($factory);
        $externalReference = $this->createStub(ExternalReference::class);
        $repo = $this->createConfiguredMock(ExternalReferenceRepository::class, [
            'count' => 1,
            'getItems' => [$externalReference],
        ]);
        $FakeExtRef = $this->createStub(DOMElement::class);

        $externalReferenceNormalizer->expects(self::once())
            ->method('normalize')
            ->with($externalReference)
            ->willReturn($FakeExtRef);

        $actual = $normalizer->normalize($repo);

        self::assertSame([$FakeExtRef], $actual);
    }

    /**
     * @dataProvider dpNormalizeSkipsOnThrow
     *
     * @psalm-param class-string<\Exception> $exceptionClass
     */
    public function testNormalizeSkipsOnThrow(string $exceptionClass): void
    {
        $spec = $this->createStub(Spec::class);
        $externalReferenceNormalizer = $this->createMock(Normalizers\ExternalReferenceNormalizer::class);
        $factory = $this->createConfiguredMock(NormalizerFactory::class, [
            'getSpec' => $spec,
            'makeForExternalReference' => $externalReferenceNormalizer,
        ]);
        $normalizer = new Normalizers\ExternalReferenceRepositoryNormalizer($factory);
        $extRef1 = $this->createStub(ExternalReference::class);
        $extRef2 = $this->createStub(ExternalReference::class);
        $tools = $this->createConfiguredMock(ExternalReferenceRepository::class, [
            'count' => 1,
            'getItems' => [$extRef1, $extRef2],
        ]);

        $externalReferenceNormalizer->expects(self::exactly(2))
            ->method('normalize')
            ->willThrowException(new $exceptionClass());

        $actual = $normalizer->normalize($tools);

        self::assertSame([], $actual);
    }

    public function dpNormalizeSkipsOnThrow(): Generator
    {
        yield 'DomainException' => [DomainException::class];
        yield 'UnexpectedValueException' => [UnexpectedValueException::class];
    }
}
