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

$extraBreadcrumbs = array();
$sidebar = '<ul class="sidebar-menu">';
foreach ($tree AS $item) {
    $sidebar .= '<li class="treeview"><a href="#"><span>' . $item['title'] . '</span><i class="fa fa-angle-left pull-right"></i></a><ul class="treeview-menu">';
    foreach ($item['links'] AS $link) {
        $sidebar .= '<li><a href="' . $link['key'] . '.html">' . $link['title'] . '</a></li>';

        foreach ($link['folders'] AS $linkFolderId) {
            $extraBreadcrumbs[$linkFolderId] = "<li><a href=\"index.html\">首頁</a></li>";
            $extraBreadcrumbs[$linkFolderId] .= "<li><a href=\"{$link['key']}.html\">{$item['title']} - {$link['title']}</a></li>";
        }
    }
    $sidebar .= '</ul></li>';
}
$sidebar .= '</ul>';

foreach ($objects AS $obj) {
    if ($obj['title'] === 'Thumbs.db') {
        continue;
    }
    $breadcrumbs = '<ol class="breadcrumb">';
    if (isset($extraBreadcrumbs[$obj['id']])) {
        $breadcrumbs .= $extraBreadcrumbs[$obj['id']];
    }
    foreach ($obj['path'] AS $parentId) {
        if (isset($extraBreadcrumbs[$parentId])) {
            $breadcrumbs .= $extraBreadcrumbs[$parentId];
        }
        if (isset($objects[$parentId])) {
            $breadcrumbs .= "<li><a href=\"{$parentId}.html\">{$objects[$parentId]['title']}</a></li>";
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
        '{{id}}' => $obj['id'],
        '{{sidebar}}' => $sidebar,
    )));
}

foreach ($tree AS $item) {
    $prefix = $item['title'] . ' - ';
    foreach ($item['links'] AS $link) {
        $content = '';
        foreach ($link['folders'] AS $linkFolderId) {
            $content .= "<a class=\"btn btn-app bg-aqua\" href=\"{$linkFolderId}.html\"><i class=\"fa fa-folder\"></i> {$objects[$linkFolderId]['title']}</a>";
        }

        $breadcrumbs = '<ol class="breadcrumb">';
        $breadcrumbs .= "<li><a href=\"index.html\">首頁</a></li>";
        $breadcrumbs .= '<li class="active">' . $prefix . $link['title'] . '</li></ol>';

        file_put_contents("{$targetFolder}/{$link['key']}.html", strtr(file_get_contents(__DIR__ . '/skel/empty.html'), array(
            '{{title}}' => $prefix . $link['title'],
            '{{breadcrumbs}}' => $breadcrumbs,
            '{{content}}' => $content,
            '{{id}}' => $link['key'],
            '{{sidebar}}' => $sidebar,
        )));
    }
}


$content = '';
foreach ($tree AS $item) {
    $prefix = $item['title'] . ' - ';
    foreach ($item['links'] AS $link) {
        $content .= "<a class=\"btn btn-app bg-aqua\" href=\"{$link['key']}.html\"><i class=\"fa fa-folder\"></i> {$prefix}{$link['title']}</a>";
    }
}
file_put_contents("{$targetFolder}/index.html", strtr(file_get_contents(__DIR__ . '/skel/empty.html'), array(
    '{{title}}' => '首頁',
    '{{breadcrumbs}}' => '',
    '{{content}}' => $content,
    '{{id}}' => 'index',
    '{{sidebar}}' => $sidebar,
)));

function getParents($id, $path = array()) {
    global $parents;
    if (isset($parents[$id])) {
        return getParents($parents[$id], array_merge(array($parents[$id]), $path));
    } else {
        return $path;
    }
}
