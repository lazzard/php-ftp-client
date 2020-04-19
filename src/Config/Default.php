<?php

return [

    /**
     * FTP client default settings values and their types.
     *
     * @var array
     */
    "timeout" => [
        "value" => 90,
        "type"  => "integer"
    ],

    "passive" => [
        "value" => false,
        "type"  => "boolean"
    ],

    "autoSeek" => [
        "value" => true,
        "type"  => "boolean"
    ],

    "usePassiveAddress" => [
        "value" => false,
        "type"  => "boolean"
    ],

    "initialDirectory" => [
        "value" => ".",
        "type"  => "string"
    ]

];