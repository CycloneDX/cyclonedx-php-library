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

use Generator;

abstract class AnyUriData
{
    /**
     * List of RAW to ENCODED urls according to
     * - XML::anyURL {@see https://datatracker.ietf.org/doc/html/rfc2396 spec}.
     * - JSON::iri-reference {@link https://datatracker.ietf.org/doc/html/rfc3987 spec}.
     *
     * @return Generator<string[]>
     *
     * @psalm-return Generator<string, array{0:string, 1:string}>
     */
    public static function dpEncodeAnyUri(): Generator
    {
        yield 'encode anyUri: empty' => ['', ''];
        yield 'encode anyUri: urn' => ['urn:example:org', 'urn:example:org'];
        yield 'encode anyUri: https' => ['https://example.org/p?k=v#f', 'https://example.org/p?k=v#f'];
        yield 'encode anyUri: mailto' => ['mailto:info@example.org', 'mailto:info@example.org'];
        yield 'encode anyUri: relative path' => ['../foo/bar', '../foo/bar'];
        yield 'encode anyUri: space' => ['https://example.org/foo bar', 'https://example.org/foo%20bar'];
        yield 'encode anyUri: quote' => ['https://example.org/#"test"is\'test\'', 'https://example.org/#%22test%22is%27test%27'];
        yield 'encode anyUri: []' => ['https://example.org/?bar[test]=baz[again]', 'https://example.org/?bar%5Btest%5D=baz%5Bagain%5D'];
        yield 'encode anyUri: <>' => ['https://example.org/#<test><again>', 'https://example.org/#%3Ctest%3E%3Cagain%3E'];
        yield 'encode anyUri: {}' => ['https://example.org/#{test}{again}', 'https://example.org/#%7Btest%7D%7Bagain%7D'];
        yield 'encode anyUri: non-ASCII' => ['https://example.org/édition', 'https://example.org/édition'];
        yield 'encode anyUri: partially encoded' => ['https://example.org/?bar[test%5D=baz', 'https://example.org/?bar%5Btest%5D=baz'];
    }
}
