<?php

namespace r\Queries\Manipulation;

use r\Datum\StringDatum;
use r\ValuedQuery\ValuedQuery;
use r\pb\Term_TermType;

class GetField extends ValuedQuery
{
    public function __construct(ValuedQuery $sequence, $attribute)
    {
        if (!(is_object($attribute) && is_subclass_of($attribute, "\\r\\Query"))) {
            $attribute = new StringDatum($attribute);
        }

        $this->setPositionalArg(0, $sequence);
        $this->setPositionalArg(1, $attribute);
    }

    protected function getTermType()
    {
        return Term_TermType::PB_GET_FIELD;
    }
}
