<?php

namespace LBHurtado\Mortgage\Enums;

enum ExtractorType: string
{
    case INCOME_REQUIREMENT_MULTIPLIER = 'income_requirement_multiplier';
    case LENDING_INSTITUTION = 'lending_institution';
}
