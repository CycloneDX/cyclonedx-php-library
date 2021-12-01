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

namespace CycloneDX\Core\Serialize;

use CycloneDX\Core\Models\Bom;
use DomainException;

/**
 * transform data models to JSON.
 *
 * @author jkowalleck
 */
class JsonSerializer extends BaseSerializer
{
    private const NORMALIZE_OPTIONS =
        \JSON_THROW_ON_ERROR // prevent unexpected data
        | \JSON_PRESERVE_ZERO_FRACTION // float/double not converted to int
        | \JSON_UNESCAPED_SLASHES // urls become shorter
        | \JSON_PRETTY_PRINT;

    /**
     * @throws DomainException if something was not supported
     */
    protected function normalize(Bom $bom): string
    {
        $data = (new JSON\NormalizerFactory($this->getSpec()))
            ->makeForBom()
            ->normalize($bom);

        $json = json_encode($data, self::NORMALIZE_OPTIONS);
        \assert(false !== $json); // as option JSON_THROW_ON_ERROR is expected to be set

        return $json;
    }
}
