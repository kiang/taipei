<?php

require_once __DIR__ . '/config.php';

$tmpPath = __DIR__ . '/tmp/' . date('Ymd');
$pagePath = __DIR__ . '/tmp/page';
if (!file_exists($pagePath)) {
    mkdir($pagePath, 0777, true);
}

$folders = $parents = $objects = array();

foreach (glob($tmpPath . '/children/*') AS $objFile) {
    $folderId = pathinfo($objFile)['filename'];
    $folders[$folderId] = json_decode(file_get_contents($objFile), true);
    foreach ($folders[$folderId]['items'] AS $item) {
        $parents[$item['id']] = $folderId;
    }
}

foreach (glob($tmpPath . '/*') AS $objFile) {
    if (is_file($objFile)) {
        $objId = pathinfo($objFile)['filename'];
        $objects[$objId] = json_decode(file_get_contents($objFile), true);
        $objects[$objId]['path'] = getParents($objId);
    }
}

foreach ($objects AS $obj) {
    if ($obj['title'] === 'Thumbs.db') {
        continue;
    }
    $html = '<meta charset="utf-8" />';
    foreach ($obj['path'] AS $parentId) {
        if (isset($objects[$parentId])) {
            $html .= "<a href=\"{$parentId}.html\">{$objects[$parentId]['title']}</a> &gt; ";
        } else {
            $html .= "<a href=\"index.html\">首頁</a> &gt; ";
        }
    }
    $html .= $obj['title'];
    
    if (isset($folders[$obj['id']])) {
        $html .= '<hr />資料夾：';
        $files = array();
        foreach ($folders[$obj['id']]['items'] AS $item) {
            if (!isset($folders[$item['id']])) {
                if ($objects[$item['id']]['title'] !== 'Thumbs.db') {
                    $files[] = $item['id'];
                }
            } else {
                $html .= "<a href=\"{$item['id']}.html\">{$objects[$item['id']]['title']}</a> | ";
            }
        }
        $html .= '<hr />檔案：';
        foreach ($files AS $fileId) {
            $html .= "<a href=\"{$fileId}.html\">{$objects[$fileId]['title']}</a> | ";
        }
    } else {
        $html .= '<hr />' . $obj['title'];
    }
    file_put_contents("{$pagePath}/{$obj['id']}.html", $html);
}

$html = '<meta charset="utf-8" />';
$html .= '<hr />資料夾：';
$files = array();
foreach ($folders[$baseFolderId]['items'] AS $item) {
    if (!isset($folders[$item['id']])) {
        if ($objects[$item['id']]['title'] !== 'Thumbs.db') {
            $files[] = $item['id'];
        }
    } else {
        $html .= "<a href=\"{$item['id']}.html\">{$objects[$item['id']]['title']}</a> | ";
    }
}
$html .= '<hr />檔案：';
foreach ($files AS $fileId) {
    $html .= "<a href=\"{$fileId}.html\">{$objects[$fileId]['title']}</a> | ";
}
file_put_contents("{$pagePath}/index.html", $html);

function getParents($id, $path = array()) {
    global $parents;
    if (isset($parents[$id])) {
        return getParents($parents[$id], array_merge(array($parents[$id]), $path));
    } else {
        return $path;
    }
}
