<?php
/*
 * Script for creating a new user with "dummy" rights
 * The administrator is then required to edit the database to grant himself required rights,
 * or use some GUI tool to manage that
 */

if ($argc != 5)
{
    echo "Usage:\r\n";
    echo "    AddUser.php <username> <email> <password> <service1,service2,..>\r\n";
    exit(1);
}

// init autoloaders (composer and our own)
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/Loader.php';

// load config and initialize dibi library
$config = require __DIR__ . '/../src/Config.php';
$dibi = new Dibi\Connection($config['settings']['dibi']);

// dibi should throw and exception on auth fail, and that should be good enough for us
if (!$dibi || !$dibi->isConnected())
    die("Cannot connect to database\r\n");

// we need user and auth models
$userModel = new UserModel($dibi);
$authModel = new AuthModel($dibi);

if (!$userModel || !$authModel)
    die("Cannot instantiate database models\r\n");

// prepare auth fields
$username = trim($argv[1]);
$email = trim($argv[2]);
$password = $argv[3];
$services = explode(',', trim($argv[4]));

// require at least some services
if (count($services) === 0)
    die("No services defined\r\n");

// disallow creating user that already exists
if ($userModel->getUserByUsername($username))
    die("User $username already exists\r\n");

// also disallow email duplication
if ($userModel->getUserByEmail($email))
    die("User with email $email already exists\r\n");

// add user, create password
$users_id = $userModel->addUser($username, $email);
if ($users_id > 0)
{
    $authModel->addPasswordAuth($users_id, $password, $services);

    echo "User $username with email $email created successfully!\r\n";
}
else
{
    echo "User has NOT been created! Username or email is probably invalid.\r\n";
}
