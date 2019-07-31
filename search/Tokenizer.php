<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace wula\booky\search;

use TeamTNT\TNTSearch\Support\TokenizerInterface;

class Tokenizer implements TokenizerInterface {
    public function tokenize($text, $stopwords = []) {
        $text  = str_replace(["\r", "\n", "\t"], ',', $text);
        $text  = mb_strtolower($text);
        $text  = preg_replace('/\\\\u([a-f\d]{4})/', 'h$1z,', trim(json_encode($text), '"'));
        $split = preg_split("/[\s,]+/", $text, -1, PREG_SPLIT_NO_EMPTY);
        $split = array_unique($split);

        return array_diff($split, $stopwords);
    }
}