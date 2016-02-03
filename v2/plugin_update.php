<?php
/**
 * Omise
 * Copyright (c) 2015 Omise
 * https://www.omise.co
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
 
class plugin_update {
    /**
     * @param array $arrPlugin プラグイン情報の連想配列(dtb_plugin)
     * @return void
     */
    function update($arrPlugin) {
//     	// バージョンの更新
//     	$objQuery = SC_Query_Ex::getSingletonInstance();
//     	$objQuery->begin();
    	
//     	$plugin_id = $arrPlugin['plugin_id'];
//     	$plugin_version = '0.5';  // 新しいバージョン
//     	$objQuery =& SC_Query_Ex::getSingletonInstance();
//     	$sqlval = array();
//     	$sqlval['plugin_version'] = $plugin_version;
//     	$sqlval['update_date'] = 'CURRENT_TIMESTAMP';
//     	$where = "plugin_id = ?";
//     	$objQuery->update("dtb_plugin", $sqlval, $where, array($plugin_id));
//     	$objQuery->commit();
    	
    	// 変更ファイルの上書き
    	SC_Utils_Ex::copyDirectory(dirname(__FILE__).'/', PLUGIN_UPLOAD_REALDIR.$arrPlugin['plugin_code'].'/');
    }
}
