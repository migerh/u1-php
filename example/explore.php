<?php

require_once("../src/OAuth-PHP.php");
require_once("../src/U1.php");

session_start();

$state = $_SESSION['state'];
$tokens = $_SESSION['oauth_tokens'];
$consumerName = 'U1 PHP Example';
$oauth = new U1_OAuth_PHP($consumerName);
$u1;

// Check if the user is authenticated
if (!isset($state) || $state < 4) {
    echo "First you have to fetch access tokens with the <a href=\"auth.php\">auth.php</a>.";
    die();
}

// Print the users account information, if available
$oauth->setToken($tokens);
$u1 = new U1($oauth);

try {
    $data = $u1->getAccountInfo();
} catch (U1_Exception $e) {
    echo "An error has occurred while fetching account information. Maybe your access tokens have expired or were revoked? Try getting new ones using <a href=\"auth.php?restart=1\">auth.php</a>.";
    die();
}

echo "<h2>Hello, " . $data['visible_name'] . " (<a href=\"auth.php?restart=1\">Logout</a>)!</h2><br />";
echo "You are currently using " . number_format($data['used_bytes']/1048576, 2) . " of " . number_format($data['max_bytes']/1048576, 2) . " MB (" . number_format($data['used_bytes']/$data['max_bytes']*100, 2) . "%).<br /><br />";

$volumes = $u1->getVolumes();

echo "Volumes: <ul>";
for ($i = 0; $i < count($volumes); $i++) {
    echo "<li><a href=\"explore.php?action=ls&volume=" . urlencode($volumes[$i]['path']) . "&path=/\">" . $volumes[$i]['path'] . "</a></li>";
}
echo "</ul>";

if (isset($_REQUEST['volume'])) {
    $vol = $_REQUEST['volume']; 
} else {
    $vol = $volumes[0]['path'];
}

if (isset($_REQUEST['path'])) {
    $path = $_REQUEST['path'];
} else {
    $path = '/';
}

if (isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
} else {
    $action = 'ls';
}

switch ($action) {
    case "ls";
        $content = $u1->getMetadata('/' . $vol, $path, true);
        echo "<strong>" . $content['resource_path'] . "</strong><br /><table>";
        for ($i = 0; $i < count($content['children']); $i++) {
            echo "<tr><td>" . $content['children'][$i]['path'] . "</td><td>" . ($content['children'][$i]['kind'] == "directory" ? "Folder" : "File") . "</td></tr>";
        }
        echo "</table>";
        break;
}
