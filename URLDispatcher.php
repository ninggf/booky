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

use wulaphp\router\IURLDispatcher;

class URLDispatcher implements IURLDispatcher {

    public function dispatch($url, $router, $parsedInfo) {
        if ($url{0} == '_') {#不处理_开头的URL
            return null;
        }
        $url  = rtrim($url, '/');
        $file = preg_replace('#(.+\.)html$#', '\1', $url, 1, $cnt);
        if ($cnt) {
            $file = BOOKY_ROOT . $file . 'md';
        } else {
            $file = BOOKY_ROOT . $file . '/index.md';
        }
        if (!is_file($file)) {
            return null;
        }
        # 创建文档
        $doc = new BookyDoc($file, $url);

        return $doc->render();
    }
}