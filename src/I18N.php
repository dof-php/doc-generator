<?php

declare(strict_types=1);

namespace DOF\DOC;

use DOF\Convention;
use DOF\Util\FS;

class I18N extends \DOF\Util\I18N
{
    public static function lang(string $lang) : string
    {
        return FS::path(\dirname(__DIR__), Convention::DIR_LANG, $lang.'.php');
    }
}
