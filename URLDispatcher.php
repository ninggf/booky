<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace booky;

use wulaphp\router\IURLDispatcher;

class URLDispatcher implements IURLDispatcher {
    function dispatch($url, $router, $parsedInfo) {
        return $url;
    }
}