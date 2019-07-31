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

class MarkdownPlugin implements Plugin {
    public function parseMarkdown($content, array &$datas) {
        $curl  = $datas['url'];
        $curls = explode('/', $curl);
        if (preg_match('/^.+\.html$/', $curls[ count($curls) - 1 ])) {
            array_pop($curls);
        }
        $parser = new Parser();
        $base   = trailingslashit($datas['config']->get('base', ''));

        if ($curls) {
            $dir = implode('/', $curls) . '/';
        } else {
            $dir = '';
        }

        # 转换URL
        $parser->url_filter_func = function ($url) use ($base, $dir) {
            if ($url{0} == '#') {
                return $url{0} . urlencode(substr($url, 1));
            } else if ($url{0} == '/' || preg_match('#^(ht|f)tps?://.+#', $url)) {
                return $url;
            }
            if (preg_match('#^.+\.md$#i', $url)) {
                if (preg_match('#^.*\.\./index\.md$#i', $url)) {
                    return $base . $dir . str_ireplace('index.md', '', $url);
                } else {
                    return rtrim($base . $dir . str_ireplace(['index.md', '.md'], ['', '.html'], $url), '/');
                }
            } else {
                return $base . BOOKY_DIR . '/' . $dir . $url;
            }
        };

        # 添加header id
        $parser->header_id_func = function ($header) {
            return urlencode(preg_replace('/(\s+|,+)/', '-', $header));
        };

        # 代码段
        $parser->code_attr_on_pre        = false;
        $parser->code_block_content_func = function ($code, $lang) use ($datas) {
            if ($lang == 'html' && isset($datas['page']['demo']) && $datas['page']['demo']) {
                $codes = htmlentities($code);
                $code  = <<<HTML
</code></pre><div class="demo-wraper">
<div class="demo">$code</div>
<div class="code">$codes</div>
</div><pre><code>
HTML;
            }

            return $code;
        };
        $content                         = $parser->transform($content);
        $datas['page']['toc']            = $parser->toc();
        $datas['page']['tocStr']         = $parser->toc()->render();
        if (isset($datas['page']['demo']) && $datas['page']['demo']) {
            return str_replace('<pre><code class="html"></code></pre>', '', $content);
        } else {
            return $content;
        }
    }

    public function parseHtml($content, $datas) {
        return $content;
    }
}