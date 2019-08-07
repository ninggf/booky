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

class VideoPlugin implements Plugin {
    public function parseMarkdown($content, array &$datas) {
        $content = preg_replace_callback('#@\[\s*([1-9]\d*)[\sx,\-\*]+([1-9]\d*)\s*\]\(([^\)]+)\)#', function ($ms) {
            return "<div class=\"video\"><iframe height=\"{$ms[2]}\" width=\"{$ms[1]}\" src=\"{$ms[3]}\" frameborder=\"0\" allowFullScreen=\"true\"></iframe></div>";
        }, $content);

        return $content;
    }

    public function parseHtml($content, $datas) {
        return $content;
    }
}