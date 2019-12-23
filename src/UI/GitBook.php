<?php

declare(strict_types=1);

namespace DOF\DOC\UI;

use Throwable;
use DOF\Util\IS;
use DOF\Util\FS;
use DOF\Util\Arr;
use DOF\Util\Str;
use DOF\Util\JSON;
use DOF\Util\Format;
use DOF\Util\Collection;
use DOF\DOC\UI;
use DOF\DOC\I18N;
use DOF\DOC\UIInterface;
use DOF\DOC\Exceptor\GitBookExceptor;
use DOF\HTTP\PortFormatter;

class GitBook implements UIInterface
{
    const README  = 'README.md';
    const SUMMARY = 'SUMMARY.md';
    const BOOKJSON = 'book.json';
    const VERINDEX = 'index.html';

    const BUILDER = 'build.sh';

    const DOC_PORT_HTTP = 'doc.port.http.md';
    const DOC_WRAPIN = 'doc.wrapin.md';
    const DOC_MODEL  = 'doc.model.md';
    const DOC_ERROR  = 'doc.errors.md';

    const OUTPUT_WRAPIN = '_wrapin';
    const OUTPUT_MODEL  = '_model';
    const OUTPUT_ENTITY = '_entity';

    /** @var string: UI language */
    private $language = 'zh-CN';

    /** @var string: Doc menus tree */
    private $menuTree = '';

    /** @var int: Doc menus depth */
    private $menuDepth = 0;

    /** @var string: SUMMARY.md path */
    private $summary;

    /** @var string: README.md path */
    private $readme;

    /** @var string: book.json path */
    private $bookjson;

    /** @var array: Doc versions selects list */
    private $selects = [];

    /** @var array: Doc versions list */
    private $versions = [];

    /** @var array: HTTP Ports */
    private $ports = [
        'http' => [],
    ];

    /** @var array: Wrapins */
    private $wrapins = [];

    /** @var array: Data models */
    private $models = [];

    /** @var array: Entities */
    private $entities = [];

    /** @var array: User defined errors */
    private $errors = [];

    /** @var array: Doc appendixes */
    private $appendixes = [
        'domain' => [],
        'global' => [],
    ];

    /** @var string: Templates directory */
    private $template;

    /** @var string: Output directory */
    private $output;

    /** @var string: Build all-versions-in-one site */
    private $builder;

    private $doc = [
        'port.http' => null, // API doc template path
        'wrapin' => null, // Wrapin doc template path
        'model' => null, // Data model doc template path
        'entity' => null, // Data model doc template path
        'error' => null, // Errors doc template path
    ];

    /** @var array: Assets for docs */
    private $assets = [];

    public function prepare()
    {
        $template = FS::path($this->template, 'gitbook', 'lang', $this->language);

        if (! \is_file($this->summary = FS::path($template, self::SUMMARY))) {
            throw new GitBookExceptor('GITBOOK_SUMMARY_NOT_FOUND', [$this->summary]);
        }
        if (! \is_file($this->readme = FS::path($template, self::README))) {
            throw new GitBookExceptor('GITBOOK_README_NOT_FOUND', [$this->readme]);
        }
        if (! \is_file($this->bookjson = FS::path($template, self::BOOKJSON))) {
            throw new GitBookExceptor('GITBOOK_JSON_FILE_NOT_FOUND', [$this->bookjson]);
        }
        if (! \is_file($this->verindex = FS::path($template, self::VERINDEX))) {
            throw new GitBookExceptor('DOC_VERSION_SELECT_INDEX_NOT_FOUND', [$this->verindex]);
        }
        if (! \is_file($this->builder = FS::path($template, self::BUILDER))) {
            throw new GitBookExceptor('GITBOOK_SITE_BUILDER_NOT_FOUND', [$this->builder]);
        }
        if (! \is_file($this->doc['port.http'] = FS::path($template, self::DOC_PORT_HTTP))) {
            throw new GitBookExceptor('API_DOC_TEMPLATE_NOT_FOUND', [$this->doc['port.http'] ?? null]);
        }
        if (! \is_file($this->doc['wrapin'] = FS::path($template, self::DOC_WRAPIN))) {
            throw new GitBookExceptor('WRAPIN_DOC_TEMPLATE_NOT_FOUND', [$this->doc['wrapin'] ?? null]);
        }
        if (! \is_file($this->doc['model'] = FS::path($template, self::DOC_MODEL))) {
            throw new GitBookExceptor('DATA_MODEL_DOC_TEMPLATE_NOT_FOUND', [$this->doc['model'] ?? null]);
        }
        if (! \is_file($this->doc['error'] = FS::path($template, self::DOC_ERROR))) {
            throw new GitBookExceptor('ERROR_DOC_TEMPLATE_NOT_FOUND', [$this->doc['error'] ?? null]);
        }
    }

    public function build()
    {
        $this->prepare();
        $this->buildAssets();
        $this->buildModel(false);
        $this->buildEntity(false);
        $this->buildWrapin(false);
        $this->buildHTTPPorts(false);
        $this->publish();
    }

    public function buildModel(bool $standalone)
    {
        if (! $this->models) {
            return;
        }
        if ($standalone) {
            $this->prepare();
        }

        $path = $this->getOutputSrc(self::OUTPUT_MODEL);
        FS::rmdir($path, ['node_modules']);
        $path = FS::mkdir($path);

        $this->selects[] = [
            'value' => '/'.self::OUTPUT_MODEL,
            'text'  => I18N::get('DATA_MODEL', $this->language),
            'version' => self::OUTPUT_MODEL,
        ];

        foreach ($this->models as $ns => $model) {
            $key = PortFormatter::formatDocNamespace($ns);
            $this->appendMenuTree($key, null, $key);
            $this->render(
                $this->doc['model'] ?? null,
                FS::path($path, "{$key}.md"),
                ['model' => PortFormatter::formatDocModel($ns)]
            );
        }

        $readme = FS::unlink($path, self::README);
        $this->render($this->readme, $readme, ['version' => I18N::get('DATA_MODEL', $this->language)]);

        $summary = FS::unlink($path, self::SUMMARY);
        $this->render($this->summary, $summary, ['tree' => $this->menuTree, 'readme' => true]);

        $this->menuTree  = '';
        $this->menuDepth = 0;
        $this->versions[] = self::OUTPUT_MODEL;

        if ($standalone) {
            $this->publish();
        }
    }

    public function buildEntity(bool $standalone)
    {
        if (! $this->entities) {
            return;
        }
        if ($standalone) {
            $this->prepare();
        }

        $path = $this->getOutputSrc(self::OUTPUT_ENTITY);
        FS::rmdir($path, ['node_modules']);
        $path = FS::mkdir($path);

        $this->selects[] = [
            'value' => '/'.self::OUTPUT_ENTITY,
            'text'  => I18N::get('DDD_ENTITY', $this->language),
            'version' => self::OUTPUT_ENTITY,
        ];

        foreach ($this->entities as $ns => $entity) {
            $key = PortFormatter::formatDocNamespace($ns);
            $this->appendMenuTree($key, null, $key);
            $this->render(
                $this->doc['model'] ?? null,
                FS::path($path, "{$key}.md"),
                ['model' => PortFormatter::formatDocModel($ns)]
            );
        }

        $readme = FS::unlink($path, self::README);
        $this->render($this->readme, $readme, ['version' => I18N::get('DDD_ENTITY', $this->language)]);

        $summary = FS::unlink($path, self::SUMMARY);
        $this->render($this->summary, $summary, ['tree' => $this->menuTree, 'readme' => true]);

        $this->menuTree  = '';
        $this->menuDepth = 0;
        $this->versions[] = self::OUTPUT_ENTITY;

        if ($standalone) {
            $this->publish();
        }
    }

    public function buildWrapin(bool $standalone)
    {
        if (! $this->wrapins) {
            return;
        }
        if ($standalone) {
            $this->prepare();
        }

        $path = $this->getOutputSrc(self::OUTPUT_WRAPIN);
        FS::rmdir($path, ['node_modules']);
        $path = FS::mkdir($path);

        $this->selects[] = [
            'value'   => '/'.self::OUTPUT_WRAPIN,
            'text'    => I18N::get('WRAPIN', $this->language),
            'version' => self::OUTPUT_WRAPIN,
        ];

        foreach ($this->wrapins as $ns => $wrapin) {
            $key = PortFormatter::formatDocNamespace($ns);
            $this->appendMenuTree($key, null, $key);
            $this->render($this->doc['wrapin'] ?? null, FS::path($path, "{$key}.md"), PortFormatter::formatWrapinDocData($wrapin, $key));
        }

        $readme = FS::unlink($path, self::README);
        $this->render($this->readme, $readme, ['version' => I18N::get('WRAPIN', $this->language)]);
        
        $summary = FS::unlink($path, self::SUMMARY);
        $this->render($this->summary, $summary, ['tree' => $this->menuTree, 'readme' => true]);

        $this->menuTree  = '';
        $this->menuDepth = 0;
        $this->versions[] = self::OUTPUT_WRAPIN;

        if ($standalone) {
            $this->publish();
        }
    }

    public function buildHTTPPorts(bool $standalone)
    {
        if ($standalone) {
            $this->prepare();
        }

        foreach ($this->ports['http'] ?? [] as $version => $domain) {
            $ver = $this->getOutputSrc($version);
            FS::rmdir($ver, ['node_modules']);
            $path = FS::mkdir($ver);

            $this->selects[] = [
                'version' => $version,
                'value' => "/{$version}",
                'text'  => "HTTP API {$version}",
            ];

            // $this->menuDepth = 0;
            // $this->appendMenuTree($version);
            foreach ($domain as $key => $data) {
                if (! ($title = $data['title'] ?? null)) {
                    throw new GitBookExceptor('HTTP_PORT_DOC_DOMAIN_TITLE_MISSING');
                }
                $_domain = FS::mkdir($ver, $key);
                // $this->menuDepth = 1;
                $this->appendMenuTree($title, $key);
                $group = $data['group'] ?? [];
                $list  = $data['list']  ?? [];
                $this->genHttpGroup($group, $_domain, $key);
                // $this->menuDepth = 2;
                $this->menuDepth = 1;
                $this->genHttpList($list, $_domain, $key);
                // $this->menuDepth = 1;
                $this->menuDepth = 0;
            }
            // $this->menuDepth = 0;

            $readme = FS::unlink($ver, self::README);
            $this->render($this->readme, $readme, ['version' => " HTTP API {$version}"]);
            $summary = FS::unlink($ver, self::SUMMARY);
            $error = FS::unlink($ver, self::DOC_ERROR);
            $this->render($this->doc['error'] ?? null, FS::path($ver, 'errors.md'), ['errors' => $this->errors]);
            
            $appendixesDomain = $this->appendixes['domain'] ?? [];
            $_appendixes = FS::mkdir($ver, '_appendixes');

            $_appendixesDomain = [];
            foreach ($appendixesDomain as $__domain) {
                foreach ($__domain as $appendix) {
                    if (! ($path = ($appendix['path'] ?? false))) {
                        throw new GitBookExceptor('MISSING_APPENDIX_DOC_FILE');
                    }
                    if (! \is_file($path)) {
                        throw new GitBookExceptor('APPENDIX_DOC_FILE_NOT_EXISTS', \compact('path'));
                    }
                    if (! ($key = ($appendix['key'] ?? false))) {
                        throw new GitBookExceptor('MISSING_APPENDIX_DOC_DOMAIN_KEY');
                    }
                    $_key = FS::mkdir($_appendixes, $key);
                    $href = \basename($path);
                    FS::copy($path, FS::path($_key, $href));

                    $appendix['href'] = \join(DIRECTORY_SEPARATOR, ['_appendixes', $key, $href]);

                    $_appendixesDomain[] = $appendix;
                }
            }

            unset($appendix);

            $appendixesGlobal = $this->appendixes['global'] ?? [];
            foreach ($appendixesGlobal as &$appendix) {
                if (! ($path = ($appendix['path'] ?? false))) {
                    throw new GitBookExceptor('MISSING_APPENDIX_DOC_FILE');
                }
                if (! \is_file($path)) {
                    throw new GitBookExceptor('APPENDIX_DOC_FILE_NOT_EXISTS', \compact('path'));
                }
                if (! ($key = ($appendix['key'] ?? false))) {
                    throw new GitBookExceptor('MISSING_APPENDIX_DOC_DOMAIN_KEY');
                }

                $_key = FS::mkdir($_appendixes, $key);
                $href = \basename($path);
                FS::copy($path, FS::path($_key, $href));

                $appendix['href'] = \join(DIRECTORY_SEPARATOR, ['_appendixes', $key, $href]);
            }

            $this->render($this->summary, $summary, [
                'tree' => $this->menuTree,
                'appendixes' => ['domain' => $_appendixesDomain, 'global' => $appendixesGlobal],
                'errors' => true,
            ]);

            $this->menuTree  = '';
            $this->menuDepth = 0;
        }


        $this->versions = \array_merge($this->versions, \array_keys($this->ports['http'] ?? []));

        if ($standalone) {
            $this->publish();
        }
    }

    private function buildAssets()
    {
        foreach ($this->assets as $asset) {
            if (! \is_file($asset)) {
                throw new GitBookExceptor('INVALID_DOC_ASSETS_PATH', \compact('path', 'asset'));
            }

            $arr = Str::arr($asset, DIRECTORY_SEPARATOR);
            unset($arr[0]);
            $_asset = $this->getOutputSrc('__assets', $arr);

            FS::rmdir(\dirname($_asset));
            FS::mkdir(\dirname($_asset));

            FS::copy($path, $_asset);
        }
    }

    private function publish()
    {
        $builder = FS::unlink($this->getOutputSrc(self::BUILDER));
        $this->render($this->builder, $builder, [
            'versions' => $this->versions,
            'docDir' => $this->getOutputSrc(),
            'siteDir' => $this->getOutputDist(),
        ]);

        $verindex = FS::unlink($this->getOutputSrc(self::VERINDEX));

        $this->render($this->verindex, $verindex, ['default' => Arr::last($this->versions)]);

        // Foramt book.json versions plugin configs with default version display logic
        foreach ($this->selects as list('version' => $_version)) {
            $_selects  = $this->selects;
            $_versions = \array_column($this->selects, 'version');
            $idx = \array_search($_version, $_versions);
            if (false !== $idx) {
                $default = $_selects[$idx] ?? [];
                unset($_selects[$idx]);
                \array_unshift($_selects, $default);
            }
            $bookjson = FS::unlink($this->getOutputSrc($_version, self::BOOKJSON));

            $this->render($this->bookjson, $bookjson, ['options' => JSON::encode($_selects)]);
        }
    }

    private function appendMenuTree(string $title, string $folder = null, string $filename = null)
    {
        if (\is_null($filename)) {
            $path = '';   // Avoid chapters toggle not working
        } else {
            $filename .= '.md';
            $path = $folder ? \join('/', [$folder, $filename]) : $filename;
        }

        $this->menuTree .= \sprintf(
            '%s* [%s](%s)%s',
            \str_repeat("\t", $this->menuDepth),
            $title,
            $path,
            PHP_EOL
        );
    }

    /**
     * Generate HTTP docs list menus
     *
     * @param array $list: List memus data
     * @param string $dir: List docs directory
     * @param string $path: The markdown doc link path (relative)
     */
    private function genHttpList(array $list, string $dir, string $path)
    {
        foreach ($list as $doc) {
            if (IS::confirm($doc['annotations']['NODOC'] ?? false)) {
                continue;
            }
            $api = new Collection([]);
            $api->route = $doc['annotations']['ROUTE'] ?? null;
            if (! $api->route) {
                throw new GitBookExceptor('MISSING_HTTP_PORT_DOC_URLPATH');
            }
            $api->verbs = $doc['annotations']['VERB'] ?? [];
            if (! $api->verbs) {
                throw new GitBookExceptor('MISSING_HTTP_PORT_DOC_VERBS');
            }
            $api->title = $doc['annotations']['TITLE'] ?? null;
            if (! $api->title) {
                throw new GitBookExceptor('MISSING_HTTP_PORT_DOC_TITLE');
            }
            $api->subtitle = $doc['annotations']['SUBTITLE'] ?? null;

            if (($file = ($doc['file'] ?? null)) && \is_file($file)) {
                $api->updatedAt = Format::timestamp(\filemtime($file));
            } else {
                $api->updatedAt = Format::timestamp();
            }
            $api->class = $doc['class'] ?? null;
            $api->method = $doc['method'] ?? null;
            $api->auth = $doc['annotations']['AUTH'] ?? null;
            $api->version = $doc['annotations']['VERSION'] ?? null;
            $api->author = $doc['annotations']['AUTHOR'] ?? null;
            $api->status = $doc['annotations']['STATUS'] ?? null;
            $api->suffixes = $doc['annotations']['SUFFIX'] ?? [];
            $api->remarks = $doc['annotations']['REMARK'] ?? [];
            $api->sorting = PortFormatter::formatDocSorting($doc);
            $api->model = PortFormatter::formatDocModel($doc['annotations']['MODEL'] ?? null);
            $api->wraperr = PortFormatter::formatDocWraperr($doc['annotations']['WRAPERR'] ?? null);
            $api->wrapout = PortFormatter::formatDocWrapout($doc);
            $api->request = PortFormatter::formatRequest($doc);
            $api->response = PortFormatter::formatResponse($doc);
            $api->arguments = PortFormatter::formatArguments($doc);

            $_doc = \md5(\join(':', [\join('_', $api->verbs), $api->route]));
            $this->appendMenuTree($api->title, $path, $_doc);
            $this->render($this->doc['port.http'] ?? null, FS::unlink($dir, "{$_doc}.md"), \compact('api'));
        }
    }

    /**
     * Generate HTTP docs group menus
     *
     * @param array $group: Group memus data
     * @param string $dir: Group docs files save directory (absolute)
     * @param string $path: The markdown doc link path (relative)
     */
    private function genHttpGroup(array $group, string $dir, string $path)
    {
        foreach ($group as $name => $_group) {
            ++$this->menuDepth;
            $_key = \join('/', [$path, $name]);
            $_dir = FS::mkdir($dir, $name);
            if ($__group = ($_group['group'] ?? [])) {
                $this->genHttpGroup($__group, $_dir, $_key);
            }
            if ($list = ($_group['list'] ?? [])) {
                // just render group with port list
                $this->appendMenuTree($_group['title'] ?? '?', $_key);

                ++$this->menuDepth;
                $this->genHttpList($list, $_dir, $_key);
                --$this->menuDepth;
            }
            --$this->menuDepth;
        }
    }

    public function render(string $template, string $save, array $data = [])
    {
        FS::render($data, $template, $save, function ($th) use ($template) {
            throw new GitBookExceptor('GITBOOK_RENDER_ERROR', \compact('template'), $th);
        });
    }

    public function getOutputSrc(...$items) : string
    {
        return FS::path($this->output, 'src', $items);
    }

    public function getOutputDist(...$items) : string
    {
        return FS::path($this->output, 'dist', $items);
    }

    public function setAssets(array $assets)
    {
        $this->assets = $assets;

        return $this;
    }

    public function setLanguage(string $language)
    {
        $this->language = $language;
    
        return $this;
    }

    public function setHTTPPorts(array $ports)
    {
        $this->ports['http'] = $ports;
    
        return $this;
    }

    public function setWrapins(array $wrapins)
    {
        $this->wrapins = $wrapins;
    
        return $this;
    }

    public function setModels(array $models)
    {
        $this->models = $models;
    
        return $this;
    }

    public function setEntities(array $entities)
    {
        $this->entities = $entities;
    
        return $this;
    }

    public function setErrors(array $errors)
    {
        $this->errors = $errors;
    
        return $this;
    }

    public function setAppendixes(array $appendixes)
    {
        $this->appendixes = $appendixes;
    
        return $this;
    }

    public function setTemplate(string $template)
    {
        $this->template = $template;
    
        return $this;
    }

    public function setOutput(string $output)
    {
        $this->output = $output;
    
        return $this;
    }
}
