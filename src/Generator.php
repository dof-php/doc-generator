<?php

declare(strict_types=1);

namespace DOF\DOC;

use DOF\DOF;
use DOF\INI;
use DOF\ErrManager;
use DOF\Convention;
use DOF\Util\FS;
use DOF\DDD\EntityManager;
use DOF\DDD\ModelManager;
use DOF\HTTP\PortFormatter;
use DOF\HTTP\WrapInManager;
use DOF\DOC\UI;

final class Generator
{
    /**
     * Build docs with given $ui and save to $save
     *
     * @param string $ui: The docs ui to use
     * @param string $save: The docs path to save
     * @param string $lang: The doc template language
     */
    public static function build(string $ui, string $save, string $lang = 'zh-CN')
    {
        UI::get($ui)
            ->setTemplate(FS::path(\dirname(__DIR__), Convention::DIR_TEMPLATE))
            ->setLanguage($lang)
            ->setOutput($save)
            ->setAssets(INI::systemGet('docs', 'assets', []))
            ->setHTTPPorts(PortFormatter::formatDocs())
            ->setWrapins(WrapinManager::getData())
            ->setModels(ModelManager::getData())
            ->setEntities(EntityManager::getData())
            ->setErrors(ErrManager::getData())
            ->build();
    }
}
