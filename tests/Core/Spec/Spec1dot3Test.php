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

namespace CycloneDX\Tests\Core\Spec;

use CycloneDX\Core\Spec\Format;
use CycloneDX\Core\Spec\Spec;
use CycloneDX\Core\Spec\SpecFactory;
use CycloneDX\Core\Spec\Version;

/**
 *
 * @uses \CycloneDX\Core\Spec\SpecFactory
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\CycloneDX\Core\Spec\_Spec::class)]
class Spec1dot3Test extends SpecBaseTestCase
{
    protected function getSpec(): Spec
    {
        return SpecFactory::make1dot3();
    }

    protected function getSpecVersion(): Version
    {
        return Version::v1dot3;
    }

    protected function shouldSupportFormats(): array
    {
        return [Format::XML, Format::JSON];
    }

    public function shouldSupportLicenseExpression(): bool
    {
        return true;
    }

    public function shouldSupportMetadata(): bool
    {
        return true;
    }

    public function shouldSupportBomRef(): bool
    {
        return true;
    }

    public function shouldSupportDependencies(): bool
    {
        return true;
    }

    public function shouldSupportExternalReferenceHashes(): bool
    {
        return true;
    }

    public function shouldRequireComponentVersion(): bool
    {
        return true;
    }

    public function shouldSupportToolExternalReferences(): bool
    {
        return false;
    }

    public function shouldSupportMetadataProperties(): bool
    {
        return true;
    }

    public function shouldSupportComponentProperties(): bool
    {
        return true;
    }
}
