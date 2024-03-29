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

namespace CycloneDX\Tests\Core;

use CycloneDX\Core\Resources;
use Generator;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversNothing]
class ResourcesTest extends TestCase
{
    #[DataProvider('dpDirs')]
    public function testDirIsReadable(string $dir): void
    {
        self::assertDirectoryExists($dir);
        self::assertDirectoryIsReadable($dir);
    }

    public static function dpDirs(): Generator
    {
        yield 'res root' => [Resources::DIR_ROOT];
        yield 'schema root' => [Resources::DIR_SCHEMA];
    }

    #[DataProvider('dpFiles')]
    public function testFileIsReadable(string $filePath): void
    {
        self::assertFileExists($filePath);
        self::assertFileIsReadable($filePath);
    }

    public static function dpFiles(): Generator
    {
        $constants = (new ReflectionClass(Resources::class))->getConstants();
        foreach ($constants as $name => $value) {
            if (str_starts_with($name, 'FILE')) {
                yield $name => [$value];
            }
        }
    }
}
