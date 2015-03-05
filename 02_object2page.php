<?php

require_once __DIR__ . '/config.php';

$tmpPath = __DIR__ . '/tmp/' . date('Ymd');

exec("cp -Ru " . __DIR__ . "/skel/* {$targetFolder}/");

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
    $breadcrumbs = '<ol class="breadcrumb">';
    foreach ($obj['path'] AS $parentId) {
        if (isset($objects[$parentId])) {
            $breadcrumbs .= "<li><a href=\"{$parentId}.html\">{$objects[$parentId]['title']}</a></li>";
        } else {
            $breadcrumbs .= "<li><a href=\"index.html\">首頁</a></li>";
        }
    }
    $breadcrumbs .= '<li class="active">' . $obj['title'] . '</li></ol>';
    $content = '';
    if (isset($folders[$obj['id']])) {
        $files = array();
        foreach ($folders[$obj['id']]['items'] AS $item) {
            if (!isset($folders[$item['id']])) {
                if ($objects[$item['id']]['title'] !== 'Thumbs.db') {
                    $files[] = $item['id'];
                }
            } else {
                $content .= "<a class=\"btn btn-app bg-aqua\" href=\"{$item['id']}.html\"><i class=\"fa fa-folder\"></i> {$objects[$item['id']]['title']}</a>";
            }
        }
        foreach ($files AS $fileId) {
            $content .= "<a class=\"btn btn-app\" href=\"{$fileId}.html\"><i class=\"fa fa-file\"></i> {$objects[$fileId]['title']}</a>";
        }
    } else {
        $content .= '<div class="clearfix"></div><a href="https://drive.google.com/open?id=' . $obj['id'] . '" target="_blank" class="btn btn-primary">下載</a>';
    }
    file_put_contents("{$targetFolder}/{$obj['id']}.html", strtr(file_get_contents(__DIR__ . '/skel/empty.html'), array(
        '{{title}}' => $obj['title'],
        '{{breadcrumbs}}' => $breadcrumbs,
        '{{content}}' => $content,
    )));
}

$content = '';
$files = array();
foreach ($folders[$baseFolderId]['items'] AS $item) {
    if (!isset($folders[$item['id']])) {
        if ($objects[$item['id']]['title'] !== 'Thumbs.db') {
            $files[] = $item['id'];
        }
    } else {
        $content .= "<a class=\"btn btn-app bg-aqua\" href=\"{$item['id']}.html\"><i class=\"fa fa-folder\"></i> {$objects[$item['id']]['title']}</a>";
    }
}
foreach ($files AS $fileId) {
    $content .= "<a class=\"btn btn-app\" href=\"{$fileId}.html\"><i class=\"fa fa-file\"></i> {$objects[$fileId]['title']}</a>";
}
file_put_contents("{$targetFolder}/index.html", strtr(file_get_contents(__DIR__ . '/skel/empty.html'), array(
    '{{title}}' => '首頁',
    '{{breadcrumbs}}' => '',
    '{{content}}' => $content,
)));

function getParents($id, $path = array()) {
    global $parents;
    if (isset($parents[$id])) {
        return getParents($parents[$id], array_merge(array($parents[$id]), $path));
    } else {
        return $path;
    }
}
