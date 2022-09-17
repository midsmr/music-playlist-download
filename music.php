<?php
// Copyright (c) 2022 Jerry<midsmr@qq.com>.
/**
 * @author Jerry midsmr@qq.com
 * @datetime 2022/9/17 13:24
 */

use Midsmr\Music\Music;

require __DIR__.'/vendor/autoload.php';

if (count($argv) < 3) {
    dd('Usage: php music.php [platform] [playlist]');
}

try {
    $music = new Music($argv);

    $music->running();
} catch (Exception $exception) {
    dd($exception->getMessage());
}
