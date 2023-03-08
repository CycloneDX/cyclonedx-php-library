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

namespace CycloneDX\Tests\Core\Spdx;

use CycloneDX\Core\Spdx\LicenseIdentifiers;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(LicenseIdentifiers::class)]
class LicenseIdentifierTest extends TestCase
{
    public static function dpLicenses(): Generator
    {
        yield 'UPPERCASE (mod)' => ['APACHE-2.0'];
        yield 'lowercase (mod)' => ['mit'];
        yield 'PascalCase (original)' => ['Bitstream-Vera'];
    }

    public static function dpKnownLicenses(): Generator
    {
        foreach (['Apache-2.0', 'MIT', 'Bitstream-Vera'] as $license) {
            yield $license => [$license];
        }
    }

    private LicenseIdentifiers $licenses;

    protected function setUp(): void
    {
        $this->licenses = new LicenseIdentifiers();
    }

    protected function tearDown(): void
    {
        unset($this->licenses);
    }

    #[DataProvider('dpKnownLicenses')]
    public function testIsKnownLicense(string $identifier): void
    {
        $valid = $this->licenses->isKnownLicense($identifier);
        self::assertTrue($valid);
    }

    public function testIsKnownLicenseWithUnknown(): void
    {
        $identifier = uniqid('unknown', true);
        $valid = $this->licenses->isKnownLicense($identifier);
        self::assertFalse($valid);
    }

    #[DataProvider('dpLicenses')]
    public function testFixLicense(string $identifier): void
    {
        $license = $this->licenses->fixLicense($identifier);
        self::assertNotNull($license);
    }

    public function testFixLicenseWithUnknown(): void
    {
        $identifier = uniqid('unknown', false);
        $license = $this->licenses->fixLicense($identifier);
        self::assertNull($license);
    }

    public function testShippedLicensesFile(): void
    {
        $file = (new LicenseIdentifiers())->getResourcesFile();
        self::assertFileExists($file);

        $json = file_get_contents($file);
        self::assertIsString($json);
        self::assertJson($json);

        ['enum' => $licenses] = json_decode($json, true, 3, \JSON_THROW_ON_ERROR);
        self::assertIsArray($licenses);
        self::assertNotEmpty($licenses);

        foreach ($licenses as $license) {
            self::assertIsString($license);
        }
    }

    public function testGetKnownLicensesAsExpected(): void
    {
        ['enum' => $expected] = json_decode(
            file_get_contents($this->licenses->getResourcesFile()),
            true, 3, \JSON_THROW_ON_ERROR);
        self::assertIsArray($expected);

        $licenses = $this->licenses->getKnownLicenses();
        self::assertSame($expected, $licenses);
    }

    public function testWithMalformedLicenseFile(): void
    {
        $fakeResourcesFile = tempnam(sys_get_temp_dir(), __CLASS__);
        file_put_contents($fakeResourcesFile, '["foo');
        try {
            $licenses = $this->createPartialMock(LicenseIdentifiers::class, ['getResourcesFile']);
            $licenses->method('getResourcesFile')->willReturn($fakeResourcesFile);

            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessageMatches('/malformed licenses file/i');

            $licenses->__construct();
        } finally {
            @unlink($fakeResourcesFile);
        }
    }

    public function testWithMissingLicenseFile(): void
    {
        $fakeResourcesFile = tempnam(sys_get_temp_dir(), __CLASS__);
        @unlink($fakeResourcesFile);

        $licenses = $this->createPartialMock(LicenseIdentifiers::class, ['getResourcesFile']);
        $licenses->method('getResourcesFile')->willReturn($fakeResourcesFile);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/missing licenses file/i');

        $licenses->__construct();
    }

    public function testWithUnreadableLicenseFile(): void
    {
        $fakeResourcesFile = tempnam(sys_get_temp_dir(), __CLASS__);
        // set mode to not-readable to force read errors ...
        if (!chmod($fakeResourcesFile, 0o222)) {
            $this->markTestSkipped('preparation could not be done');
        }

        try {
            $licenses = $this->createPartialMock(LicenseIdentifiers::class, ['getResourcesFile']);
            $licenses->method('getResourcesFile')->willReturn($fakeResourcesFile);

            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessageMatches('/failed to get content from licenses file/i');

            // suppress the warning/notice - we want the exception
            @$licenses->__construct();
        } finally {
            @unlink($fakeResourcesFile);
        }
    }
}
