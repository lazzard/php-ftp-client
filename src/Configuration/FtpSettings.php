<?php

namespace Lazzard\FtpClient\Configuration;

/**
 * Class FtpConfiguration
 *
 * @since 1.0
 * @package Lazzard\FtpClient\FtpConfiguration
 * @author EL AMRANI CHAKIR <elamrani.sv.laza@gmail.com>
 */
final class FtpSettings
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

        "root" => [
            "value" => ".",
            "type"  => "string"
        ]
    ];
}