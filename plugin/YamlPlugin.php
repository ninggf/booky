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

class YamlPlugin implements Plugin {
    public function parseMarkdown($content, array &$datas) {
        if (extension_loaded('yaml')) {
            $contents = preg_split('#-{3,}\r?\n\s*\r?\n#', $content);
            if (isset($contents[1])) {
                $ydatas = yaml_parse(trim(trim($contents[0]), '-'));
                if ($ydatas && is_array($ydatas)) {
                    $datas['page'] = $ydatas;
                }
                $content = $contents[1];
            }
        }

        return $content;
    }

    public function parseHtml($content, $datas) {
        return $content;
    }
}