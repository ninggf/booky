<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace wula\booky\markdown;

use Michelf\MarkdownExtra;

/**
 * 1. 添加TOC功能
 * 2. 为List添加class支持
 *
 * @package wula\booky\markdown
 */
class Parser extends MarkdownExtra {
    public    $add_list_cls = false;
    protected $tocs;
    private   $head;

    public function __construct() {
        parent::__construct();
        $this->tocs = new Toc();
        $this->head = $this->tocs;
    }

    /**
     * 获取Toc.
     *
     * @return \wula\booky\markdown\Toc
     */
    public function toc() {
        return $this->head;
    }

    /**
     * Callback for atx headers
     *
     * @param array $matches
     *
     * @return string
     */
    protected function _doHeaders_callback_atx($matches) {
        $level = intval(strlen($matches[1]));

        $defaultId = is_callable($this->header_id_func) ? call_user_func($this->header_id_func, $matches[2]) : null;
        $attr      = $this->doExtraAttributes("h$level", $dummy =& $matches[3], $defaultId);
        $block     = "<h$level$attr>" . $this->runSpanGamut($matches[2]) . "</h$level>";
        # 添加toc
        $this->tocs = $this->tocs->add($matches[2], $level, ['id' => $defaultId]);

        return "\n" . $this->hashBlock($block) . "\n\n";
    }

    /**
     * Callback for setext headers
     *
     * @param array $matches
     *
     * @return string
     */
    protected function _doHeaders_callback_setext($matches) {
        if ($matches[3] == '-' && preg_match('{^- }', $matches[1])) {
            return $matches[0];
        }

        $level     = $matches[3]{0} == '=' ? 1 : 2;
        $defaultId = is_callable($this->header_id_func) ? call_user_func($this->header_id_func, $matches[1]) : null;
        $attr      = $this->doExtraAttributes("h$level", $dummy =& $matches[2], $defaultId);
        $block     = "<h$level$attr>" . $this->runSpanGamut($matches[1]) . "</h$level>";
        # 添加toc
        $this->tocs = $this->tocs->add($matches[2], $level, ['id' => $defaultId]);

        return "\n" . $this->hashBlock($block) . "\n\n";
    }

    /**
     * List parsing callback
     *
     * @param array $matches
     *
     * @return string
     */
    protected function _doLists_callback($matches) {
        // Re-usable patterns to match list item bullets and number markers:
        $marker_ul_re       = '[*+-]';
        $marker_ol_re       = '\d+[\.]';
        $marker_ol_start_re = '[0-9]+';

        $list      = $matches[1];
        $list_type = preg_match("/$marker_ul_re/", $matches[4]) ? "ul" : "ol";

        $marker_any_re = ($list_type == "ul" ? $marker_ul_re : $marker_ol_re);

        $list   .= "\n";
        $result = $this->processListItems($list, $marker_any_re);

        $ol_start = 1;
        if ($this->enhanced_ordered_list) {
            // Get the start number for ordered list.
            if ($list_type == 'ol') {
                $ol_start_array = [];
                $ol_start_check = preg_match("/$marker_ol_start_re/", $matches[4], $ol_start_array);
                if ($ol_start_check) {
                    $ol_start = $ol_start_array[0];
                }
            }
        }
        $listCls = $this->add_list_cls ? 'class="navi-ul-' . ($this->list_level + 1) . '"' : '';
        if ($ol_start > 1 && $list_type == 'ol') {
            $result = $this->hashBlock("<$list_type $listCls start=\"$ol_start\">\n" . $result . "</$list_type>");
        } else {
            $result = $this->hashBlock("<$list_type $listCls>\n" . $result . "</$list_type>");
        }

        return "\n" . $result . "\n\n";
    }
}