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

namespace CycloneDX\Tests\Core\Models\License;

use CycloneDX\Core\Enums\LicenseAcknowledgement;
use CycloneDX\Core\Models\License\NamedLicense;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DependsUsingShallowClone;
use PHPUnit\Framework\TestCase;

#[CoversClass(NamedLicense::class)]
class NamedLicenseTest extends TestCase
{
    public function testConstruct(): NamedLicense
    {
        $id = uniqid('name', true);
        $license = new NamedLicense($id);

        self::assertSame($id, $license->getName());
        self::assertNull($license->getUrl());

        return $license;
    }

    public function testConstructWithEmpty(): NamedLicense
    {
        $license = new NamedLicense('');
        self::assertSame('', $license->getName());
        self::assertNull($license->getUrl());
        self::assertNull($license->getAcknowledgement());

        return $license;
    }

    #[DependsUsingShallowClone('testConstruct')]
    public function testSetName(NamedLicense $license): void
    {
        $license->setName('bar');
        self::assertSame('bar', $license->getName());
    }

    #[DependsUsingShallowClone('testConstruct')]
    public function testSetNameWithEmpty(NamedLicense $license): void
    {
        $license->setName('');
        self::assertSame('', $license->getName());
    }

    #[DependsUsingShallowClone('testConstruct')]
    public function testSetAndGetUrl(NamedLicense $license): NamedLicense
    {
        $url = uniqid('url', true);
        $license->setUrl($url);
        self::assertSame($url, $license->getUrl());

        return $license;
    }

    #[DependsUsingShallowClone('testSetAndGetUrl')]
    public function testSetUrlNull(NamedLicense $license): void
    {
        $license->setUrl(null);
        self::assertNull($license->getUrl());
    }

    #[DependsUsingShallowClone('testSetAndGetUrl')]
    public function testSetUrlEmptyString(NamedLicense $license): void
    {
        $license->setUrl('');
        self::assertNull($license->getUrl());
    }

    #[DependsUsingShallowClone('testConstruct')]
    public function testSetAndGetAcknowledgement(NamedLicense $license): void
    {
        $acknowledgement = LicenseAcknowledgement::Declared;

        $got = $license->setAcknowledgement($acknowledgement);

        self::assertSame($license, $got);
        self::assertSame($acknowledgement, $license->getAcknowledgement());
    }
}
