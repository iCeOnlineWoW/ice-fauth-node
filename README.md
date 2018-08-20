# FAuth node

FAuth (Frozen authenticator) implementation in PHP language. This is the core of shared authentication across all iCe Online projects.

This project is still under heavy development, and will undergo major structure changes as new requirements manifests. The basic idea is to have single authentication system for all subsystems, like websites, forums, game, etc.

## Requirements

* PHP 7.0+
* any http daemon supporting URL rewrite
* database backend of your choice (MySQL/MariaDB, PostgreSQL, ...)
