<?php


namespace Lazzard\FtpClient;


class TimingHelper
{
    private static $start;

    public static function start() {
        self::$start = microtime(true);
    }

    public static function segs() {
        return microtime(true) - self::$start;
    }

    public static function time() {
        $segs = self::segs();
        $days = floor($segs / 86400);
        $segs -= $days * 86400;
        $hours = floor($segs / 3600);
        $segs -= $hours * 3600;
        $mins = floor($segs / 60);
        $segs -= $mins * 60;
        $microsegs = ($segs - floor($segs)) * 1000;
        $segs = floor($segs);

        return
            (empty($days) ? "" : $days . "d ") .
            (empty($hours) ? "" : $hours . "h ") .
            (empty($mins) ? "" : $mins . "m ") .
            $segs . "s " .
            $microsegs . "ms";
    }
}