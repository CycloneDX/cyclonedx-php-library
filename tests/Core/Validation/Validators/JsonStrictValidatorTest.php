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

namespace CycloneDX\Tests\Core\Validation\Validators;

use CycloneDX\Core\Spec\Spec;
use CycloneDX\Core\Spec\Version;
use CycloneDX\Core\Validation\Errors\JsonValidationError;
use CycloneDX\Core\Validation\Exceptions\FailedLoadingSchemaException;
use CycloneDX\Core\Validation\Validators\JsonStrictValidator;
use JsonException;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\TestCase;
use stdClass;

#[\PHPUnit\Framework\Attributes\CoversClass(\CycloneDX\Core\Validation\Validators\JsonStrictValidator::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\CycloneDX\Core\Validation\Validators\JsonValidator::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\CycloneDX\Core\Validation\BaseValidator::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(\CycloneDX\Core\Validation\Errors\JsonValidationError::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(\CycloneDX\Core\Validation\ValidationError::class)]
class JsonStrictValidatorTest extends TestCase
{
    public function testConstructor(): JsonStrictValidator
    {
        $spec = $this->createStub(Spec::class);
        $validator = new JsonStrictValidator($spec);
        self::assertSame($spec, $validator->getSpec());

        return $validator;
    }

    public function testValidateString(): void
    {
        $validator = $this->createPartialMock(JsonStrictValidator::class, ['validateData']);
        $json = '{"dummy": "true"}';

        $validator->expects(self::once())->method('validateData')
            ->with(new IsInstanceOf(stdClass::class))
            ->willReturn(null);

        $error = $validator->validateString($json);

        self::assertNull($error);
    }

    public function testValidateStringError(): void
    {
        $validator = $this->createPartialMock(JsonStrictValidator::class, ['validateData']);
        $json = '{"dummy": "true"}';
        $expectedError = $this->createStub(JsonValidationError::class);

        $validator->expects(self::once())->method('validateData')
            ->with(new IsInstanceOf(stdClass::class))
            ->willReturn($expectedError);

        $error = $validator->validateString($json);

        self::assertSame($expectedError, $error);
    }

    public function testValidateStringThrowsWhenNotParseable(): void
    {
        $spec = $this->createConfiguredMock(Spec::class, ['getVersion' => Version::v1dot2]);
        $validator = new JsonStrictValidator($spec);
        $json = '{"dummy":';

        $this->expectException(JsonException::class);
        $this->expectExceptionMessageMatches('/loading failed/i');

        $validator->validateString($json);
    }

    public function testValidateDataPasses(): void
    {
        $spec = $this->createConfiguredMock(Spec::class, ['getVersion' => Version::v1dot2]);
        $validator = new JsonStrictValidator($spec);
        $data = (object) [
            'bomFormat' => 'CycloneDX',
            'specVersion' => '1.2',
            'version' => 1,
            'components' => [
                (object) [
                    'type' => 'library',
                    'group' => 'org.acme',
                    'name' => 'web-framework',
                    'version' => '1.0.0',
                    'purl' => 'pkg:maven/org.acme/web-framework@1.0.0',
                    'licenses' => [
                        (object) ['license' => (object) ['id' => 'MIT']],
                    ],
                ],
            ],
        ];

        $error = $validator->validateData($data);

        self::assertNull($error);
    }

    public function testValidateDataFails(): void
    {
        $spec = $this->createConfiguredMock(Spec::class, ['getVersion' => Version::v1dot2]);
        $validator = new JsonStrictValidator($spec);
        $data = (object) [
            'bomFormat' => 'CycloneDX',
            'specVersion' => '1.2',
            'version' => 1,
            'components' => [
                (object) [
                    'type' => 'library',
                    'group' => 'org.acme',
                    'name' => 'web-framework',
                    'version' => '1.0.0',
                    'purl' => 'pkg:maven/org.acme/web-framework@1.0.0',
                    'licenses' => [
                        (object) [
                            'license' => (object) [
                                'id' => 'MIT',
                                'foo' => 'bare',
                                // Error: no additional values allowed here
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $error = $validator->validateData($data);

        self::assertNotNull($error);
    }

    public function testValidateDataThrowsOnSchemaFileUnknown(): void
    {
        $spec = $this->createConfiguredMock(Spec::class, ['getVersion' => Version::v1dot1]);
        $validator = new JsonStrictValidator($spec);

        $this->expectException(FailedLoadingSchemaException::class);

        $validator->validateData(new stdClass());
    }
}
