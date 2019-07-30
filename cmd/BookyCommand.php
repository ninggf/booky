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

class BookyCommand extends ArtisanCommand {
    public function cmd() {
        return 'booky';
    }

    public function desc() {
        return 'booky tool';
    }

    protected function execute($options) {
        $this->output('this is book tool');

        return 0;
    }

    protected function subCommands() {
        $this->subCmds['init']  = new InitCommand('booky');
        $this->subCmds['index'] = new IndexCommand('booky');
    }
}