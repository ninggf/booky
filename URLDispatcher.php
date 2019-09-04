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

use wula\booky\cmd\IndexCommand;
use wulaphp\router\IURLDispatcher;
use wulaphp\router\Router;
use wulaphp\router\UrlParsedInfo;

class URLDispatcher implements IURLDispatcher {

    public function dispatch(string $url, Router $router, UrlParsedInfo $parsedInfo) {
        if (preg_match('/_summary.html$/', $url)) {#不处理_开头的URL
            return null;
        }
        if ($url == 'search-doc.do') {
            $q = rqst('q');
            if ($q) {
                $searcher = IndexCommand::getSearcher();
                $pdo      = new \PDO('sqlite:' . BOOKY_ROOT . 'search.db');
                try {
                    $searcher->selectIndex('search.db');
                    $rest      = $searcher->search($q, 50);
                    $rest['q'] = $q;
                    $ids       = $rest['ids'];
                    unset($rest['ids']);
                    if ($ids) {
                        $rows = $pdo->query('select id,file,title,cate from filelist where id in (' . implode(',', $ids) . ')');
                        if ($rows) {
                            $_pages = [];
                            while (($row = $rows->fetch(\PDO::FETCH_ASSOC))) {
                                $url                  = BookyDoc::getURL($row['file']);
                                $_pages[ $row['id'] ] = [
                                    'url'   => $url,
                                    'title' => $row['title'],
                                    'cate'  => $row['cate']
                                ];
                            }
                            $pages = [];
                            foreach ($ids as $id) {
                                $pages[] = $_pages[ $id ];
                            }
                            $rest['pages'] = $pages;
                        } else {
                            $rest['pages'] = [];
                        }
                    } else {
                        $rest['pages'] = [];
                    }

                    return $rest;
                } catch (\Exception $e) {

                }
            }

            return ['pages' => [], 'hits' => 0, 'execution_time' => 0, 'q' => ''];
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