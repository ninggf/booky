<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace wula\booky {

    use wula\booky\cmd\BookyCommand;
    use wulaphp\app\App;
    use wulaphp\router\Router;

    if (App::cfg('dir@booky', '')) {
        define('BOOKY_DIR', App::cfg('dir@booky', 'doc'));
        define('BOOKY_ROOT', APPROOT . BOOKY_DIR . DS);
    }

    bind('allowed_res_dirs', function ($dirs) {
        $dirs[] = BOOKY_DIR;

        return $dirs;
    });

    bind('router\registerDispatcher', function (Router $router) {
        $router->register(new URLDispatcher(), 500);
    });

    bind('artisan\getCommands', function ($cmds) {
        $cmds['booky'] = new BookyCommand();

        return $cmds;
    });
}

namespace {
    /**
     * 生成文档URL。
     *
     * @param $params
     * @param $compiler
     *
     * @return string
     * @internal
     */
    function smarty_modifiercompiler_docurl($params, $compiler) {
        return "\wula\booky\BookyDoc::getURL({$params [0]})";
    }
}