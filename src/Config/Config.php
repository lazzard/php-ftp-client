<?php

namespace Lazzard\FtpClient\Config;

/**
 * Class Config
 *
 * @since 1.0
 * @package Lazzard\FtpClient\FtpConfiguration
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
final class Config
{
    /**
     * FTP client default settings values and their types.
     *
     * @var array
     */
    const SETTINGS = [

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
}