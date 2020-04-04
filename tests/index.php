<?php

use Lazzard\FtpClient\Exception\FtpClientException;
use Lazzard\FtpClient\FtpClient;

require "../vendor/autoload.php";

try {

    $ftp = new FtpClient();
    $ftp->connect("files.000webhost.com", 21);
    $ftp->login("gs-exercices", "0659630023");

    var_dump(ftp_nlist($ftp->getFtpStream(), "public_html"));

    $ftp->close();

} catch (FtpClientException $ex) {
    echo $ex->getMessage();
}
