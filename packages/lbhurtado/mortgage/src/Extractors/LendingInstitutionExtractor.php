<?php

namespace LBHurtado\Mortgage\Extractors;

use LBHurtado\Mortgage\Classes\LendingInstitution;
use LBHurtado\Mortgage\Attributes\ExtractorFor;
use LBHurtado\Mortgage\Enums\ExtractorType;

#[ExtractorFor(ExtractorType::LENDING_INSTITUTION)]
class LendingInstitutionExtractor extends BaseExtractor
{
    public function extract(): LendingInstitution
    {
        return ($this->inputs->buyer()->getLendingInstitution() ?? $this->inputs->order()->getLendingInstitution())
            ?? $this->inputs->property()->getLendingInstitution();
    }
}
