#!/usr/bin/env php
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

/**
 * Schema Downloader.
 *
 * @internal
 *
 * @author jkowalleck
 */

namespace tools\CycloneDX\SchemaDownloader;

const SOURCE_ROOT = 'https://raw.githubusercontent.com/CycloneDX/specification/master/schema/';
const TARGET_ROOT = __DIR__.'/../../res/';

abstract class BaseDownloadable
{
    /** @psalm-var list<string> */
    public const Versions = [];

    /** @var string */
    public const SourcePattern = SOURCE_ROOT.'...';

    /** @var string */
    public const TargetPattern = TARGET_ROOT.'...';

    /** @psalm-var array<string, string>  */
    public const Replace = [];
}

abstract class BomXsd extends BaseDownloadable
{
    final public const Versions = ['1.0', '1.1', '1.2', '1.3', '1.4'];
    final public const SourcePattern = SOURCE_ROOT.'bom-%s.xsd';
    final public const TargetPattern = TARGET_ROOT.'bom-%s.SNAPSHOT.xsd';
    final public const Replace = [
        'schemaLocation="http://cyclonedx.org/schema/spdx"' => 'schemaLocation="spdx.SNAPSHOT.xsd"',
        'schemaLocation="https://cyclonedx.org/schema/spdx"' => 'schemaLocation="spdx.SNAPSHOT.xsd"',
    ];
}

abstract class BomJsonLax extends BaseDownloadable
{
    final public const Versions = ['1.2', '1.3', '1.4'];
    final public const SourcePattern = SOURCE_ROOT.'bom-%s.schema.json';
    final public const TargetPattern = TARGET_ROOT.'bom-%s.SNAPSHOT.schema.json';
    final public const Replace = [
        'spdx.schema.json' => 'spdx.SNAPSHOT.schema.json',
        'jsf-0.82.schema.json' => 'jsf-0.82.SNAPSHOT.schema.json',
    ];
}

abstract class BomJsonStrict extends BaseDownloadable
{
    final public const Versions = ['1.2', '1.3'];
    final public const SourcePattern = SOURCE_ROOT.'bom-%s-strict.schema.json';
    final public const TargetPattern = TARGET_ROOT.'bom-%s-strict.SNAPSHOT.schema.json';
    final public const Replace = BomJsonLax::Replace;
}

const OtherDownloadables = [
    SOURCE_ROOT.'spdx.schema.json' => TARGET_ROOT.'spdx.SNAPSHOT.schema.json',
    SOURCE_ROOT.'spdx.xsd' => TARGET_ROOT.'spdx.SNAPSHOT.xsd',
    SOURCE_ROOT.'jsf-0.82.schema.json' => TARGET_ROOT.'jsf-0.82.SNAPSHOT.schema.json',
];

/** @psalm-var class-string<BaseDownloadable>  $class */
foreach ([
             BomXsd::class,
             BomJsonLax::class,
             BomJsonStrict::class,
         ] as $class) {
    foreach ($class::Versions as $version) {
        $source = sprintf($class::SourcePattern, $version);
        $target = sprintf($class::TargetPattern, $version);

        $content = file_get_contents($source);
        $content = strtr($content, $class::Replace);
        file_put_contents($target, $content);
        unset($content);
    }
}

foreach (OtherDownloadables as $source => $target) {
    file_put_contents($target, file_get_contents($source));
}
