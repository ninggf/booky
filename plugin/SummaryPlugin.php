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
    private $conf = ['l1cls' => '', 'l2cls' => '', 'l3cls' => '', 'acls' => '', 'ccls' => 'active'];

    public function __construct(array $conf = null) {
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

        if ($conf) {
            $this->conf = array_merge($this->conf, $conf);
        }
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
            $acls    = $this->conf['acls'];
            $ccls    = $this->conf['ccls'];
            $summary = str_replace('{$curl$}', $curl . '" class="' . $acls . ' ' . $ccls, $toc);
            $s       = [];
            $r       = [];
            if ($this->conf['l1cls']) {
                $s[] = 'navi-ul-1';
                $r[] = $this->conf['l1cls'];
            }
            if ($this->conf['l2cls']) {
                $s[] = 'navi-ul-2';
                $r[] = $this->conf['l2cls'];
            }
            if ($this->conf['l3cls']) {
                $s[] = 'navi-ul-3';
                $r[] = $this->conf['l3cls'];
            }

            $summary = str_replace($s, $r, $summary);
            if ($acls) {
                $summary = preg_replace_callback('#<a\shref="[^>]+>#', function ($ms) use ($acls) {
                    if (strpos($ms[0], 'class="')) {
                        return $ms[0];
                    } else {
                        return str_replace('href="', 'class="' . $acls . '" href="', $ms[0]);
                    }
                }, $summary);
            }
            $datas['summary'] = $summary;
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
        $cpos = strpos($toc, '{$curl$}');
        if (!$cpos) {
            return;
        }
        #找下一个URL
        $napos1 = strpos($toc, '<a', $cpos);
        if ($napos1 && preg_match('#<a href="([^"]+)"[^>]*>(.+?)</a>#u', $toc, $ms, 0, $napos1)) {
            $datas['nextPage'] = ['url' => $ms[1], 'name' => $ms[2]];
        }
        #找上一个URL
        $ptoc   = substr($toc, 0, $cpos - 28);
        $papos1 = strrpos($ptoc, '<a');
        if ($papos1 && preg_match('#<a href="([^"]+)"[^>]*>(.+?)</a>#u', $ptoc, $ms, 0, $papos1)) {
            $datas['prevPage'] = ['url' => $ms[1], 'name' => $ms[2]];
        }
    }
}