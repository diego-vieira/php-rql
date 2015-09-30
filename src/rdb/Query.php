<?php

namespace r;

use r\pb\Term_TermType;
use r\ValuedQuery\Info;
use r\Queries\Control\RDefault;
use r\Exceptions\RqlDriverError;

abstract class Query
{
    private $positionalArgs = array();
    private $optionalArgs = array();
    private $unwrappedImplicitVar = false;

    abstract protected function getTermType();

    protected function setOptionalArg($key, Query $val)
    {
        if (!is_string($key)) {
            throw new RqlDriverError("Internal driver error: Got a non-string key for an optional argument.");
        }
        if ($val->_hasUnwrappedImplicitVar()) {
            $this->unwrappedImplicitVar = true;
        }
        $this->optionalArgs[$key] = $val;
    }

    protected function setPositionalArg($pos, Query $arg)
    {
        if (!is_numeric($pos)) {
            throw new RqlDriverError("Internal driver error: Got a non-numeric position for a positional argument.");
        }
        if ($arg->_hasUnwrappedImplicitVar()) {
            $this->unwrappedImplicitVar = true;
        }
        $this->positionalArgs[$pos] = $arg;
    }

    public function _hasUnwrappedImplicitVar()
    {
        return $this->unwrappedImplicitVar;
    }

    public function _getJSONTerm()
    {
        $args = array();
        foreach ($this->positionalArgs as $i => $arg) {
            $args[] = $arg->_getJSONTerm();
        }
        $optargs = array();
        foreach ($this->optionalArgs as $key => $val) {
            $optargs[$key] = $val->_getJSONTerm();
        }
        return array($this->getTermType(), $args, (object)$optargs);
    }

    public function run(Connection $connection, $options = null)
    {
        return $connection->_run($this, $options, $profile);
    }

    public function profile(Connection $connection, $options = null, &$result = null)
    {
        if (!isset($options)) {
            $options = array();
        }
        $options['profile'] = true;
        $result = $connection->_run($this, $options, $profile);
        return $profile;
    }

    public function info()
    {
        return new Info($this);
    }
    public function rDefault($defaultCase)
    {
        return new RDefault($this, $defaultCase);
    }

    public function __toString()
    {
        $backtrace = null;
        return $this->_toString($backtrace);
    }

    public function _toString(&$backtrace)
    {
        // TODO (daniel): This kind of printing backtraces is pretty hacky. Overhaul this.
        //  Maybe we could generate a PHP backtrace structure...

        $backtraceFrame = null;
        if (isset($backtrace) && $backtrace !== false) {
            $backtraceFrame = $backtrace->_consumeFrame();
        }

        $types = (new \ReflectionObject(new Term_TermType()));
        $types = $types->getConstants();
        $type = "UNKNOWN";
        foreach ($types as $key => $val) {
            if (substr($key, 0, 3) != "PB_") {
                continue;
            }
            if ($val == $this->getTermType()) {
                $type = substr($key, 3);
            }
        }

        $argList = "";
        foreach ($this->positionalArgs as $i => $arg) {
            if ($i > 0) {
                if (isset($backtrace)) {
                    $argList .= "  ";
                } else {
                    $argList .= ", ";
                }
            }

            $subTrace = is_null($backtrace) ? null : false;
            if (is_object($backtraceFrame)
                && $backtraceFrame->isPositionalArg()
                && $backtraceFrame->getPositionalArgPosition() == $i
            ) {
                $subTrace = $backtrace;
            }
            $argList .= $arg->_toString($subTrace);
        }

        $optArgList = "";
        $firstOptArg = true;
        foreach ($this->optionalArgs as $key => $val) {
            if (!$firstOptArg) {
                if (isset($backtrace)) {
                    $optArgList .= "  ";
                } else {
                    $optArgList .= ", ";
                }
            }
            $firstOptArg = false;

            $subTrace = is_null($backtrace) ? null : false;
            if (is_object($backtraceFrame)
                && $backtraceFrame->isOptionalArg()
                && $backtraceFrame->getOptionalArgName() == $key
            ) {
                $subTrace = $backtrace;
            }
            if (isset($backtrace)) {
                $optArgList .= str_repeat(" ", strlen($key)) . "    " . $val->_toString($subTrace);
            } else {
                $optArgList .= $key . " => " . $val->_toString($subTrace);
            }
        }

        if ($optArgList) {
            if (strlen($argList) > 0) {
                if (isset($backtrace)) {
                    $argList .= "  ";
                } else {
                    $argList .= ", ";
                }
            }
            if (isset($backtrace)) {
                $argList .= "        " . $optArgList . " ";
            } else {
                $argList .= "OptArgs(" . $optArgList . ")";
            }
        }

        $result = $type . "(" . $argList . ")";
        if (isset($backtrace)) {
            if ($backtraceFrame === false) {
                // We are the origin of the trouble
                return str_repeat("~", strlen($result));
            } else {
                return str_repeat(" ", strlen($type)) . " " . $argList . " ";
            }
        } else {
            return $result;
        }
    }
}
