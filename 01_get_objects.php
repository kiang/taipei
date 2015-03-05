<?php

require_once 'Google/autoload.php';
require_once __DIR__ . '/config.php';
set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . '/google-api-php-client/src');

session_start();

$tmpPath = __DIR__ . '/tmp/' . date('Ymd');
if (!file_exists($tmpPath)) {
    mkdir($tmpPath, 0777, true);
}



$url = "http://{$_SERVER['SERVER_NAME']}{$_SERVER['PHP_SELF']}";

$client = new Google_Client();
$client->setClientId($client_id);
$client->setClientSecret($client_secret);
$client->setRedirectUri($url);
$client->addScope("https://www.googleapis.com/auth/drive");

if (isset($_GET['code'])) {
    $client->authenticate($_GET['code']);
    $_SESSION['token'] = $client->getAccessToken();
    header('Location: ' . filter_var($url, FILTER_SANITIZE_URL));
}

$service = new Google_Service_Drive($client);

if (isset($_SESSION['token']) && $_SESSION['token']) {
    $client->setAccessToken($_SESSION['token']);
    if ($client->isAccessTokenExpired()) {
        unset($_SESSION['token']);
    }
} else {
    $authUrl = $client->createAuthUrl();
    header('Location: ' . $authUrl);
}

/* * **********************************************
  If we're signed in then lets try to upload our
  file. For larger files, see fileupload.php.
 * ********************************************** */
if ($client->getAccessToken()) {
    echo '<meta charset="utf-8" />';
    echo '<pre>';
    // This is uploading a file directly, with no metadata associated.
    $service = new Google_Service_Drive($client);
    printFilesInFolder($service, $baseFolderId);
    echo '</pre>';
}

/**
 * Print files belonging to a folder.
 *
 * @param Google_DriveService $service Drive API service instance.
 * @param String $folderId ID of the folder to print files from.
 */
function printFilesInFolder($service, $folderId, $parent = '') {
    global $tmpPath;
    $pageToken = NULL;

    do {
        try {
            $parameters = array();
            if ($pageToken) {
                $parameters['pageToken'] = $pageToken;
            }

            if (!file_exists($tmpPath . '/children')) {
                mkdir($tmpPath . '/children', 0777, true);
            }
            $childrenCache = $tmpPath . '/children/' . $folderId;
            if (!file_exists($childrenCache)) {
                $node = $service->children->listChildren($folderId, $parameters);
                $node->items = $node->getItems();
                file_put_contents($childrenCache, json_encode($node));
            }
            $children = json_decode(file_get_contents($childrenCache), true);

            foreach ($children['items'] as $item) {
                $objCache = $tmpPath . '/' . $item['id'];
                if (!file_exists($objCache)) {
                    file_put_contents($objCache, json_encode($service->files->get($item['id'])));
                }
                $obj = json_decode(file_get_contents($objCache), true);
                if ($obj['title'] === 'Thumbs.db') {
                    continue;
                }
                $nextParent = '';
                if (!empty($parent)) {
                    $nextParent = $parent . '/';
                }
                if ($obj['mimeType'] === 'application/vnd.google-apps.folder') {
                    printFilesInFolder($service, $item['id'], "{$nextParent}{$obj['title']}");
                } else {
                    echo "{$nextParent}{$obj['title']}\n";
                }
            }
            if (!empty($children['nextPageToken'])) {
                $pageToken = $children['nextPageToken'];
            } else {
                $pageToken = NULL;
            }
        } catch (Exception $e) {
            print "An error occurred: " . $e->getMessage();
            $pageToken = NULL;
        }
    } while ($pageToken);
}
