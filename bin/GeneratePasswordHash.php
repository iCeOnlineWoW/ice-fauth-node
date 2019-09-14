<?php
/*
 * Script for creating a new user with "dummy" rights
 * The administrator is then required to edit the database to grant himself required rights,
 * or use some GUI tool to manage that
 */

if ($argc != 2)
{
    echo "Usage:\r\n";
    echo "    GeneratePasswordHash.php <password>\r\n";
    exit(1);
}

// init autoloaders (composer and our own)
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/Loader.php';

echo AuthModel::generateHash($argv[1])."\r\n";
