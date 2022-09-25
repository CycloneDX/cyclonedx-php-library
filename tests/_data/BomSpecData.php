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

namespace CycloneDX\Tests\_data;

use Generator;

use function PHPUnit\Framework\assertFileExists;
use function PHPUnit\Framework\assertIsArray;
use function PHPUnit\Framework\assertIsString;
use function PHPUnit\Framework\assertNotCount;

use SimpleXMLElement;

abstract class BomSpecData
{
    /**
     * @psalm-suppress MissingThrowsDocblock
     */
    public static function getSpecFilePath(string $version): string
    {
        $file = realpath(__DIR__."/../../res/bom-$version.SNAPSHOT.xsd");
        assertIsString($file);
        assertFileExists($file);

        return $file;
    }

    /**
     * @return string[]
     *
     * @psalm-return list<string> sorted list
     */
    public static function getClassificationEnumForVersion(string $version): array
    {
        return self::getEnumValuesForName($version, 'classification');
    }

    /**
     * @return string[]
     *
     * @psalm-return list<string> sorted list
     */
    public static function getExternalReferenceTypeForVersion(string $version): array
    {
        return self::getEnumValuesForName($version, 'externalReferenceType');
    }

    /**
     * @return string[]
     *
     * @psalm-return list<string> sorted list
     */
    public static function getHashAlgEnumForVersion(string $version): array
    {
        return self::getEnumValuesForName($version, 'hashAlg');
    }

    // region helpers

    /** @psalm-var array<string, array<string, list<string>>>  */
    private static array $enumValueCache = [];

    /**
     * @return string[]
     *
     * @psalm-return list<string> sorted list
     *
     * @psalm-suppress MissingThrowsDocblock
     */
    private static function getEnumValuesForName(string $version, string $name): array
    {
        $values = self::$enumValueCache[$version][$name] ?? null;
        if (null === $values) {
            $values = iterator_to_array(self::getEnumValuesForNameFromFile($version, $name));
            assertNotCount(0, $values);
            sort($values, \SORT_STRING);
            self::$enumValueCache[$version][$name] = $values;
        }

        return $values;
    }

    /**
     * @psalm-return Generator<string>
     *
     * @psalm-suppress MissingThrowsDocblock
     */
    private static function getEnumValuesForNameFromFile(string $version, string $name): Generator
    {
        $specXml = self::getSpecFilePath($version);
        $xml = new SimpleXMLElement($specXml, 0, true);
        $xmlEnumElems = $xml->xpath("xs:simpleType[@name='$name']/xs:restriction/xs:enumeration/@value");
        assertIsArray($xmlEnumElems);
        foreach ($xmlEnumElems as $xmlEnumElem) {
            yield (string) $xmlEnumElem;
        }
    }

    // endregion helpers
}
