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

use CycloneDX\Core\Spdx\SpdxLicenses;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(SpdxLicenses::class)]
class SpdxLicensesTest extends TestCase
{
    private const LICENSES_FILE_CONTENT = <<<'JSON'
        {
           "enum": [
                "foo",
                "BAR",
                "FooBaR"
           ]
        }
        JSON;

    /**
     * return valid licenses based on {@see LICENSES_FILE_CONTENT}
     */
    public static function dpLicenses(): Generator
    {
        yield 'UPPERCASE' => ['FOOBAR'];
        yield 'lowercase' => ['foobar'];
        yield 'PascalCase' => ['FooBar'];
    }

    public static function dpKnownLicenses() : Generator
    {
        foreach (["foo",                     "BAR", "FooBaR"] as $license) {
            yield $license => [$license];
        }
    }

    private SpdxLicenses & \PHPUnit\Framework\MockObject\MockObject $licenses;

    private string $fakeResourcesFile;


    protected function setUp(): void
    {
        $this->fakeResourcesFile = tempnam(sys_get_temp_dir(), __CLASS__);
        file_put_contents($this->fakeResourcesFile, self::LICENSES_FILE_CONTENT);

        $this->licenses = $this->createPartialMock(SpdxLicenses::class, ['getResourcesFile']);
        $this->licenses->method('getResourcesFile')->willReturn($this->fakeResourcesFile);
    }

    protected function tearDown(): void
    {
        @unlink($this->fakeResourcesFile);
        unset(
            $this->fakeResourcesFile,
            $this->licenses
        );
    }

    public function testGetLicensesAsExpected(): void
    {
        ['enum' => $expected] = json_decode(self::LICENSES_FILE_CONTENT, true, 3, \JSON_THROW_ON_ERROR);
        $licenses = $this->licenses->getKnownLicenses();
        self::assertIsArray($expected);
        self::assertSame($expected, $licenses);
    }

    #[DataProvider('dpKnownLicenses')]
    public function testValidate(string $identifier): void
    {
        $valid = $this->licenses->validate($identifier);
        self::assertTrue($valid);
    }

    public function testValidateWithUnknown(): void
    {
        $identifier = uniqid('unknown', true);
        $valid = $this->licenses->validate($identifier);
        self::assertFalse($valid);
    }

    #[DataProvider('dpLicenses')]
    public function testGetLicense(string $identifier): void
    {
        $license = $this->licenses->getLicense($identifier);
        self::assertNotNull($license);
    }

    public function testGetLicenseWithUnknown(): void
    {
        $identifier = uniqid('unknown', false);
        $license = $this->licenses->getLicense($identifier);
        self::assertNull($license);
    }



    public function testShippedLicensesFile(): void
    {
        $file = (new SpdxLicenses())->getResourcesFile();
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

    public function testWithMalformedLicenseFile(): void
    {
        file_put_contents($this->fakeResourcesFile, '["foo');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/malformed licenses file/i');

        $this->licenses->getKnownLicenses();
    }

    public function testWithMissingLicenseFile(): void
    {
        unlink($this->fakeResourcesFile);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/missing licenses file/i');

        $this->licenses->getKnownLicenses();
    }

    public function testWithUnreadableLicenseFile(): void
    {
        // set mode to not-readable to force read errors ...
        if (!chmod($this->fakeResourcesFile, 0222)) {
            $this->markTestSkipped('preparation could not be done');
        }

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/failed to get content from licenses file/i');

        $this->licenses->getKnownLicenses();
    }
}
