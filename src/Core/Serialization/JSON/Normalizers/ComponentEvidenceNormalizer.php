<?php

namespace CycloneDX\Core\Serialization\JSON\Normalizers;

use CycloneDX\Core\Models\ComponentEvidence;
use CycloneDX\Core\Serialization\JSON\_BaseNormalizer;

/**
 * @author jkowalleck
 */
class ComponentEvidenceNormalizer  extends _BaseNormalizer {

    public function normalize(ComponentEvidence $evidence): array {
        // @TODO
        return [];
    }

}
