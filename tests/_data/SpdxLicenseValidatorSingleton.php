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

use CycloneDX\Core\Spdx\LicenseValidator as SpdxLicenseValidator;
use RuntimeException;

abstract class SpdxLicenseValidatorSingleton
{
    private static ?SpdxLicenseValidator $instance = null;

    /**
     * @throws RuntimeException if loading licenses failed
     */
    public static function getInstance(): SpdxLicenseValidator
    {
        if (null === self::$instance) {
            self::$instance = new SpdxLicenseValidator();
        }

        return self::$instance;
    }
}
