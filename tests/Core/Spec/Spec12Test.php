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
 * Copyright (c) Steve Springett. All Rights Reserved.
 */

namespace CycloneDX\Tests\Core\Spec;

use CycloneDX\Core\Spec\Format;
use CycloneDX\Core\Spec\Spec12;
use CycloneDX\Core\Spec\SpecInterface;

/**
 * @covers \CycloneDX\Core\Spec\Spec12
 */
class Spec12Test extends AbstractSpecTestCase
{
    protected function getSpec(): SpecInterface
    {
        return new Spec12();
    }

    protected function getSpecVersion(): string
    {
        return '1.2';
    }

    protected function shouldSupportFormats(): array
    {
        return [Format::XML, Format::JSON];
    }

    public function shouldSupportLicenseExpression(): bool
    {
        return true;
    }

    public function shouldSupportMetaData(): bool
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
        return false;
    }
}
