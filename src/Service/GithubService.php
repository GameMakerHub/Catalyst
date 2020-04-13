<?php

namespace Catalyst\Service;

use GuzzleHttp\Exception\ClientException;

class GithubService
{
    private $client;

    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client([
            'base_uri' => 'https://api.github.com/',
            'headers' => [
                'User-Agent' => 'catalyst/1.0',
                'Accept'     => 'application/vnd.github.v3+json',
            ]
        ]);

        $this->fileClient = new \GuzzleHttp\Client([
            'base_uri' => 'https://raw.githubusercontent.com/',
            'headers' => [
                'User-Agent' => 'catalyst/1.0',
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

    public function getDependenciesFor(string $package, string $version):array {

        try {
            $try = $this->fileClient->get($package.'/' . $version . '/catalyst.json');
        } catch (ClientException $e) {
            $try = $this->fileClient->get($package.'/v' . $version . '/catalyst.json');
        }

        $data = json_decode($try->getBody()->getContents());

        $ret = [];
        if (isset($data->require)) {
            foreach ($data->require as $item => $version) {
                $ret[$item->name] = $version;
            }
        }

        return $ret;
    }

    public function downloadZipball(string $url, string $location) {
        if (!@dir(dirname($location))) {
            mkdir(dirname($location), 0777, true);
        }
        $file_path = fopen($location,'w');
        try {
            $response = $this->client->get($url, ['save_to' => $file_path]);
        } catch (\Exception $e) {
            unlink($location);
            throw new \Exception('Error while downloading "' . $url. '" ' . $e->getMessage());
        }

        return ['response_code' => $response->getStatusCode()];
    }

    public function getZipballUrl(string $gitUri, string $version) {
        return sprintf(
            'https://github.com/%s/archive/%s.zip',
            $this->getPackageNameFromUri($gitUri),
            $version
        );
        // Old URL = https://api.github.com/repos/%s/zipball/%s
    }

    public function getPackageNameFromUri($uri) {
        $matches = [];
        preg_match(
            '~git@github\.com:([a-zA-Z0-9-]+\/[a-zA-Z0-9-]+){1}\.git~',
            $uri,
            $matches
        );

        if (count($matches) != 2) {
            throw new \RuntimeException(
                sprintf(
                    'VCS URI "%s" is not supported - must be "%s" format',
                    $uri,
                    'git@github.com:vendor/package.git'
                )
            );
        }
        return strtolower($matches[1]);
    }

    public function getDownloadedPackageFolder(string $zipballUrl):string {
        $cacheKey = sha1($zipballUrl);

        $cacheFolder = implode(DIRECTORY_SEPARATOR, [$GLOBALS['SYMFONY_KERNEL']->getCacheDir(), '/../', 'packages', '']);
        $zipFile = $cacheFolder . $cacheKey. '.zip';
        $location = $cacheFolder . $cacheKey;

        if (!file_exists($zipFile)) {
            echo '    Downloading: "' . $zipballUrl . '" ...' . PHP_EOL;
            $this->downloadZipball($zipballUrl, $zipFile);
        }

        echo '    Extracting ' . $zipFile . PHP_EOL;
        $this->delTree($location);

        $zip = new \ZipArchive();
        if (!$zip->open($zipFile) === TRUE) {
            throw new \Exception('Error while opening ' . $zipFile . ' - try clearing cache.');
        }

        if (!$zip->extractTo($location)) {
            throw new \Exception('Error while extracting ' . $zipFile . ' - try clearing cache.');
        }

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

        // Since this is rather dangerous stuff... Add in some failsafes
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