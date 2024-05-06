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

use CycloneDX\Core\Spec\_Spec;
use CycloneDX\Core\Spec\_SpecProtocol;
use CycloneDX\Core\Spec\SpecFactory;
use CycloneDX\Core\Spec\Version;
use CycloneDX\Core\Validation\BaseValidator;
use CycloneDX\Core\Validation\Errors\JsonValidationError;
use CycloneDX\Core\Validation\Exceptions\FailedLoadingSchemaException;
use CycloneDX\Core\Validation\ValidationError;
use CycloneDX\Core\Validation\Validators\JsonValidator;
use Generator;
use JsonException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(JsonValidator::class)]
#[CoversClass(BaseValidator::class)]
#[UsesClass(JsonValidationError::class)]
#[UsesClass(ValidationError::class)]
#[UsesClass(_Spec::class)]
class JsonValidatorTest extends TestCase
{
    public function testConstructor(): JsonValidator
    {
        $spec = $this->createStub(_SpecProtocol::class);
        $validator = new JsonValidator($spec);
        self::assertSame($spec, $validator->getSpec());

        return $validator;
    }

    public function testValidateString(): void
    {
        $validator = $this->createPartialMock(JsonValidator::class, ['validateData']);
        $json = '{"dummy": "true"}';

        $validator->expects(self::once())
            ->method('validateData')
            ->with(new IsInstanceOf(stdClass::class))
            ->willReturn(null);

        $error = $validator->validateString($json);

        self::assertNull($error);
    }

    public function testValidateStringError(): void
    {
        $validator = $this->createPartialMock(JsonValidator::class, ['validateData']);
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
        $spec = $this->createConfiguredMock(_SpecProtocol::class, ['getVersion' => Version::v1dot2]);
        $validator = new JsonValidator($spec);
        $json = '{"dummy":';

        $this->expectException(JsonException::class);
        $this->expectExceptionMessageMatches('/loading failed/i');

        $validator->validateString($json);
    }

    public function testValidateDataPasses(): void
    {
        $spec = $this->createConfiguredMock(_SpecProtocol::class, ['getVersion' => Version::v1dot2]);
        $validator = new JsonValidator($spec);
        $data = (object) [
            '$schema' => 'http://cyclonedx.org/schema/bom-1.2.schema.json',
            'bomFormat' => 'CycloneDX',
            'specVersion' => '1.3',
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
                                'foo' => 'bar',
                                // additional properties are allowed in non-strict mode
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $error = $validator->validateData($data);

        self::assertNull($error);
    }

    public function testValidateDataFails(): void
    {
        $spec = $this->createConfiguredMock(_SpecProtocol::class, ['getVersion' => Version::v1dot2]);
        $validator = new JsonValidator($spec);
        $data = (object) [
            '$schema' => 'http://cyclonedx.org/schema/bom-1.2.schema.json',
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
                                'name' => 'Some License',
                                //  Errors: eiter ID or name ...
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
        $spec = $this->createConfiguredMock(_SpecProtocol::class, ['getVersion' => Version::v1dot1]);
        $validator = new JsonValidator($spec);

        $this->expectException(FailedLoadingSchemaException::class);

        $validator->validateData(new stdClass());
    }

    #[DataProvider('dpSchemaTestDataValid')]
    public function testFunctionalValid(_Spec $spec, string $file): void
    {
        $validator = new JsonValidator($spec);
        $errors = $validator->validateString(file_get_contents($file));
        $this->assertNull($errors);
    }

    #[DataProvider('dpSchemaTestDataInvalid')]
    public function testFunctionalInvalid(_Spec $spec, string $file): void
    {
        $validator = new JsonValidator($spec);
        $errors = $validator->validateString(file_get_contents($file));
        $this->assertInstanceOf(JsonValidationError::class, $errors);
    }

    public static function dpSchemaTestDataValid(): Generator
    {
        yield from self::dpSchemaTestData('valid');
    }

    public static function dpSchemaTestDataInvalid(): Generator
    {
        yield from self::dpSchemaTestData('invalid');
    }

    private static function dpSchemaTestData(string $filePrefix): Generator
    {
        /** @var _SpecProtocol $spec */
        foreach ([
            SpecFactory::make1dot6(),
            SpecFactory::make1dot5(),
            SpecFactory::make1dot4(),
            SpecFactory::make1dot3(),
            SpecFactory::make1dot2(),
        ] as $spec) {
            $specVersion = $spec->getVersion()->value;
            foreach (glob(__DIR__."/../../../_data/schemaTestData/$specVersion/$filePrefix-*.json") as $file) {
                yield "$specVersion ".basename($file, '.json') => [$spec, $file];
            }
        }
    }
}
