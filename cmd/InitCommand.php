<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace wula\booky\cmd;

use wulaphp\artisan\ArtisanCommand;

class InitCommand extends ArtisanCommand {
    public function cmd() {
        return 'init';
    }

    public function argDesc() {
        return '[dirname]';
    }

    public function desc() {
        return 'initialize booky';
    }

    protected function execute($options) {
        $dir  = $this->opt(1, 'doc');
        $rdir = APPROOT . $dir . DS;
        if (file_exists($rdir)) {
            $this->error('destination path \'' . $dir . '\' already exists and is not an empty directory.');

            return 1;
        }
        if (!@mkdir($rdir, 0755)) {
            $this->error("cannot create path '$dir', permission denied!");

            return 1;
        }
        ## 配置
        $conf = <<<CONF
<?php
return [
    'dir' => '$dir'
];
CONF;
        if (!is_file(CONFIG_PATH . 'booky_config.php')) {
            @file_put_contents(CONFIG_PATH . 'booky_config.php', $conf);
        }
        ## 配置结束

        @file_put_contents($rdir . '_summary.md', "- [首页](index.md)\n");
        $date  = date('Y-m-d H:i:s');
        $index = <<<IND
---
title: 首页
date: $date
---

# 首页

IND;
        @file_put_contents($rdir . 'index.md', $index);
        $this->output('done');

        return 0;
    }
}