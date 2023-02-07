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

namespace CycloneDX\Tests\Core\Models;

use CycloneDX\Core\Collections\HashDictionary;
use CycloneDX\Core\Enums\ExternalReferenceType;
use CycloneDX\Core\Models\ExternalReference;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\CycloneDX\Core\Models\ExternalReference::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(\CycloneDX\Core\Collections\HashDictionary::class)]
class ExternalReferenceTest extends TestCase
{
    public function testConstructor(): ExternalReference
    {
        $extRef = new ExternalReference(ExternalReferenceType::OTHER, 'https://localhost/dummy');

        $this->assertSame(ExternalReferenceType::OTHER, $extRef->getType());
        $this->assertSame('https://localhost/dummy', $extRef->getUrl());
        $this->assertNull($extRef->getComment());
        $this->assertCount(0, $extRef->getHashes());

        return $extRef;
    }

    // region test Type

     #[\PHPUnit\Framework\Attributes\DependsUsingShallowClone('testConstructor')]
    public function testTypeSetterAndGetter(ExternalReference $extRef): void
    {
        $this->assertNotSame(ExternalReferenceType::CHAT, $extRef->getType());
        $got = $extRef->setType(ExternalReferenceType::CHAT);
        $this->assertSame($extRef, $got);
        $this->assertSame(ExternalReferenceType::CHAT, $extRef->getType());
    }

    // endregion test Type

    // region test Url

     #[\PHPUnit\Framework\Attributes\DependsUsingShallowClone('testConstructor')]
    public function testUrlSetterAndGetter(ExternalReference $extRef): void
    {
        $this->assertNotSame('ftp://localhost/foobar', $extRef->getUrl());
        $got = $extRef->setUrl('ftp://localhost/foobar');
        $this->assertSame($extRef, $got);
        $this->assertSame('ftp://localhost/foobar', $extRef->getUrl());
    }

     #[\PHPUnit\Framework\Attributes\DependsUsingShallowClone('testConstructor')]
    public function testUrlSetterWithURN(ExternalReference $extRef): void
    {
        $this->assertNotSame('urn:uuid:bdd819e6-ee8f-42d7-a4d0-166ff44d51e8', $extRef->getUrl());
        $got = $extRef->setUrl('urn:uuid:bdd819e6-ee8f-42d7-a4d0-166ff44d51e8');
        $this->assertSame($extRef, $got);
        $this->assertSame('urn:uuid:bdd819e6-ee8f-42d7-a4d0-166ff44d51e8', $extRef->getUrl());
    }

    // endregion test Url

    // region test Comment

     #[\PHPUnit\Framework\Attributes\DependsUsingShallowClone('testConstructor')]
    public function testCommentSetterAndGetter(ExternalReference $extRef): void
    {
        $this->assertNotSame('foobar', $extRef->getComment());
        $got = $extRef->setComment('foobar');
        $this->assertSame($extRef, $got);
        $this->assertSame('foobar', $extRef->getComment());
    }

    // endregion test Comment

    // region test Comment

     #[\PHPUnit\Framework\Attributes\DependsUsingShallowClone('testConstructor')]
    public function testHashesSetterAndGetter(ExternalReference $extRef): void
    {
        $hashes = $this->createStub(HashDictionary::class);
        $this->assertNotSame($hashes, $extRef->getHashes());
        $got = $extRef->setHashes($hashes);
        $this->assertSame($extRef, $got);
        $this->assertSame($hashes, $extRef->getHashes());
    }

    // endregion test Comment
}
