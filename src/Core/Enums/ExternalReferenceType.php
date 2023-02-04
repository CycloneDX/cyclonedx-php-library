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

namespace CycloneDX\Core\Enums;

/**
 * See {@link https://cyclonedx.org/schema/bom/1.1 Schema 1.1} for `externalReferenceType`.
 * See {@link https://cyclonedx.org/schema/bom/1.2 Schema 1.2} for `externalReferenceType`.
 * See {@link https://cyclonedx.org/schema/bom/1.3 Schema 1.3} for `externalReferenceType`.
 * See {@link https://cyclonedx.org/schema/bom/1.4 Schema 1.4} for `externalReferenceType`.
 *
 * @author jkowalleck
 */
enum ExternalReferenceType: string
{
    /* Version Control System */
    case VCS = 'vcs';
    /* Issue or defect tracking system, or an Application Lifecycle Management (ALM) system */
    case ISSUE_TRACKER = 'issue-tracker';
    /* Website */
    case WEBSITE = 'website';
    /* Security advisories */
    case ADVISORIES = 'advisories';
    /* Bill-of-material document (CycloneDX, SPDX, SWID, etc) */
    case BOM = 'bom';
    /* Mailing list or discussion group */
    case MAILING_LIST = 'mailing-list';
    /* Social media account */
    case SOCIAL = 'social';
    /* Real-time chat platform */
    case CHAT = 'chat';
    /* Documentation, guides, or how-to instructions */
    case DOCUMENTATION = 'documentation';
    /* Community or commercial support */
    case SUPPORT = 'support';
    /*** Direct or repository download location.*/
    case DISTRIBUTION = 'distribution';
    /* The URL to the license file. If a license URL has been defined in the licensenode, it should also be defined as an external reference for completeness. */
    case LICENSE = 'license';
    /* Build-system specific meta file (i.e. pom.xml, package.json, .nuspec, etc). */
    case BUILD_META = 'build-meta';
    /* URL to an automated build system. */
    case BUILD_SYSTEM = 'build-system';
    /* URL to release notes. */
    case RELEASE_NOTES = 'release-notes';
    /* Use this if no other types accurately describe the purpose of the external reference. */
    case OTHER = 'other';
}
