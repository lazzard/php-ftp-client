<?php

return [

    "default" => [

        "timeout"           => 90,
        "passive"           => false,
        "autoSeek"          => true,
        "usePassiveAddress" => false,
        "initialDirectory"  => '.'

    ],

    "recommended" => [

        "timeout"           => 90,
        "passive"           => true,
        "autoSeek"          => true,
        "usePassiveAddress" => false,
        "initialDirectory"  => 'public_html'

    ],

];