<?php

namespace r\Queries\Misc;

use r\ValuedQuery\ValuedQuery;
use r\pb\Term_TermType;

class Minval extends ValuedQuery
{
    protected function getTermType()
    {
        return Term_TermType::PB_MINVAL;
    }
}
