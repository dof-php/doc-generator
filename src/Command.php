<?php

declare(strict_types=1);

namespace DOF\DOC;

use DOF\DOF;
use DOF\Convention;
use DOF\Util\IS;
use DOF\Util\FS;
use DOF\Util\JSON;
use DOF\Util\Format;
use DOF\CLI\Color;
use DOF\DOC\Generator;

class Command
{
    /**
     * @CMD(doc.gen)
     * @Alias(doc.build)
     * @Desc(Generate and build docs with static site of DOF project)
     * @Option(html){notes=Build final documentation HTML pages or not}
     * @Option(save){notes=The path to save the build result}
     * @Option(ui){notes=The UI name used to render docs}
     */
    public function buildDocsAll($console)
    {
        $ui = $console->getOption('ui', 'gitbook');
        if ($console->hasOption('save')) {
            $save = $console->getOption('save');
        } else {
            $save = DOF::path(Convention::DIR_RUNTIME, \join('-', ['doc-generator', $ui]));
        }

        if ((empty($save)) || (false === FS::mkdir($save = Format::path($save)))) {
            $console->error('Empty or unwritable save path', \compact('save'));
        }

        $status = $console->task("Generating documentation of DOF project to `{$save}`", function () use ($ui, $save) {
            Generator::build($ui, $save);
        });

        if (false !== $status) {
            $html = $console->confirmOption('html', true);
            switch ($ui) {
                case 'gitbook':
                    $cli = "sh {$save}/src/build.sh";
                    $script = $console->render($cli, Color::TIPS);
                    if ($html && \function_exists('shell_exec')) {
                        $console->info('Building GitBook static site, it may take a few minutes ...');
                        $console->line($result = \shell_exec($cli));
                        if (\is_null($result)) {
                            $console->fail("Unable to build GitBook static site, run `{$script}` to build it manually");
                        } else {
                            $console->ok("GitBook static site at: `{$save}/dist`");
                        }
                    } else {
                        $html && $console->warn('`shell_exec()` has been disabled, unable to build GitBook static site automatically');
                        $console->info("Run `{$script}` to build GitBook static site manually");
                    }
                    break;
            }
        }
    }
}
