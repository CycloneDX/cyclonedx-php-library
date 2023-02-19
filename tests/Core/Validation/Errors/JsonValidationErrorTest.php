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

namespace CycloneDX\Tests\Core\Validation\Errors;

use CycloneDX\Core\Validation\Errors\JsonValidationError;
use CycloneDX\Core\Validation\ValidationError;
use Opis\JsonSchema;
use Opis\JsonSchema\Info\DataInfo;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Schemas\EmptySchema;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(JsonValidationError::class)]
#[CoversClass(ValidationError::class)]
#[UsesClass(JsonSchema\Errors\ValidationError::class)]
#[UsesClass(EmptySchema::class)]
#[UsesClass(SchemaInfo::class)]
#[UsesClass(DataInfo::class)]
class JsonValidationErrorTest extends TestCase
{
    public function testFromJsonSchemaInvalidValue(): void
    {
        $errorJsonSchemaInvalidValue = new JsonSchema\Errors\ValidationError(
            'foo',
            new EmptySchema(new SchemaInfo(false, null)),
            new DataInfo(null, null, null),
            'some error message'
        );

        $error = JsonValidationError::fromSchemaValidationError($errorJsonSchemaInvalidValue);

        $expected = <<<'error'
            {
                "/": [
                    "some error message"
                ]
            }
            error;

        self::assertSame($expected, $error->getMessage());
    }
}
