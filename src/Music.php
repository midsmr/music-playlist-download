<?php
// Copyright (c) 2022 Jerry<midsmr@qq.com>.

/**
 * @author Jerry midsmr@qq.com
 * @datetime 2022/9/17 13:24
 */

namespace Midsmr\Music;

use InvalidArgumentException;
use Metowolf\Meting;
use Yurun\Util\HttpRequest;

class Music
{
    private Platform $platform;

    private Meting $meting;

    private array $playlist;

    public function __construct(private readonly array $arg)
    {
        $this->setPlatform($this->arg[1]);
        $this->meting = new Meting($this->getPlatform()->value);
    }

    public function running(): void
    {
        $this->playlist = json_decode($this->meting
            ->format(true)
            ->cookie($this->getCookie())
            ->playlist($this->getPlaylistID()), true);
        foreach ($this->playlist as $key => $value) {
            $this->download($value);
            dump('剩余' . (count($this->playlist) - ($key + 1)) . '首待下载');
            sleep(1);
        }
    }


    private function download(array $songInfo): void
    {
        $filename = $songInfo['name'] . '-' . $songInfo['artist'][0] . '.mp3';
        $savedPath = $this->getDownloadDir() . '/' . $filename;
        if (is_file($savedPath)) {
            dump("{$filename} 已存在");
            return;
        }
        dump("Downloading {$filename}");
        $http = $this->getHttpInstance();
        $result = $http->download($savedPath, $this->getPlayURL($songInfo['id']));
        if (is_file($result->getSavedFileName())) {
            dump("Download {$filename} successfully!");
            dump("Total time {$result->getTotalTime()}");
        } else {
            dump("Download {$filename} failed!");
        }
    }

    public function getDownloadDir(): string
    {
        $base = __DIR__ . '/..';
        is_dir($base . '/download') || mkdir($base . '/download');
        $dir = $base . '/download';
        is_dir($dir . '/' . $this->getPlatform()->value) || mkdir($dir . '/' . $this->getPlatform()->value);
        $dir = $dir . '/' . $this->getPlatform()->value;
        is_dir($dir . '/' . date('Y-m-d')) || mkdir($dir . '/' . date('Y-m-d'));
        return $dir . '/' . date('Y-m-d');
    }

    public function getHttpInstance(): HttpRequest
    {
        return new HttpRequest();
    }

    public function getPlayURL(string $id): ?string
    {
        return json_decode($this->meting->url($id), true)['url'] ?? null;
    }

    public function getPlaylistID(): string
    {
        preg_match('/\d+/', $this->arg[2], $matches);

        return $matches[0] ?? throw new InvalidArgumentException('Invalid playlist ID');
    }

    public function getCookie(): string
    {
        return file_get_contents(__DIR__ . '/../' . $this->getPlatform()->value . '.cookie');
    }


    /**
     * @return Platform
     */
    public function getPlatform(): Platform
    {
        return $this->platform;
    }

    /**
     * @param string $platform
     */
    public function setPlatform(string $platform): void
    {
        $enum = Platform::tryFrom($platform);
        if ($enum === null) {
            throw new InvalidArgumentException('Invalid platform');
        }
        $this->platform = $enum;
    }

}