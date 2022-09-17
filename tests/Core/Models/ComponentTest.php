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

namespace CycloneDX\Tests\Core\Models;

use CycloneDX\Core\Enums\Classification;
use CycloneDX\Core\Models\BomRef;
use CycloneDX\Core\Models\Component;
use CycloneDX\Core\Models\License\LicenseExpression;
use CycloneDX\Core\Collections\BomRefRepository;
use CycloneDX\Core\Collections\LicenseRepository;
use CycloneDX\Core\Collections\ExternalReferenceRepository;
use CycloneDX\Core\Collections\HashDictionary;
use PackageUrl\PackageUrl;
use PHPUnit\Framework\TestCase;

/**
 * Class ComponentTest.
 *
 * @covers \CycloneDX\Core\Models\Component
 *
 * @uses   \CycloneDX\Core\Enums\Classification::isValidValue
 * @uses   \CycloneDX\Core\Models\BomRef::__construct
 */
class ComponentTest extends TestCase
{
    /**
     * @uses \CycloneDX\Core\Enums\Classification::isValidValue
     * @uses \CycloneDX\Core\Models\BomRef
     */
    public function testConstructor(): Component
    {
        $type = Classification::LIBRARY;
        $name = bin2hex(random_bytes(random_int(23, 255)));

        $component = new Component($type, $name);

        self::assertInstanceOf(BomRef::class, $component->getBomRef());
        self::assertNull($component->getDependenciesBomRefRepository());
        self::assertNull($component->getDescription());
        self::assertNull($component->getExternalReferenceRepository());
        self::assertNull($component->getGroup());
        self::assertNull($component->getHashRepository());
        self::assertNull($component->getLicense());
        self::assertSame($name, $component->getName());
        self::assertNull($component->getPackageUrl());
        self::assertSame($type, $component->getType());
        self::assertNull($component->getVersion());

        return $component;
    }

    /**
     * @depends testConstructor
     *
     * @uses \CycloneDX\Core\Models\BomRef::getValue
     * @uses \CycloneDX\Core\Models\BomRef::setValue
     */
    public function testSetBomRefValue(Component $component): void
    {
        $bomRef = $component->getBomRef();
        self::assertNull($bomRef->getValue());

        $component->setBomRefValue('foo');

        self::assertSame('foo', $bomRef->getValue());
    }

    // region type getter&setter

    /**
     * @depends testConstructor
     *
     * @uses \CycloneDX\Core\Enums\Classification::isValidValue()
     */
    public function testTypeSetterGetter(Component $component): void
    {
        $type = Classification::LIBRARY;
        $component->setType($type);
        self::assertSame($type, $component->getType());
    }

    /**
     * @depends testConstructor
     *
     * @uses \CycloneDX\Core\Enums\Classification::isValidValue()
     */
    public function testSetTypeWithUnknownValue(Component $component): void
    {
        $this->expectException(\DomainException::class);
        $component->setType('something unknown');
    }

    // endregion type getter&setter

    // region version setter&getter

    /**
     * @depends testConstructor
     */
    public function testVersionSetterGetter(Component $component): void
    {
        $version = uniqid('v', true);
        $component->setVersion($version);
        self::assertSame($version, $component->getVersion());
    }

    // endregion version setter&getter

    // region licenses setter&getter

    /**
     * @dataProvider dpLicensesSetterGetter
     */
    public function testLicensesSetterGetter(Component $component, $license): void
    {
        $component->setLicenses($license);
        self::assertSame($license, $component->getLicense());
    }

    public function dpLicensesSetterGetter(): \Generator
    {
        $component = $this->testConstructor();
        yield 'null' => [$component, null];
        yield 'repo' => [$component, $this->createStub(LicenseRepository::class)];
        yield 'expression' => [$component, $this->createStub(LicenseExpression::class)];
    }

    // endregion licenses setter&getter

    // region hashes setter&getter

    /**
     * @dataProvider dpHashesSetterGetter
     */
    public function testHashesSetterGetter(Component $component, $hashes): void
    {
        $component->setHashes($hashes);
        self::assertSame($hashes, $component->getHashes());
    }

    public function dpHashesSetterGetter(): \Generator
    {
        $component = $this->testConstructor();
        yield 'null' => [$component, null];
        yield 'repo' => [$component, $this->createStub(HashDictionary::class)];
    }

    // endregion hashes setter&getter

    // region packageUrl setter&getter

    /**
     * @depends testConstructor
     */
    public function testPackageUrlSetterGetter(Component $component): void
    {
        $url = $this->createMock(PackageUrl::class);
        $component->setPackageUrl($url);
        self::assertSame($url, $component->getPackageUrl());
    }

    // endregion packageUrl setter&getter

    // region description setter&getter

    /**
     * @dataProvider dpDescriptionSetterGetter
     */
    public function testDescriptionSetterGetter(Component $component, ?string $description, ?string $expected): void
    {
        $setOn = $component->setDescription($description);

        self::assertSame($component, $setOn);
        self::assertSame($expected, $component->getDescription());
    }

    public function dpDescriptionSetterGetter(): \Generator
    {
        $component = $this->testConstructor();
        yield 'null' => [$component, null, null];
        yield 'empty string' => [$component, '', null];
        $group = bin2hex(random_bytes(32));
        yield 'non-empty-string' => [$component, $group, $group];
    }

    // endregion description setter&getter

    // region group setter&getter

    /**
     * @dataProvider dpGroupSetterGetter
     */
    public function testGroupSetterGetter(Component $component, ?string $group, ?string $expected): void
    {
        $setOn = $component->setGroup($group);

        self::assertSame($component, $setOn);
        self::assertSame($expected, $component->getGroup());
    }

    public function dpGroupSetterGetter(): \Generator
    {
        $component = $this->testConstructor();
        yield 'null' => [$component, null, null];
        yield 'empty string' => [$component, '', null];
        $group = bin2hex(random_bytes(32));
        yield 'non-empty-string' => [$component, $group, $group];
    }

    // endregion group setter&getter

    // region dependenciesBomRefRepository setter&getter

    /**
     * @depends testConstructor
     */
    public function testDependenciesBomRefRepositorySetterGetter(Component $component): void
    {
        $repo = $this->createMock(BomRefRepository::class);
        self::assertNull($component->getDependencies());

        $component->setDependencies($repo);

        self::assertSame($repo, $component->getDependencies());
    }

    // endregion dependenciesBomRefRepository setter&getter

    // region externalReferenceRepository setter&getter

    /**
     * @depends testConstructor
     */
    public function testExternalReferenceRepositorySetterGetter(Component $component): void
    {
        $extRefRepo = $this->createStub(ExternalReferenceRepository::class);

        $actual = $component->setExternalReferences($extRefRepo);

        self::assertSame($component, $actual);
        self::assertSame($extRefRepo, $component->getExternalReferences());
    }

    // endregion externalReferenceRepository setter&getter

    // region clone

    /**
     * @depends testConstructor
     */
    public function testCloneHasOwnBom(Component $component): void
    {
        $component->setDescription('foobar');
        $actual = clone $component;

        self::assertEquals($component, $actual);
        self::assertSame('foobar', $component->getDescription());
        self::assertNotSame($component->getBomRef(), $actual->getBomRef());
    }

    // endregion clone
}
