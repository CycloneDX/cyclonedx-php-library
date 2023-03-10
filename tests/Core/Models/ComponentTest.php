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
use CycloneDX\Core\Models\ComponentEvidence;
use Generator;
use PackageUrl\PackageUrl;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DependsUsingShallowClone;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Component::class)]
#[UsesClass(BomRef::class)]
#[UsesClass(LicenseRepository::class)]
#[UsesClass(HashDictionary::class)]
#[UsesClass(ExternalReferenceRepository::class)]
#[UsesClass(BomRefRepository::class)]
#[UsesClass(PropertyRepository::class)]
class ComponentTest extends TestCase
{
    public function testConstructor(): Component
    {
        $type = ComponentType::Library;
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
        self::assertNull($component->getCopyright());
        self::assertNull($component->getEvidence());

        return $component;
    }

    #[DependsUsingShallowClone('testConstructor')]
    public function testSetBomRefValue(Component $component): void
    {
        $bomRef = $component->getBomRef();
        self::assertNotSame('foo', $bomRef->getValue());
        $actual = $component->setBomRefValue('foo');
        self::assertSame($component, $actual);
        self::assertSame('foo', $bomRef->getValue());
    }

    // region type getter&setter

    #[DependsUsingShallowClone('testConstructor')]
    public function testTypeSetterGetter(Component $component): void
    {
        $type = ComponentType::File;
        self::assertNotSame($type, $component->getType());
        $actual = $component->setType($type);
        self::assertSame($component, $actual);
        self::assertSame($type, $component->getType());
    }

    // endregion type getter&setter

    // region version setter&getter

    #[DependsUsingShallowClone('testConstructor')]
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

    #[DependsUsingShallowClone('testConstructor')]
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

    #[DependsUsingShallowClone('testConstructor')]
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

    #[DependsUsingShallowClone('testConstructor')]
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

    #[DataProvider('dpDescriptionSetterGetter')]
    public function testDescriptionSetterGetter(?string $description, ?string $expected): void
    {
        $component = new Component(ComponentType::Container, 'foo');
        $actual = $component->setDescription($description);
        self::assertSame($component, $actual);
        self::assertSame($expected, $component->getDescription());
    }

    public static function dpDescriptionSetterGetter(): Generator
    {
        yield 'null' => [null, null];
        yield 'empty string' => ['', null];
        $description = bin2hex(random_bytes(32));
        yield 'non-empty-string' => [$description, $description];
    }

    // endregion description setter&getter

    // region author setter&getter

    #[DataProvider('dpAuthorSetterGetter')]
    public function testAuthorSetterGetter(?string $author, ?string $expected): void
    {
        $component = new Component(ComponentType::Container, 'foo');
        $actual = $component->setAuthor($author);
        self::assertSame($component, $actual);
        self::assertSame($expected, $component->getAuthor());
    }

    public static function dpAuthorSetterGetter(): Generator
    {
        yield 'null' => [null, null];
        yield 'empty string' => ['', null];
        $author = bin2hex(random_bytes(32));
        yield 'non-empty-string' => [$author, $author];
    }

    // endregion author setter&getter

    // region group setter&getter

    #[DataProvider('dpGroupSetterGetter')]
    public function testGroupSetterGetter(?string $group, ?string $expected): void
    {
        $component = new Component(ComponentType::Container, 'foo');
        $actual = $component->setGroup($group);
        self::assertSame($component, $actual);
        self::assertSame($expected, $component->getGroup());
    }

    public static function dpGroupSetterGetter(): Generator
    {
        yield 'null' => [null, null];
        yield 'empty string' => ['', null];
        $group = bin2hex(random_bytes(32));
        yield 'non-empty-string' => [$group, $group];
    }

    // endregion group setter&getter

    // region dependenciesBomRefRepository setter&getter

    #[DependsUsingShallowClone('testConstructor')]
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

    #[DependsUsingShallowClone('testConstructor')]
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

    #[DependsUsingShallowClone('testConstructor')]
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

    #[DependsUsingShallowClone('testConstructor')]
    public function testCloneHasOwnBom(Component $component): void
    {
        $component->setDescription('foobar');
        $actual = clone $component;
        self::assertEquals($component, $actual);
        self::assertSame('foobar', $component->getDescription());
        self::assertNotSame($component->getBomRef(), $actual->getBomRef());
    }

    // endregion clone

    // region copyright setter&getter

    #[DataProvider('dpCopyrightSetterGetter')]
    public function testCopyrightSetterGetter(?string $copyright, ?string $expected): void
    {
        $component = new Component(ComponentType::Container, 'foo');
        $actual = $component->setCopyright($copyright);
        self::assertSame($component, $actual);
        self::assertSame($expected, $component->getCopyright());
    }

    public static function dpCopyrightSetterGetter(): Generator
    {
        yield 'null' => [null, null];
        yield 'empty string' => ['', null];
        $copyright = bin2hex(random_bytes(32));
        yield 'non-empty-string' => [$copyright, $copyright];
    }

    // endregion copyright setter&getter

    // region evidence setter&getter

    #[DependsUsingShallowClone('testConstructor')]
    public function testEvidenceSetterGetter(Component $component): void
    {
        $evidence = $this->createStub(ComponentEvidence::class);
        $actual = $component->setEvidence($evidence);
        self::assertSame($component, $actual);
        self::assertSame($evidence, $component->getEvidence());
    }

    #[DependsUsingShallowClone('testConstructor')]
    public function testEvidenceSetterGetterNull(Component $component): void
    {
        $component->setEvidence($this->createStub(ComponentEvidence::class));
        self::assertNotNull($component->getEvidence());
        $component->setEvidence(null);
        self::assertNull($component->getEvidence());
    }

    // endregion evidence setter&getter
}
