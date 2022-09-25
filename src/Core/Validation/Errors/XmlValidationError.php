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
use LibXMLError;

/**
 * @author jkowalleck
 */
class XmlValidationError extends ValidationError
{
    /**
     * keep for internal debug purposes.
     */
    private ?object $debugError = null;

    /**
     * @internal as this function may be affected by breaking changes without notice
     */
    public static function fromLibXMLError(LibXMLError $error): static
    {
        $instance = new static($error->message);
        $instance->debugError = $error;

        return $instance;
    }

    /**
     * Accessor for debug purposes.
     *
     * @internal as this method may be affected by breaking changes without notice
     *
     * @codeCoverageIgnore
     *
     * @SuppressWarnings(PHPMD)
     */
    final public function debug_getError(): ?object
    {
        return $this->debugError;
    }
}
