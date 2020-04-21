<?php

return [

    /**
     * Default FTP configuration setup.
     */
    "default" => [

        "timeout"           => 90,
        "passive"           => false,
        "autoSeek"          => true,
        "usePassiveAddress" => false,
        "initialDirectory"  => '/'

    ],

    /**
     * Recommended FTP configuration setup.
     */
    "recommended" => [

        "timeout"           => 300,
        "passive"           => true,
        "autoSeek"          => true,
        "usePassiveAddress" => false,
        "initialDirectory"  => '/',

        "phpLimit"          => [

            "memory"           => -1,
            "maxExecutionTime" => -1,
            "ignoreUserAbort"  => true,
            "loadFtpExtension" => true

        ]

    ],

];