<?php

// namespace App\Core;

// class Autoloader
// {
//     public static function register(): void
//     {
//         // echo " hello autoloader ";
//         spl_autoload_register(function (string $class): void {
//             $baseDir
//             $prefix = 'app\\';



//             $len = strlen($prefix);
//             if (strncmp($prefix, $class, $len) !== 0) {
//                 return;
//             }

//             $relativeClass = substr($class, $len);

//             // Converts App\Controllers\UserController to app/Controllers/UserController.php
//             $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

//             if (file_exists($file)) {
//                 require_once $file;
//             }
//         });
//     }
// }

namespace App\Core;

class Autoloader
{
    public static function register(): void
    {
        spl_autoload_register(function (string $class) {
            $prefix = 'App\\';
            $baseDir = __DIR__ . '/../';

            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                return;
            }

            $relativeClass = substr($class, $len);
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

            if (file_exists($file)) {
                require_once $file;
            }
        });
    }
}

