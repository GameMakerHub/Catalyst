<?php
namespace GMDepMan\Entity;

use Assert\Assert;
use Assert\Assertion;
use Assert\AssertionFailedException;
use GMDepMan\Exception\MalformedProjectFileException;
use Symfony\Component\Console\Output\Output;

class ProjectEntity {

    /** @var string */
    private $yypFilename;

    /** @var string */
    private $originalYyp;

    /**
     * Load a JSON string in YYP format
     * @param string $json
     */
    public function load(string $yypFilename)
    {
        Assertion::file($yypFilename);

        $this->originalYyp = json_decode(file_get_contents($this->yypFilename));

        try {
            Assertion::notNull($this->originalYyp);
        } catch (\InvalidArgumentException $e) {
            throw new MalformedProjectFileException($yypFilename . ' is not a valid GMS2 .yyp file');
        }

        $this->yypFilename = $yypFilename;
        return $this;
    }

    /**
     * Return this project in YYP format
     */
    public function getYypJson()
    {
        return json_encode([]);
    }

    /**
     * Persist the files and write to disk
     */
    public function save()
    {
        //stub
    }
}