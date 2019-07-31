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

use PDO;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use TeamTNT\TNTSearch\Exceptions\IndexNotFoundException;
use TeamTNT\TNTSearch\TNTSearch;
use wula\booky\plugin\YamlPlugin;
use wula\booky\search\Indexer;
use wulaphp\artisan\ArtisanCommand;

class IndexCommand extends ArtisanCommand {
    public function cmd() {
        return 'index';
    }

    public function desc() {
        return 'generate page indexes for search';
    }

    public function argDesc() {
        return '[build|search]';
    }

    protected function execute($options) {
        $op       = $this->opt(1, 'build');
        $searcher = self::getSearcher();

        if ($op == 'search') {
            $key = $this->opt(2);
            if (!$key) {
                $this->output('give me something to search!');

                return 1;
            }
            try {
                $searcher->selectIndex('search.db');
                $rest = $searcher->search($key, 10);
                $this->output('Text: ' . $key);
                $this->output('Hits: ' . $rest['hits']);
                $this->output('Time: ' . $rest['execution_time']);
                $this->output('IDS : ' . implode(',', $rest['ids']));
            } catch (IndexNotFoundException $e) {
                $this->error($e->getMessage());
            }
        } else {
            $indexer = new Indexer();
            $indexer->loadConfig($searcher->config);
            $indexer->createIndex('search.db');
            $pdo = $indexer->getPdo();
            $indexer->setStopWords(['a', 'the', 'and', 'or', 'but', 'an', 'is', 'am', 'are', 'was', 'were']);
            $this->scan($pdo);
            $this->build($pdo, $indexer);
        }

        return 0;
    }

    private function scan(\PDO $pdo) {
        $atime   = time();
        $path    = APPROOT . BOOKY_DIR;
        $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(BOOKY_ROOT), RecursiveIteratorIterator::SELF_FIRST);
        $stmt    = $pdo->prepare('INSERT INTO filelist (file,atime,mtime) VALUES (:f,:a,:m)');
        foreach ($objects as $name => $object) {
            $name = str_replace($path . '/', '', $name);
            $ext  = strtolower($object->getExtension());
            if ($ext == 'md' && $object->getFileName() != '_summary.md') {
                $mtime = $object->getMTime();
                $stmt->bindValue(':f', $name);
                $stmt->bindValue(':a', $atime, PDO::PARAM_INT);
                $stmt->bindValue(':m', $mtime, PDO::PARAM_INT);
                $stmt->execute();
            }
        }
    }

    protected function build(\PDO $pdo, Indexer $indexer) {
        $result = $pdo->query('SELECT id,file FROM filelist');
        if ($result) {
            $yamlPlugin = new YamlPlugin();
            $counter    = 0;
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('update filelist set title=:t where id=:id');
            while (($row = $result->fetch(PDO::FETCH_ASSOC))) {
                $datas   = [];
                $id      = $row['id'];
                $file    = $row['file'];
                $content = @file_get_contents(BOOKY_ROOT . $file);
                # 内容为空
                if (!$content) {
                    $counter++;
                } else {
                    $content = $yamlPlugin->parseMarkdown($content, $datas);
                    # 内容为空且不需要需要
                    if (!$content || !isset($datas['page']['index']) || !$datas['page']['index']) {
                        $counter++;
                    } else {
                        $counter++;
                        $doc       = [];
                        $doc['id'] = $id;
                        if ($datas['page']['title']) {
                            $doc['title'] = $datas['page']['title'];
                            $stmt->bindValue(':t', $doc['title']);
                            $stmt->bindValue(':id', $id);
                            $stmt->execute();
                        }
                        $doc['article'] = $datas['page']['index'];
                        $indexer->insert($doc);
                    }
                }

                if ($counter % 10 == 0) {
                    $this->output("Processed $counter rows");
                }

                if ($counter % 100 == 0) {
                    $pdo->commit();
                    $pdo->beginTransaction();
                    $this->output("Commited");
                }
            }
            $pdo->commit();
            $this->output("Total rows $counter");
        } else {
            $this->output("Total rows 0");
        }
    }

    /**
     * 获取一个Searcher.
     *
     * @return bool|\TeamTNT\TNTSearch\TNTSearch
     */
    public static function getSearcher() {
        static $searcher = false;
        if ($searcher === false) {
            $searcher = new TNTSearch();
            $searcher->loadConfig([
                'driver'    => 'filesystem',
                'tokenizer' => '\wula\booky\search\Tokenizer',
                'storage'   => APPROOT . BOOKY_DIR
            ]);
        }

        return $searcher;
    }
}