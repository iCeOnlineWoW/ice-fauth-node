<?php

// use custom class loader for lazy class loading
function class_loader($class_name)
{
    $subdirs = array('/Handlers/', '/Models/', '/Misc/', '/Enums/');

    foreach ($subdirs as $subdir)
    {
        if (file_exists(__DIR__.$subdir.$class_name.'.php'))
        {
            include __DIR__.$subdir.$class_name.'.php';
            break;
        }
    }
}
// register class loader
spl_autoload_register('class_loader');
