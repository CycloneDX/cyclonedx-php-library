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

namespace CycloneDX\Tests\Core\Factories;

use CycloneDX\Core\Factories\LicenseFactory;
use CycloneDX\Core\Models\License\LicenseExpression;
use CycloneDX\Core\Models\License\NamedLicense;
use CycloneDX\Core\Models\License\SpdxLicense;
use CycloneDX\Core\Spdx\LicenseValidator as SpdxLicenseValidator;
use DomainException;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DependsUsingShallowClone;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;


class LicenseFactoryTest extends TestCase
{
    // @TODO
}
