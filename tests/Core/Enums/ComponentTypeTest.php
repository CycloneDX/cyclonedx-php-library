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

namespace CycloneDX\Tests\Core\Enums;

use CycloneDX\Core\Enums\ComponentType;
use CycloneDX\Tests\_data\BomSpecData;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ComponentType::class)]
class ComponentTypeTest extends TestCase
{
    #[DataProvider('dpSchemaValues')]
    public function testHasCaseForSchemaValue(string $value): void
    {
        self::assertNotNull(ComponentType::tryFrom($value), "missing $value");
    }

    public static function dpSchemaValues(): Generator
    {
        $allValues = array_unique(array_merge(
            BomSpecData::getClassificationEnumForVersion('1.0'),
            BomSpecData::getClassificationEnumForVersion('1.1'),
            BomSpecData::getClassificationEnumForVersion('1.2'),
            BomSpecData::getClassificationEnumForVersion('1.3'),
            BomSpecData::getClassificationEnumForVersion('1.4'),
            BomSpecData::getClassificationEnumForVersion('1.5'),
            BomSpecData::getClassificationEnumForVersion('1.6'),
        ));
        foreach ($allValues as $value) {
            yield $value => [$value];
        }
    }
}
