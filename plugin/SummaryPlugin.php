<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace wula\booky\plugin;

use wula\booky\markdown\Parser;

class SummaryPlugin implements Plugin {
    private $parser;
    private $base;
    private $curl;

    public function __construct() {
        $this->parser                  = new Parser();
        $this->parser->add_list_cls    = true;
        $this->parser->url_filter_func = function ($url) {
            if ($url{0} == '#') {
                return $url{0} . urlencode(substr($url, 1));
            } else if ($url{0} == '/' || preg_match('#^(ht|f)tps?://.+#', $url)) {
                return $url;
            }
            $url = rtrim($this->base . str_replace(['index.md', '.md'], ['', '.html'], $url), '/');
            if ($url == $this->curl) {
                $url = '{$curl$}';
            }

            return $url;
        };
    }

    public function parseMarkdown($content, array &$datas) {
        $urls       = explode('/', $datas['url']);
        $lang       = $urls[0];
        $summary    = BOOKY_ROOT . $lang . DS . '_summary.md';
        $baseURL    = trailingslashit($datas['config']->get('base', ''));
        $this->curl = $curl = $baseURL . $datas['url'];
        if (is_file($summary)) {
            $datas['language'] = $urls[0];
            $this->base        = $baseURL . $urls[0] . '/';
        } else {
            $summary    = BOOKY_ROOT . '_summary.md';
            $this->base = $baseURL;
        }

        if (is_file($summary)) {
            $parser = $this->parser;

            $toc = $parser->transform(file_get_contents($summary));
            $this->parseNP($toc, $datas);
            $datas['summary'] = str_replace('{$curl$}', $curl . '" class="active', $toc);
        }

        return $content;
    }

    public function parseHtml($content, $datas) {
        return $content;
    }

    /**
     * 解析上一页下一页.
     *
     * @param string $toc
     * @param array  $datas
     */
    private function parseNP($toc, &$datas) {
        # 找到当前页URL所在位置
        $cpos = mb_strpos($toc, '{$curl$}');
        if (!$cpos) {
            return;
        }
        #找下一个URL
        $napos1 = mb_strpos($toc, '<a', $cpos);
        if ($napos1 && preg_match('#<a href="([^"]+)"[^>]*>(.+?)</a>#', $toc, $ms, 0, $napos1)) {
            $datas['nextPage'] = ['url' => $ms[1], 'name' => $ms[2]];
        }
        #找上一个URL
        $papos1 = mb_strrpos($toc, '<a', $cpos - mb_strlen($toc) - 10);
        if ($papos1 && preg_match('#<a href="([^"]+)"[^>]*>(.+?)</a>#', $toc, $ms, 0, $papos1)) {
            $datas['prevPage'] = ['url' => $ms[1], 'name' => $ms[2]];
        }
    }
}