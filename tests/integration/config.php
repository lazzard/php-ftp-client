<?php

/**
 * Your FTP settings for testing.
 */

const HOST        = "host";
const USERNAME    = "username";
const PASSWORD    = "password";
const TIMEOUT     = 90; // is recommended to have a high value of timeout to avoid connection interruptions
const PORT        = 21;
const PASSIVE     = true; // is recommended to run the tests on passive mode connection
const INITIAL_DIR = '.'; // make sure to have write and read permissions for this directory
