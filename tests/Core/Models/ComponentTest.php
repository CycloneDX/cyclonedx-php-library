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

use CycloneDX\Core\Collections\BomRefRepository;
use CycloneDX\Core\Collections\ExternalReferenceRepository;
use CycloneDX\Core\Collections\HashDictionary;
use CycloneDX\Core\Collections\LicenseRepository;
use CycloneDX\Core\Collections\PropertyRepository;
use CycloneDX\Core\Enums\ComponentType;
use CycloneDX\Core\Models\BomRef;
use CycloneDX\Core\Models\Component;
use Generator;
use PackageUrl\PackageUrl;
use PHPUnit\Framework\TestCase;

/**
 * Class ComponentTest.
 *
 * @covers \CycloneDX\Core\Models\Component
 *
 * @uses \CycloneDX\Core\Models\BomRef::__construct
 * @uses \CycloneDX\Core\Collections\LicenseRepository
 * @uses \CycloneDX\Core\Collections\HashDictionary
 * @uses \CycloneDX\Core\Collections\ExternalReferenceRepository
 * @uses \CycloneDX\Core\Collections\BomRefRepository
 * @uses \CycloneDX\Core\Collections\PropertyRepository
 */
class ComponentTest extends TestCase
{
    /**
     * @uses \CycloneDX\Core\Models\BomRef
     */
    public function testConstructor(): Component
    {
        $type = ComponentType::LIBRARY;
        $name = bin2hex(random_bytes(random_int(23, 255)));

        $component = new Component($type, $name);

        self::assertInstanceOf(BomRef::class, $component->getBomRef());
        self::assertCount(0, $component->getDependencies());
        self::assertNull($component->getDescription());
        self::assertNull($component->getAuthor());
        self::assertCount(0, $component->getExternalReferences());
        self::assertNull($component->getGroup());
        self::assertCount(0, $component->getHashes());
        self::assertCount(0, $component->getLicenses());
        self::assertSame($name, $component->getName());
        self::assertNull($component->getPackageUrl());
        self::assertSame($type, $component->getType());
        self::assertNull($component->getVersion());
        self::assertCount(0, $component->getProperties());

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
     */
    public function testTypeSetterGetter(Component $component): void
    {
        $type = ComponentType::LIBRARY;
        $component->setType($type);
        self::assertSame($type, $component->getType());
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
     * @depends testConstructor
     */
    public function testLicensesSetterGetter(Component $component): void
    {
        $licenses = $this->createStub(LicenseRepository::class);
        $component->setLicenses($licenses);
        self::assertSame($licenses, $component->getLicenses());
    }

    // endregion licenses setter&getter

    // region hashes setter&getter

    /**
     * @depends testConstructor
     */
    public function testHashesSetterGetter(Component $component): void
    {
        $hashes = $this->createStub(HashDictionary::class);
        $component->setHashes($hashes);
        self::assertSame($hashes, $component->getHashes());
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

    public function dpDescriptionSetterGetter(): Generator
    {
        $component = $this->testConstructor();
        yield 'null' => [$component, null, null];
        yield 'empty string' => [$component, '', null];
        $group = bin2hex(random_bytes(32));
        yield 'non-empty-string' => [$component, $group, $group];
    }

    // endregion description setter&getter

    // region author setter&getter

    /**
     * @dataProvider dpAuthorSetterGetter
     */
    public function testAuthorSetterGetter(Component $component, ?string $author, ?string $expected): void
    {
        $setOn = $component->setAuthor($author);

        self::assertSame($component, $setOn);
        self::assertSame($expected, $component->getAuthor());
    }

    public function dpAuthorSetterGetter(): Generator
    {
        $component = $this->testConstructor();
        yield 'null' => [$component, null, null];
        yield 'empty string' => [$component, '', null];
        $group = bin2hex(random_bytes(32));
        yield 'non-empty-string' => [$component, $group, $group];
    }

    // endregion author setter&getter

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

    public function dpGroupSetterGetter(): Generator
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
        self::assertNotSame($repo, $component->getDependencies());

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

    // region properties setter&getter

    /**
     * @depends testConstructor
     */
    public function testGetterSetterProperties(Component $component): void
    {
        $properties = $this->createStub(PropertyRepository::class);
        $component->setProperties($properties);
        self::assertSame($properties, $component->getProperties());
    }

    // endregion properties setter&getter

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
