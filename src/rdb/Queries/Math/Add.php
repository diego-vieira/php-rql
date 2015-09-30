<?php

namespace r\Queries\Math;

use r\ValuedQuery\ValuedQuery;
use r\pb\Term_TermType;

class Add extends BinaryOp
{
    public function __construct($value, $other)
    {
        parent::__construct(Term_TermType::PB_ADD, $value, $other);
    }
}
