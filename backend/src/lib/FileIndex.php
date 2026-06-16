<?php

class FileIndex
{
    private $dir;
    private $cacheDir;
    private $cacheFile;
    private $dirMtime;

    public function __construct($dir, $cacheDir)
    {
        $this->dir = rtrim($dir, '/\\');
        $this->cacheDir = rtrim($cacheDir, '/\\');
        $this->cacheFile = $this->cacheDir . '/file_index_cache.json';
        $this->dirMtime = is_dir($this->dir) ? filemtime($this->dir) : 0;
    }

    public function getIndex($prefix = '')
    {
        $cache = $this->loadCache();
        if ($cache === null || $cache['dir_mtime'] < $this->dirMtime) {
            $cache = $this->scanAndCache();
        }
        $files = $cache['files'];
        if ($prefix !== '') {
            $files = array_values(array_filter($files, function ($f) use ($prefix) {
                return strpos($f['name'], $prefix) === 0;
            }));
        }
        return $files;
    }

    public function getPage($page = 1, $perPage = 10, $prefix = '')
    {
        $all = $this->getIndex($prefix);
        $total = count($all);
        $page = max(1, (int)$page);
        $perPage = max(1, (int)$perPage);
        $offset = ($page - 1) * $perPage;
        return [
            'items'      => array_slice($all, $offset, $perPage),
            'total'      => $total,
            'page'       => $page,
            'perPage'    => $perPage,
            'totalPages' => (int)ceil($total / $perPage),
        ];
    }

    private function scanAndCache()
    {
        $files = [];
        if (is_dir($this->dir)) {
            foreach (scandir($this->dir) as $entry) {
                if ($entry === '.' || $entry === '..' || $entry === '.gitkeep') {
                    continue;
                }
                $full = $this->dir . DIRECTORY_SEPARATOR . $entry;
                if (is_file($full)) {
                    $files[] = [
                        'name'  => $entry,
                        'size'  => filesize($full),
                        'mtime' => filemtime($full),
                    ];
                }
            }
        }
        $cache = ['dir_mtime' => $this->dirMtime, 'files' => $files];
        $this->saveCache($cache);
        return $cache;
    }

    private function loadCache()
    {
        if (!file_exists($this->cacheFile)) {
            return null;
        }
        $json = file_get_contents($this->cacheFile);
        $data = json_decode($json, true);
        if (!is_array($data) || !isset($data['dir_mtime'], $data['files'])) {
            return null;
        }
        return $data;
    }

    private function saveCache(array $cache)
    {
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
        file_put_contents(
            $this->cacheFile,
            json_encode($cache, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    public static function renderItem(array $file)
    {
        return '<a href="uploads/' . htmlspecialchars($file['name'])
             . '" class="list-group-item list-group-item-action py-2" target="_blank">'
             . '<div class="d-flex w-100 justify-content-between align-items-center">'
             . '<span class="text-dark"><i class="bi bi-file-earmark-pdf text-danger me-2"></i>'
             . htmlspecialchars($file['name']) . '</span>'
             . '<small class="text-muted">下载</small>'
             . '</div></a>';
    }

    public static function renderEmpty()
    {
        return '<div class="text-center text-muted py-3">暂无政策文件</div>';
    }
}
