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

interface Plugin {
    /**
     * 解析原始的MD
     *
     * @param string $content markdown string
     * @param array  $datas
     *
     * @return string
     */
    public function parseMarkdown($content, array &$datas);

    /**
     * 解析生成的html
     *
     * @param string $content
     * @param array  $datas
     *
     * @return string
     */
    public function parseHtml($content, $datas);
}