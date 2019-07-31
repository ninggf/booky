<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace wula\booky;

use wula\booky\plugin\MarkdownPlugin;
use wula\booky\plugin\SummaryPlugin;
use wula\booky\plugin\YamlPlugin;
use wulaphp\app\App;
use wulaphp\router\Router;

class BookyDoc {
    /**
     * @var string
     */
    private $file;
    /**
     * @var string
     */
    private $url;
    /**
     * @var \wula\booky\plugin\Plugin[]
     */
    private $plugins = [];
    /**
     * @var string
     */
    private $content;
    /**
     * @var \wulaphp\conf\Configuration
     */
    private $opts;

    /**
     * BookyDoc constructor.
     *
     * @param string $file
     * @param string $url
     */
    public function __construct($file, $url) {
        $this->url  = $url;
        $this->file = $file;
    }

    /**
     * 生成文档页面视图.
     *
     * @param bool $checkCache
     *
     * @return \wulaphp\mvc\view\View
     */
    public function render($checkCache = true) {
        if ($checkCache) {
            Router::checkCache();#检测缓存
        }
        $this->content = file_get_contents($this->file);
        $this->opts    = App::config('booky', true);
        $this->plugins = $this->opts->geta('plugins');
        $datas         = [
            'url'        => $this->url,
            'file'       => $this->file,
            'sourceFile' => str_replace([APPROOT, DS], ['', '/'], $this->file),
            'config'     => $this->opts,
            'page'       => [
                'create_time' => @filectime($this->file),
                'update_time' => @filemtime($this->file)
            ]
        ];
        # 内置插件
        array_unshift($this->plugins, new SummaryPlugin());# 目录
        array_unshift($this->plugins, new YamlPlugin()); # yaml 变量
        array_push($this->plugins, new MarkdownPlugin()); # markdown 解析
        # 应用插件
        foreach ($this->plugins as $plugin) {
            $this->content = $plugin->parseMarkdown($this->content, $datas);
        }
        $datas['page']['content'] = $this->content;
        $langDefined              = isset($datas['language']) && $datas['language'] ? $datas['language'] : false;
        if ($langDefined) {
            $theme = $this->opts->get('theme_' . $langDefined);
        } else {
            $theme = $this->opts->get('theme');
        }

        if ($theme != 'default') {
            bind('get_theme', function () use ($theme) {
                return $theme;
            }, 1000, 0);
        }

        if (isset($datas['layout']) && $datas['layout']) {
            $layout = $datas['layout'] . '.tpl';
        } else if ($this->url == 'index.html' || ($langDefined == $this->url)) {
            $layout = 'index.tpl';
        } else {
            $layout = 'page.tpl';
        }

        $datas['config'] = $this->opts->toArray();
        $view            = template($layout, $datas);

        if (isset($datas['expire']) && $datas['expire'] > 0) {
            $view->expire($datas['expire']);
        }
        # html插件.
        bind('before_output_content', function ($content) use ($datas) {
            foreach ($this->plugins as $plugin) {
                $content = $plugin->parseHtml($content, $datas);
            }

            return $content;
        });

        return $view;
    }

    /**
     * 生成文件的URL。
     *
     * @param string $file
     *
     * @return string
     */
    public static function getURL($file) {
        static $base = null;
        if ($base == null) {
            $base = trailingslashit(App::cfg('base@booky'));
        }

        if (preg_match('#^.+\.md$#i', $file)) {
            $url = str_replace([BOOKY_ROOT, DS, 'index.md', '.md'], ['', '/', '', '.html'], $file);
        } else {
            $url = BOOKY_DIR . '/' . $file;
        }

        $url = rtrim($base . $url, '/');

        return $url ? $url : '/';
    }
}