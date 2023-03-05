<?php

namespace CycloneDX\Core\Serialization\DOM\Normalizers;

use CycloneDX\Core\Models\ComponentEvidence;
use CycloneDX\Core\Serialization\DOM\_BaseNormalizer;

/**
 * @author jkowalleck
 */
class ComponentEvidenceNormalizer  extends _BaseNormalizer {

    public function normalize(ComponentEvidence $evidence): \DOMElement {
        // @TODO
        return $this->getNormalizerFactory()->getDocument()->createElement('evidence');
    }

}
