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

    public function desc() {
        return 'initialize booky';
    }

    protected function execute($options) {
        echo 'haha';

        return 0;
    }
}