<?php
namespace GMDepMan\Entity;

class ProjectEntity {

    public function __construct()
    {

    }

    public function testValue()
    {
        return 123;
    }

    public function fromJson(string $json)
    {
        var_dump(json_decode($json));
    }
}