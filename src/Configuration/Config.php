<?php

namespace Lazzard\FtpClient\Configuration;

/**
 * This constant specifies that the value of particular php directive in
 * your php.ini file will not be overrated, use this constant if you
 * want to use default directives values in your php.ini file.
 */
define(__NAMESPACE__ . '\NOT_CHANGE', 'not_overwrite');

/**
 * Use this constant with the appropriate directive
 * to indicate an unlimited value.
 */
define(__NAMESPACE__. '\UNLIMITED', 'unlimited_value');

return [

    /**
     * Php resources limits.
     */
    "phpLimit"          => [

        "maxExecutionTime" => UNLIMITED,
        "ignoreUserAbort"  => false

    ],

    /**
     * Default FTP configuration setup.
     */
    "default" => [

        /**
         * The active mode is the default mode when connecting to
         * the ftp server for the first time.
         */
        "passive"           => false,

        /**
         * Ftp runtime options default values was mentioned in
         * the php manual.
         *
         * @link https://www.php.net/manual/en/function.ftp-set-option.php
         */
        "timeout"           => 90,
        "autoSeek"          => true,
        "usePassiveAddress" => true,

        /**
         * Ftp default root folder.
         */
        "initialDirectory"  => '/',

    ],

];
