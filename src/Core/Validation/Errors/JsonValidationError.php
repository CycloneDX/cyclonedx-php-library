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

namespace CycloneDX\Core\Validation\Errors;

use CycloneDX\Core\Validation\ValidationError;
use Opis\JsonSchema;

/**
 * @author jkowalleck
 */
class JsonValidationError extends ValidationError
{
    /**
     * @uses \Opis\JsonSchema\Errors\ErrorFormatter
     */
    public static function fromSchemaValidationError(JsonSchema\Errors\ValidationError $error): static
    {
        $formatter = new JsonSchema\Errors\ErrorFormatter();
        $message = json_encode(
            $formatter->format($error, true),
            \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES
        );
        \assert(\is_string($message));
        $instance = new static($message);
        $instance->error = $error;

        return $instance;
    }
}
