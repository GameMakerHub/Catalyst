<?php

namespace Catalyst\Interfaces;

interface SaveableEntityInterface {
    public function getFileContents() : string;
    public function getFilePath() : string;
}