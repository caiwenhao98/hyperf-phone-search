<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

require_once dirname(dirname(__FILE__)) . '/vendor/autoload.php';
//设置PHP最大内存，组件大概会占用200M内存
ini_set('memory_limit', '1024M');
$phone = '18718861563';
$search = new \Hyperf\PhoneSearch\PhoneSearch();
echo $search->get($phone);
//广东|深圳|518000|0755|440300|移动