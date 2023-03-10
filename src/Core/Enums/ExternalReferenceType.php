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
    /* Security advisories */
    case Advisories = 'advisories';

    /* Bill-of-material document (CycloneDX, SPDX, SWID, etc) */
    case BOM = 'bom';

    /* Build-system specific meta file (i.e. pom.xml, package.json, .nuspec, etc). */
    case BuildMeta = 'build-meta';

    /* URL to an automated build system. */
    case BuildSystem = 'build-system';

    /* Real-time chat platform */
    case Chat = 'chat';

    /* Direct or repository download location. */
    case Distribution = 'distribution';

    /* Documentation, guides, or how-to instructions */
    case Documentation = 'documentation';

    /* Issue or defect tracking system, or an Application Lifecycle Management (ALM) system */
    case IssueTracker = 'issue-tracker';

    /* The URL to the license file. If a license URL has been defined in the license node,
     * it should also be defined as an external reference for completeness.
     */
    case License = 'license';

    /* Mailing list or discussion group */
    case MailingList = 'mailing-list';

    /* URL to release notes. */
    case ReleaseNotes = 'release-notes';

    /* Social media account */
    case Social = 'social';

    /* Community or commercial support */
    case Support = 'support';

    /* Version Control System */
    case VCS = 'vcs';

    /* Website */
    case Website = 'website';

    // ----

    /* Use this if no other types accurately describe the purpose of the external reference. */
    case Other = 'other';
}
