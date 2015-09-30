<?php

namespace r\Queries\Geo;

use r\ValuedQuery\ValuedQuery;
use r\pb\Term_TermType;

class Intersects extends ValuedQuery
{
    public function __construct($g1, $g2)
    {
        $this->setPositionalArg(0, \r\nativeToDatum($g1));
        $this->setPositionalArg(1, \r\nativeToDatum($g2));
    }

    protected function getTermType()
    {
        return Term_TermType::PB_INTERSECTS;
    }
}
