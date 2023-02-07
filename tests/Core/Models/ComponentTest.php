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

#[\PHPUnit\Framework\Attributes\CoversClass(\CycloneDX\Core\Models\Component::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(\CycloneDX\Core\Models\BomRef::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(\CycloneDX\Core\Collections\LicenseRepository::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(\CycloneDX\Core\Collections\HashDictionary::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(\CycloneDX\Core\Collections\ExternalReferenceRepository::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(\CycloneDX\Core\Collections\BomRefRepository::class)]
#[\PHPUnit\Framework\Attributes\UsesClass(\CycloneDX\Core\Collections\PropertyRepository::class)]
class ComponentTest extends TestCase
{
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

     #[\PHPUnit\Framework\Attributes\DependsUsingShallowClone('testConstructor')]
    public function testSetBomRefValue(Component $component): void
    {
        $bomRef = $component->getBomRef();
        self::assertNotSame('foo', $bomRef->getValue());
        $actual = $component->setBomRefValue('foo');
        self::assertSame($component, $actual);
        self::assertSame('foo', $bomRef->getValue());
    }

    // region type getter&setter

     #[\PHPUnit\Framework\Attributes\DependsUsingShallowClone('testConstructor')]
    public function testTypeSetterGetter(Component $component): void
    {
        $type = ComponentType::FILE;
        self::assertNotSame($type, $component->getType());
        $actual = $component->setType($type);
        self::assertSame($component, $actual);
        self::assertSame($type, $component->getType());
    }

    // endregion type getter&setter

    // region version setter&getter

     #[\PHPUnit\Framework\Attributes\DependsUsingShallowClone('testConstructor')]
    public function testVersionSetterGetter(Component $component): void
    {
        $version = uniqid('v', true);
        self::assertNotSame($version, $component->getVersion());
        $actual = $component->setVersion($version);
        self::assertSame($component, $actual);
        self::assertSame($version, $component->getVersion());
    }

    // endregion version setter&getter

    // region licenses setter&getter

     #[\PHPUnit\Framework\Attributes\DependsUsingShallowClone('testConstructor')]
    public function testLicensesSetterGetter(Component $component): void
    {
        $licenses = $this->createStub(LicenseRepository::class);
        self::assertnotSame($licenses, $component->getLicenses());
        $actual = $component->setLicenses($licenses);
        self::assertSame($component, $actual);
        self::assertSame($licenses, $component->getLicenses());
    }

    // endregion licenses setter&getter

    // region hashes setter&getter

     #[\PHPUnit\Framework\Attributes\DependsUsingShallowClone('testConstructor')]
    public function testHashesSetterGetter(Component $component): void
    {
        $hashes = $this->createStub(HashDictionary::class);
        self::assertnotSame($hashes, $component->getHashes());
        $actual = $component->setHashes($hashes);
        self::assertSame($component, $actual);
        self::assertSame($hashes, $component->getHashes());
    }

    // endregion hashes setter&getter

    // region packageUrl setter&getter

     #[\PHPUnit\Framework\Attributes\DependsUsingShallowClone('testConstructor')]
    public function testPackageUrlSetterGetter(Component $component): void
    {
        $url = $this->createMock(PackageUrl::class);
        self::assertNotSame($url, $component->getPackageUrl());
        $actual = $component->setPackageUrl($url);
        self::assertSame($component, $actual);
        self::assertSame($url, $component->getPackageUrl());
    }

    // endregion packageUrl setter&getter

    // region description setter&getter

     #[\PHPUnit\Framework\Attributes\DataProvider('dpDescriptionSetterGetter')]
    public function testDescriptionSetterGetter(Component $component, ?string $description, ?string $expected): void
    {
        $actual = $component->setDescription($description);
        self::assertSame($component, $actual);
        self::assertSame($expected, $component->getDescription());
    }

    public function dpDescriptionSetterGetter(): Generator
    {
        $component = clone $this->testConstructor();
        yield 'null' => [clone $component, null, null];
        yield 'empty string' => [clone $component, '', null];
        $description = bin2hex(random_bytes(32));
        yield 'non-empty-string' => [clone $component, $description, $description];
    }

    // endregion description setter&getter

    // region author setter&getter

     #[\PHPUnit\Framework\Attributes\DataProvider('dpAuthorSetterGetter')]
    public function testAuthorSetterGetter(Component $component, ?string $author, ?string $expected): void
    {
        $actual = $component->setAuthor($author);
        self::assertSame($component, $actual);
        self::assertSame($expected, $component->getAuthor());
    }

    public function dpAuthorSetterGetter(): Generator
    {
        $component = clone $this->testConstructor();
        yield 'null' => [clone $component, null, null];
        yield 'empty string' => [clone $component, '', null];
        $author = bin2hex(random_bytes(32));
        yield 'non-empty-string' => [clone $component, $author, $author];
    }

    // endregion author setter&getter

    // region group setter&getter

     #[\PHPUnit\Framework\Attributes\DataProvider('dpGroupSetterGetter')]
    public function testGroupSetterGetter(Component $component, ?string $group, ?string $expected): void
    {
        $actual = $component->setGroup($group);
        self::assertSame($component, $actual);
        self::assertSame($expected, $component->getGroup());
    }

    public function dpGroupSetterGetter(): Generator
    {
        $component = clone $this->testConstructor();
        yield 'null' => [clone $component, null, null];
        yield 'empty string' => [clone $component, '', null];
        $group = bin2hex(random_bytes(32));
        yield 'non-empty-string' => [clone $component, $group, $group];
    }

    // endregion group setter&getter

    // region dependenciesBomRefRepository setter&getter

     #[\PHPUnit\Framework\Attributes\DependsUsingShallowClone('testConstructor')]
    public function testDependenciesBomRefRepositorySetterGetter(Component $component): void
    {
        $repo = $this->createMock(BomRefRepository::class);
        self::assertNotSame($repo, $component->getDependencies());
        $actual = $component->setDependencies($repo);
        self::assertSame($component, $actual);
        self::assertSame($repo, $component->getDependencies());
    }

    // endregion dependenciesBomRefRepository setter&getter

    // region externalReferenceRepository setter&getter

     #[\PHPUnit\Framework\Attributes\DependsUsingShallowClone('testConstructor')]
    public function testExternalReferenceRepositorySetterGetter(Component $component): void
    {
        $extRefRepo = $this->createStub(ExternalReferenceRepository::class);
        self::assertNotSame($extRefRepo, $component->getExternalReferences());
        $actual = $component->setExternalReferences($extRefRepo);
        self::assertSame($component, $actual);
        self::assertSame($extRefRepo, $component->getExternalReferences());
    }

    // endregion externalReferenceRepository setter&getter

    // region properties setter&getter

     #[\PHPUnit\Framework\Attributes\DependsUsingShallowClone('testConstructor')]
    public function testGetterSetterProperties(Component $component): void
    {
        $properties = $this->createStub(PropertyRepository::class);
        self::assertNotSame($properties, $component->getProperties());
        $actual = $component->setProperties($properties);
        self::assertSame($component, $actual);
        self::assertSame($properties, $component->getProperties());
    }

    // endregion properties setter&getter

    // region clone

     #[\PHPUnit\Framework\Attributes\DependsUsingShallowClone('testConstructor')]
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
