<?php

/**
 * Your FTP settings for testing.
 */

const HOST     = 'host';
const USERNAME = 'username';
const PASSWORD = 'password';
const TIMEOUT  = 90;
const PORT     = 21;

/**
 * Specifies weather to use an ssl connection for all test unites.
 *
 * Please note that if this option enabled and the connection failed to
 * the server then all depending tests will be failed.
 */

const USESSL = false;
