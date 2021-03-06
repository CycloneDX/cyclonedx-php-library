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
 * Copyright (c) Steve Springett. All Rights Reserved.
 */

namespace CycloneDX\Tests\_data;

use Generator;

abstract class GeneralDataProvider
{
    /**
     * @return Generator<string, array{string|null}>
     */
    public static function stringRandomEmptyNull(): Generator
    {
        yield 'null' => [null];
        yield 'empty string' => [''];
        yield 'random string' => [bin2hex(random_bytes(random_int(1, 255)))];
    }
}
