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

namespace CycloneDX\Core\Utils;

use Deprecated;
use Exception;

/**
 * Utility regarding:
 * - {@see \CycloneDX\Core\Models\Bom}.
 *
 * @author jkowalleck
 *
 * @deprecated
 */
abstract class BomUtility
{
    /**
     * Deprecated — Alias of {@see \CycloneDX\Contrib\Bom\Utils\randomSerialNumber()}.
     *
     * Generate valid random SerialNumbers for {@see \CycloneDX\Core\Models\Bom::setSerialNumber()}.
     *
     * @throws Exception if an appropriate source of randomness cannot be found
     *
     * @since 2.1.0
     * @deprecated Use {@see \CycloneDX\Contrib\Bom\Utils\randomSerialNumber()} instead
     */
    public static function randomSerialNumber(): string
    {
        return \CycloneDX\Contrib\Bom\Utils\randomSerialNumber();
    }
}
