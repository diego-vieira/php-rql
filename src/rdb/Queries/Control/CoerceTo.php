<?php

namespace r\Queries\Control;

use r\ValuedQuery\ValuedQuery;
use r\pb\Term_TermType;
use r\Datum\StringDatum;

class CoerceTo extends ValuedQuery
{
    public function __construct(ValuedQuery $value, $typeName)
    {
        if (!(is_object($typeName) && is_subclass_of($typeName, '\r\Query'))) {
            $typeName = new StringDatum($typeName);
        }

        $this->setPositionalArg(0, $value);
        $this->setPositionalArg(1, $typeName);
    }

    protected function getTermType()
    {
        return Term_TermType::PB_COERCE_TO;
    }
}
