<?php

declare(strict_types=1);

namespace DOF\DOC;

use DOF\Convention;
use DOF\Util\FS;
use DOF\Util\Arr;
use DOF\Util\Format;
use DOF\DOC\UIInterface;
use DOF\DOC\Exceptor\UIExceptor;

final class UI
{
    const LIST = [
        'gitbook' => \DOF\DOC\UI\GitBook::class,
    ];

    public static function get(string $ui) : UIInterface
    {
        $_ui = self::LIST[\strtolower($ui)] ?? null;
        if (! $_ui) {
            throw new UIExceptor('DOC_UI_NOT_SUPPORT', \compact('ui'));
        }

        return new $_ui;
    }

    public static function support(string &$ui) : bool
    {
        return isset(self::LIST[$ui = \strtolower($ui)]);
    }
}
