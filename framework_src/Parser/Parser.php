<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Parser;

interface Parser {

    /**
     * @param list<string>|string $dirs
     * @return ParserResult
     */
    public function parse(array|string $dirs) : ParserResult;

}