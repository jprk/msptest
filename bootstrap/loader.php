<?php
/**
 * Created by PhpStorm.
 * User: prikryl
 * Date: 18.9.2017
 * Time: 11:50
 */


/* Register autoloader for our app.
   Based on https://gist.github.com/mageekguy/8300961 */

namespace Lectweb;

spl_autoload_register(function($class)
{
    if (stripos($class, __NAMESPACE__) === 0)
    {
        @include(__DIR__ .
            DIRECTORY_SEPARATOR . '..' .
            DIRECTORY_SEPARATOR . 'classes' .
            DIRECTORY_SEPARATOR . $class . '.php');
    }
}
);
// Just put this file in the root directory of your project and include it in your bootstrap file and update
// the namespace.
// File name should be lowercase.
// File name should have .php extension, if you want using an another extension, update line 8 accordingly.
// Search class in "classes" directory, if you want using an another directory, update line 8 accordingly.
