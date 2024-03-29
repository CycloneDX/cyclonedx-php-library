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

use CycloneDX\Core\Collections\CopyrightRepository;
use CycloneDX\Core\Collections\LicenseRepository;
use CycloneDX\Core\Models\ComponentEvidence;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DependsUsingShallowClone;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ComponentEvidence::class)]
#[UsesClass(LicenseRepository::class)]
#[UsesClass(CopyrightRepository::class)]
class ComponentEvidenceTest extends TestCase
{
    public function testConstructor(): ComponentEvidence
    {
        $instance = new ComponentEvidence();

        self::assertCount(0, $instance->getLicenses());
        self::assertCount(0, $instance->getCopyright());

        return $instance;
    }

    #[DependsUsingShallowClone('testConstructor')]
    public function testLicensesSetterGetter(ComponentEvidence $evidence): void
    {
        $licenses = $this->createStub(LicenseRepository::class);
        $actual = $evidence->setLicenses($licenses);
        self::assertSame($evidence, $actual);
        self::assertSame($licenses, $evidence->getLicenses());
    }

    #[DependsUsingShallowClone('testConstructor')]
    public function testCopyrightSetterGetter(ComponentEvidence $evidence): void
    {
        $copyright = $this->createStub(CopyrightRepository::class);
        $actual = $evidence->setCopyright($copyright);
        self::assertSame($evidence, $actual);
        self::assertSame($copyright, $evidence->getCopyright());
    }
}
