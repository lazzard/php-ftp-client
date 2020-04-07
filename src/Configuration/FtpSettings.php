<?php

namespace Lazzard\FtpClient\Configuration;

/**
 * Class FtpConfiguration
 *
 * @since 1.0
 * @package Lazzard\FtpClient\Configuration
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
class FtpSettings
{
    /**
     * FTP client default settings values and their types.
     *
     * @var array
     */
    const settings = [
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
        "root" => [
            "value" => "/",
            "type"  => "string"
        ]
    ];
}