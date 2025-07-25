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

const SOURCE_ROOT = 'https://raw.githubusercontent.com/CycloneDX/specification/refs/tags/1.6.1/schema/';
const SOURCE_ROOT_LATEST = 'https://raw.githubusercontent.com/CycloneDX/specification/refs/heads/master/schema/';
const TARGET_ROOT = __DIR__.'/../../res/schema/';

abstract class BaseDownloadable
{
    /** @psalm-var list<string> */
    public const Versions = [];

    /** @var string */
    public const SourcePattern = SOURCE_ROOT.'...';

    /** @var string */
    public const TargetPattern = TARGET_ROOT.'...';

    /** @psalm-var array<string, string> */
    public const ReplaceStr = [];

    /** @var list<array{0:string, 1:string}> */
    public const ReplaceReg = [];
}

abstract class BomXsd extends BaseDownloadable
{
    final public const Versions = ['1.6', '1.5', '1.4', '1.3', '1.2', '1.1', '1.0'];
    final public const SourcePattern = SOURCE_ROOT.'bom-%s.xsd';
    final public const TargetPattern = TARGET_ROOT.'bom-%s.SNAPSHOT.xsd';
    final public const ReplaceStr = [];
    final public const ReplaceReg = [
        '#schemaLocation="https?://cyclonedx.org/schema/spdx"#' => 'schemaLocation="spdx.SNAPSHOT.xsd"',
    ];
}

/* "version" is not required but optional with a default value!
    this is wrong in schema<1.5 */
const _bomRequired = '"required": [
    "bomFormat",
    "specVersion",
    "version"
  ],';
const _bomRequiredReplace = '
  "required": [
    "bomFormat",
    "specVersion"
  ],';

abstract class BomJsonLax extends BaseDownloadable
{
    final public const Versions = ['1.6', '1.5', '1.4', '1.3', '1.2'];
    final public const SourcePattern = SOURCE_ROOT.'bom-%s.schema.json';
    final public const TargetPattern = TARGET_ROOT.'bom-%s.SNAPSHOT.schema.json';
    final public const ReplaceStr = [
        'spdx.schema.json' => 'spdx.SNAPSHOT.schema.json',
        'jsf-0.82.schema.json' => 'jsf-0.82.SNAPSHOT.schema.json',
        _bomRequired => _bomRequiredReplace,
    ];
    final public const ReplaceReg = [
        /* "$schema" is not required but optional.
           that enum constraint value there is complicated -> remove it.
           See https://github.com/CycloneDX/specification/issues/402
           See https://github.com/CycloneDX/specification/pull/403 */
        '#,?\\s*"enum"\\s*:\\s*\\[\\s+"http://cyclonedx\\.org/schema\/.+?\\.schema\\.json"\\s*\\]#s' => '',
        /* there was a case where the default value did not match the own pattern ...
            this is wrong in schema<1.5
            with current SchemaValidator this is no longer required, as defaults are not applied */
        // '/\s+"default": "",(?![^}]*?"pattern": "\^\(\.\*\)\$")/gm' => ''
    ];
}

abstract class BomJsonStrict extends BaseDownloadable
{
    final public const Versions = ['1.3', '1.2'];
    final public const SourcePattern = SOURCE_ROOT.'bom-%s-strict.schema.json';
    final public const TargetPattern = TARGET_ROOT.'bom-%s-strict.SNAPSHOT.schema.json';
    final public const ReplaceStr = BomJsonLax::ReplaceStr;
    final public const ReplaceReg = BomJsonLax::ReplaceReg;
}

const OtherDownloadables = [
    SOURCE_ROOT_LATEST.'spdx.schema.json' => TARGET_ROOT.'spdx.SNAPSHOT.schema.json',
    SOURCE_ROOT_LATEST.'spdx.xsd' => TARGET_ROOT.'spdx.SNAPSHOT.xsd',
    SOURCE_ROOT_LATEST.'jsf-0.82.schema.json' => TARGET_ROOT.'jsf-0.82.SNAPSHOT.schema.json',
];

/** @psalm-var class-string<BaseDownloadable> $class */
foreach ([
    BomXsd::class,
    BomJsonLax::class,
    BomJsonStrict::class,
] as $class) {
    foreach ($class::Versions as $version) {
        $source = \sprintf($class::SourcePattern, $version);
        $target = \sprintf($class::TargetPattern, $version);

        $content = file_get_contents($source);
        $content = strtr($content, $class::ReplaceStr);
        foreach ($class::ReplaceReg as $rp => $rr) {
            $content = preg_replace($rp, $rr, $content);
        }

        file_put_contents($target, $content);
        unset($content);
    }
}

foreach (OtherDownloadables as $source => $target) {
    file_put_contents($target, file_get_contents($source));
}
