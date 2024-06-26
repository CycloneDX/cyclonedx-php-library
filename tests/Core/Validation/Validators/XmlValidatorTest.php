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
use CycloneDX\Core\Validation\Errors\XmlValidationError;
use CycloneDX\Core\Validation\Exceptions\FailedLoadingSchemaException;
use CycloneDX\Core\Validation\ValidationError;
use CycloneDX\Core\Validation\Validators\XmlValidator;
use DOMDocument;
use DOMException;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\TestCase;

#[CoversClass(XmlValidator::class)]
#[CoversClass(BaseValidator::class)]
#[UsesClass(XmlValidationError::class)]
#[UsesClass(ValidationError::class)]
#[UsesClass(_Spec::class)]
class XmlValidatorTest extends TestCase
{
    public function testConstructor(): XmlValidator
    {
        $spec = $this->createStub(_SpecProtocol::class);
        $validator = new XmlValidator($spec);
        self::assertSame($spec, $validator->getSpec());

        return $validator;
    }

    public function testValidateString(): void
    {
        $validator = $this->createPartialMock(XmlValidator::class, ['validateDom']);
        $xml = '<bom/>';

        $validator->expects(self::once())->method('validateDom')
            ->with(new IsInstanceOf(DOMDocument::class))
            ->willReturn(null);

        $error = $validator->validateString($xml);

        self::assertNull($error);
    }

    public function testValidateStringError(): void
    {
        $validator = $this->createPartialMock(XmlValidator::class, ['validateDom']);
        $xml = '<bom/>';
        $expectedError = $this->createStub(XmlValidationError::class);

        $validator->expects(self::once())->method('validateDom')
            ->with(new IsInstanceOf(DOMDocument::class))
            ->willReturn($expectedError);

        $error = $validator->validateString($xml);

        self::assertSame($expectedError, $error);
    }

    public function testValidateStringThrowsWhenNotParseable(): void
    {
        $spec = $this->createConfiguredMock(_SpecProtocol::class, ['getVersion' => Version::v1dot2]);
        $validator = new XmlValidator($spec);
        $xml = '<bom>some invalid XML';

        $this->expectException(DOMException::class);
        $this->expectExceptionMessageMatches('/loading failed/i');

        $validator->validateString($xml);
    }

    public function testValidateDomPasses(): void
    {
        $spec = $this->createConfiguredMock(_SpecProtocol::class, ['getVersion' => Version::v1dot2]);
        $validator = new XmlValidator($spec);
        $doc = new DOMDocument();
        $loaded = $doc->loadXML(
            <<<'XML'
                <?xml version="1.0" encoding="utf-8"?>
                <bom xmlns="http://cyclonedx.org/schema/bom/1.2" version="1">
                    <components>
                        <component type="library">
                            <name>tomcat-catalina</name>
                            <version>9.0.14</version>
                            <licenses>
                                <license>
                                    <id>MIT</id>
                                </license>
                            </licenses>
                        </component>
                    </components>
                </bom>
                XML,
            \LIBXML_NONET
        );
        self::assertTrue($loaded);

        $error = $validator->validateDom($doc);
        self::assertNull($error);
    }

    public function testValidateDomFails(): void
    {
        $spec = $this->createConfiguredMock(_SpecProtocol::class, ['getVersion' => Version::v1dot2]);
        $validator = new XmlValidator($spec);
        $doc = new DOMDocument();
        $loaded = $doc->loadXML(
            <<<'XML'
                <?xml version="1.0" encoding="utf-8"?>
                <bom xmlns="http://cyclonedx.org/schema/bom/1.2" version="1">
                    <components>
                        <component type="library">
                            <name>tomcat-catalina</name>
                            <version>9.0.14</version>
                            <licenses>
                                <license>
                                    <id>MIT</id>
                                    <name>Some License</name>
                                    <!-- Errors: eiter ID or name ... -->
                                </license>
                            </licenses>
                        </component>
                    </components>
                </bom>
                XML,
            \LIBXML_NONET
        );
        self::assertTrue($loaded);

        $error = $validator->validateDom($doc);

        self::assertNotNull($error);
        self::assertInstanceOf(XmlValidationError::class, $error);
        self::assertStringContainsString(
            "Element '{http://cyclonedx.org/schema/bom/1.2}name': This element is not expected.",
            $error->getMessage()
        );
    }

    public function testValidateDomThrowsOnDuplicateBomRef(): void
    {
        $spec = $this->createConfiguredMock(_SpecProtocol::class, ['getVersion' => Version::v1dot2]);
        $validator = new XmlValidator($spec);
        $doc = new DOMDocument();
        $loaded = $doc->loadXML(
            <<<'XML'
                <?xml version="1.0" encoding="utf-8"?>
                <bom xmlns="http://cyclonedx.org/schema/bom/1.2" version="1">
                    <components>
                        <component type="library" bom-ref="BomRef-foo">
                            <name>foo1</name>
                            <version>1.2.3</version>
                        </component>
                        <component type="library" bom-ref="BomRef-foo">
                            <name>foo2</name>
                            <version>1.0.0</version>
                        </component>
                    </components>
                </bom>
                XML,
            \LIBXML_NONET
        );
        self::assertTrue($loaded);

        $error = $validator->validateDom($doc);

        self::assertNotNull($error);
        self::assertInstanceOf(XmlValidationError::class, $error);
        self::assertStringContainsString(
            "Element '{http://cyclonedx.org/schema/bom/1.2}component': Duplicate key-sequence ['BomRef-foo'] in unique identity-constraint '{http://cyclonedx.org/schema/bom/1.2}bom-ref'.",
            $error->getMessage()
        );
    }

    public function testValidateDomThrowsOnSchemaFileUnknown(): void
    {
        self::markTestSkipped('skipped, unless there is a version that does not support XML.');

        $spec = $this->createConfiguredMock(_SpecProtocol::class, ['getVersion' => Version::v1dot1]);
        $validator = new XmlValidator($spec);
        $doc = $this->createPartialMock(DOMDocument::class, ['schemaValidate']);

        $doc->expects(self::never())->method('schemaValidate');

        $this->expectException(FailedLoadingSchemaException::class);

        $validator->validateDom($doc);
    }

    #[DataProvider('dpSchemaTestDataValid')]
    public function testFunctionalValid(_Spec $spec, string $file): void
    {
        $validator = new XmlValidator($spec);
        $errors = $validator->validateString(file_get_contents($file));
        $this->assertNull($errors);
    }

    #[DataProvider('dpSchemaTestDataInvalid')]
    public function testFunctionalInvalid(_Spec $spec, string $file): void
    {
        $validator = new XmlValidator($spec);
        $errors = $validator->validateString(file_get_contents($file));
        $this->assertInstanceOf(XmlValidationError::class, $errors);
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
            SpecFactory::make1dot1(),
        ] as $spec) {
            $specVersion = $spec->getVersion()->value;
            foreach (glob(__DIR__."/../../../_data/schemaTestData/$specVersion/$filePrefix-*.xml") as $file) {
                yield "$specVersion ".basename($file, '.xml') => [$spec, $file];
            }
        }
    }
}
