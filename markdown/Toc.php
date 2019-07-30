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

class Toc implements \ArrayAccess, \IteratorAggregate {
    /**
     * @var \wula\booky\markdown\Toc
     */
    private $parent = null;
    /**
     * @var \wula\booky\markdown\Toc[]
     */
    private $chidren = [];
    private $level   = 0;
    private $name;
    private $data;

    /**
     * 添加节点.
     *
     * @param string $name  节点名称
     * @param int    $level 级别
     * @param array  $data  数据
     *
     * @return  \wula\booky\markdown\Toc
     */
    public function add($name, $level, array $data) {
        $data['name']  = $name;
        $data['level'] = $level;
        if ($level - 1 == $this->level || !$this->parent) {# 子或第一个
            $toc             = new Toc();
            $toc->parent     = $this;
            $toc->level      = $level;
            $toc->name       = $name;
            $toc->data       = $data;
            $this->chidren[] = $toc;

            return $toc;
        } else if ($level == $this->level) {# 邻
            $toc                     = new Toc();
            $toc->parent             = $this->parent;
            $toc->level              = $level;
            $toc->name               = $name;
            $toc->data               = $data;
            $this->parent->chidren[] = $toc;

            return $toc;
        } else if ($level < $this->level) {# 回归上级
            $p = $this->parent;
            if (!$p) {
                $p = $this;
            }

            return $p->add($name, $level, $data);
        }

        return $this; # 忽略跳级
    }

    /**
     * 获取Toc的头.
     *
     * @return \wula\booky\markdown\Toc
     */
    public function head() {
        if (!$this->parent) {
            return $this;
        }
        $p = $this->parent;
        while (true) {
            if (!$p->parent) {
                break;
            }
        }

        return $p;
    }

    /**
     * 生成默认的TOC HTML片断.
     *
     * @param int $level
     *
     * @return string
     */
    public function render($level = 3) {
        $head   = $this->head();
        $chunks = ['<ul class="toc">'];
        /**@var \wula\booky\markdown\Toc $h */
        foreach ($head as $h) {
            $h->_render($chunks, 1, $level);
        }
        $chunks[] = '</ul>';

        return implode('', $chunks);
    }

    public function getIterator() {
        return new \ArrayIterator($this->chidren);
    }

    public function offsetExists($offset) {
        return isset($this->data[ $offset ]);
    }

    public function offsetGet($offset) {
        if ($offset == 'children') {
            return count($this->chidren);
        }

        return $this->data[ $offset ];
    }

    public function offsetSet($offset, $value) {

    }

    public function offsetUnset($offset) {

    }

    private function _render(&$chunks, $level, $stop) {
        if ($level > $stop) {
            return;
        }
        $chunks[] = "<li><a id=\"toc-{$this['id']}\" href=\"#{$this['id']}\">{$this->name}</a>";
        if ($this->chidren) {
            $chunks[] = '<ul class="toc-' . $level . '">';
            foreach ($this->chidren as $toc) {
                $toc->_render($chunks, $level + 1, $stop);
            }
            $chunks[] = '</ul>';
        }
        $chunks[] = '</li>';
    }
}
