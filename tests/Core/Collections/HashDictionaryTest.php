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

namespace CycloneDX\Tests\Core\Collections;

use CycloneDX\Core\Collections\HashDictionary;
use CycloneDX\Core\Enums\HashAlgorithm;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CycloneDX\Core\Collections\HashDictionary
 *
 * @uses \CycloneDX\Core\Enums\HashAlgorithm::isValidValue()
 */
class HashDictionaryTest extends TestCase
{
    public function testNonEmptyConstructor(): void
    {
        $hashes = new HashDictionary([HashAlgorithm::MD5 => 'foobar']);

        self::assertCount(1, $hashes);
        self::assertArrayHasKey(HashAlgorithm::MD5, $hashes->getItems());
        self::assertSame('foobar', $hashes->getItems()[HashAlgorithm::MD5]);
        self::assertSame('foobar', $hashes->get(HashAlgorithm::MD5));
    }

    public function testAddHash(): void
    {
        $hashes = new HashDictionary([HashAlgorithm::SHA_1 => 'foo']);

        $hashes->set(HashAlgorithm::MD5, 'bar');

        self::assertCount(2, $hashes);
        self::assertArrayHasKey(HashAlgorithm::MD5, $hashes->getItems());
        self::assertSame('bar', $hashes->getItems()[HashAlgorithm::MD5]);
        self::assertSame('bar', $hashes->get(HashAlgorithm::MD5));
    }

    public function testUpdateHash(): void
    {
        $hashes = new HashDictionary([HashAlgorithm::MD5 => 'foo', HashAlgorithm::SHA_1 => 'foo']);

        $hashes->set(HashAlgorithm::MD5, 'bar');

        self::assertCount(2, $hashes);
        self::assertArrayHasKey(HashAlgorithm::MD5, $hashes->getItems());
        self::assertSame('bar', $hashes->getItems()[HashAlgorithm::MD5]);
        self::assertSame('bar', $hashes->get(HashAlgorithm::MD5));
    }

    public function testUnsetHash(): void
    {
        $hashes = new HashDictionary([HashAlgorithm::MD5 => 'foo', HashAlgorithm::SHA_1 => 'foo']);
        $hashes->set(HashAlgorithm::MD5, null);

        self::assertNull($hashes->get(HashAlgorithm::MD5));
        self::assertCount(1, $hashes);
        self::assertSame([HashAlgorithm::SHA_1 => 'foo'], $hashes->getItems());
    }

    public function testGetUnknownHash(): void
    {
        $hashes = new HashDictionary();
        self::assertNull($hashes->get(HashAlgorithm::MD5));
    }

    public function testSetUnknownHashAlgorithmThrows(): void
    {
        $hashes = new HashDictionary();

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageMatches('/unknown hash algorithm/i');

        $hashes->set('unknownAlgorithm', 'foobar');
    }

    public function testSetGetHashes(): void
    {
        $hashes = new HashDictionary([HashAlgorithm::SHA_256 => 'barbar']);

        $hashes->setItems([HashAlgorithm::MD5 => 'foobar', 'unknownAlgorithm' => 'foobar']);
        $got = $hashes->getItems();

        self::assertCount(2, $got);
        self::assertSame('barbar', $got[HashAlgorithm::SHA_256]);
        self::assertSame('foobar', $got[HashAlgorithm::MD5]);
    }
}
