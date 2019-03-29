<?php

namespace GMDepMan\Service;

use Assert\Assertion;
use GMDepMan\Entity\YoYoProjectEntity;
use Symfony\Component\Console\Output\Output;

class GithubService
{
    private $client;

    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client([
            'base_uri' => 'https://api.github.com/',
            'headers' => [
                'User-Agent' => 'gmdepman/1.0',
                'Accept'     => 'application/vnd.github.v3+json',
            ]
        ]);
    }

    public function getTags(string $repository):array {

        $data = json_decode($this->client->get('repos/'.$repository.'/tags')->getBody()->getContents());
        $ret = [];
        foreach ($data as $item) {
            if (stripos($item->name, 'v', 0) !== false) {
                $item->name = substr($item->name, 1, strlen($item->name)-1);
            }
            $ret[$item->name] = $item->zipball_url;
        }
        return $ret;
    }

    public function downloadZipball(string $url, string $location) {
        $file_path = fopen($location,'w');
        $response = $this->client->get($url, ['save_to' => $file_path]);
        return ['response_code'=>$response->getStatusCode()];
    }

    public function getDownloadedPackageFolder(string $zipballUrl):string {
        $cacheKey = sha1($zipballUrl);

        $cacheFolder = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'gmdepman' . DIRECTORY_SEPARATOR;
        $zipFile = $cacheFolder . $cacheKey. '.zip';
        $location = $cacheFolder . $cacheKey;

        if (!file_exists($zipFile)) {
            echo 'Downloading: <fg=yellow>' . $zipballUrl . '</> to '.$zipFile.'...' . PHP_EOL;
            $this->downloadZipball($zipballUrl, $zipFile);
        }

        echo 'Extracting ' . $zipFile . PHP_EOL;

        $this->delTree($location);

        $zip = new \ZipArchive();
        if (!$zip->open($zipFile) === TRUE) {
            throw new \Exception('Error while extracting ' . $zipFile . ' - try clearing cache.');
        }

        $zip->extractTo($location);
        $zip->close();

        $folders = glob($location . DIRECTORY_SEPARATOR . '*');
        $firstFolder = $folders[0];

        rename($firstFolder, $location . '_TMP');
        $this->delTree($location);
        rename($location . '_TMP', $location);

        return $location;
    }

    public function delTree($dir) {
        if (!is_dir($dir) && !is_file($dir)) {
            return false;
        }
        if (stripos($dir, sys_get_temp_dir()) === false) {
            throw new \Exception('Not deleting ' . $dir . ' because temp dir is not in it');
        }
        if (strlen($dir) < 30) {
            throw new \Exception('Not deleting ' . $dir . ' because length is < 30');
        }
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }
}