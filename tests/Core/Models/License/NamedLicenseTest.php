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

use CycloneDX\Core\Models\License\NamedLicense;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\CycloneDX\Core\Models\License\NamedLicense::class)]
class NamedLicenseTest extends TestCase
{
    public function testConstruct(): NamedLicense
    {
        $license = new NamedLicense('foo');
        self::assertSame('foo', $license->getName());
        self::assertNull($license->getUrl());

        return $license;
    }

     #[\PHPUnit\Framework\Attributes\DependsUsingShallowClone('testConstruct')]
    public function testSetName(NamedLicense $license): void
    {
        $license->setName('bar');
        self::assertSame('bar', $license->getName());
    }

     #[\PHPUnit\Framework\Attributes\DependsUsingShallowClone('testConstruct')]

    public function testSetAndGetUrl(NamedLicense $license): NamedLicense
    {
        $url = uniqid('url', true);
        $license->setUrl($url);
        self::assertSame($url, $license->getUrl());

        return $license;
    }


     #[\PHPUnit\Framework\Attributes\DependsUsingShallowClone('testSetAndGetUrl')]
    public function testSetUrlNull(NamedLicense $license): void
    {
        $license->setUrl(null);
        self::assertNull($license->getUrl());
    }
}
