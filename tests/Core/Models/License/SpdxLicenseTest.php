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
use CycloneDX\Core\Models\License\SpdxLicense;
use DomainException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DependsUsingShallowClone;
use PHPUnit\Framework\TestCase;

#[CoversClass(SpdxLicense::class)]
class SpdxLicenseTest extends TestCase
{
    public function testConstruct(): SpdxLicense
    {
        $id = uniqid('id', true);
        $license = new SpdxLicense($id);

        self::assertSame($id, $license->getId());
        self::assertNull($license->getUrl());
        self::assertNull($license->getAcknowledgement());

        return $license;
    }

    public function testConstructWithEmptyStringThrows(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('ID must not be empty');

        new SpdxLicense('');
    }

    #[DependsUsingShallowClone('testConstruct')]
    public function testSetIdWithEmptyStringThrows(SpdxLicense $license): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('ID must not be empty');

        $license->setId('');
    }

    #[DependsUsingShallowClone('testConstruct')]
    public function testSetAndGetUrl(SpdxLicense $license): SpdxLicense
    {
        $url = uniqid('url', true);
        $license->setUrl($url);
        self::assertSame($url, $license->getUrl());

        return $license;
    }

    #[DependsUsingShallowClone('testSetAndGetUrl')]
    public function testSetUrlNull(SpdxLicense $license): void
    {
        $license->setUrl(null);
        self::assertNull($license->getUrl());
    }

    #[DependsUsingShallowClone('testSetAndGetUrl')]
    public function testSetUrlEmptyString(SpdxLicense $license): void
    {
        $license->setUrl('');
        self::assertNull($license->getUrl());
    }

    #[DependsUsingShallowClone('testConstruct')]
    public function testSetAndGetAcknowledgement(SpdxLicense $license): void
    {
        $acknowledgement = LicenseAcknowledgement::Declared;

        $got = $license->setAcknowledgement($acknowledgement);

        self::assertSame($license, $got);
        self::assertSame($acknowledgement, $license->getAcknowledgement());
    }
}
