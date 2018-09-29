<?php

require __DIR__ . '/vendor/autoload.php';

echo '<!doctype html><html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
</head>
<body>';

mb_internal_encoding('UTF-8');

const KNOWLEDGE_BASE_DIR = 'kb';
const FILESYSTEM_ITEMS_TO_IGNORE = [
    '.',
    '..',
    '.git',
];

$files = [];
$firstDirFiles = scanDirForFiles(KNOWLEDGE_BASE_DIR, $files);

if(isset($_REQUEST['test']) && ($_REQUEST['test'] !== '')) {
    //require_once 'src/Test.php';
    $test = new App\Test($_REQUEST['test']);
    echo $test->getTestHtml();
}

echo '<hr style="margin-top: 10em">';
echo '<button id="randQuestion" onclick="location.reload();">Random</button>';
echo '<hr><div id="listOfTestsAvailable" style="font-size: 0.8em">';
echo '<h1>Tests available:</h1>';
echo '<ul>';
foreach ($files as $test) {
    echo sprintf('<li><a href="?test=%s">%s</a></li>', urlencode($test), strtr($test, ['kb/' => '<span style="color:grey;">kb/</span>']));
}
echo '</ul>';
echo '<p>It is easy to <a href="https://github.com/sugalvojau/Knowledge-base" target="_blank">add your own tests and questions to Github</a>.</p>';
echo '</div>';


echo '</body>';

function scanDirForFiles($dir, &$files)
{
    $dirsAndFiles = array_diff(scandir($dir, SCANDIR_SORT_ASCENDING), FILESYSTEM_ITEMS_TO_IGNORE);

    foreach ($dirsAndFiles as $dirOrFile) {
        $pathToDirOrFile = $dir . DIRECTORY_SEPARATOR . $dirOrFile;
        if(is_file($pathToDirOrFile)) {
            $files[$pathToDirOrFile] = $pathToDirOrFile;
            continue;
        }
        if(is_dir($pathToDirOrFile)) {
            scanDirForFiles($pathToDirOrFile,$files);
        }
    }
}
