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

$countQuestionsAvailable = 0;
$countQuestionRand = 0;
$question = [];
if (isset($_REQUEST['test']) && ($_REQUEST['test'] !== '')) {
    $test = new App\Test($_REQUEST['test']);
    if (isset($_REQUEST['question']) && (int)$_REQUEST['question'] > 0) {
        $countQuestionsAvailable = count($test->getQuestions());
        $countQuestionRand = (int)$_REQUEST['question'];
        $question = $test->getQuestion((int)$_REQUEST['question']);
    } else {
        $questions = $test->getQuestions();
        if (!empty($questions)) {
            $countQuestionsAvailable = count($questions);
            $countQuestionRand = random_int(1, $countQuestionsAvailable);
            $question = $questions[$countQuestionRand];
        }
    }

    if ($countQuestionsAvailable === 0) {
        echo '<h1>No questions under: "' . htmlspecialchars($_REQUEST['test'], ENT_QUOTES, mb_internal_encoding()) . '"</h1>';
    }

    if ($question) {

        $Parsedown = new \Parsedown();

        echo
//            '<script>'
////            . '$( "#showAnswer" ).click(function() {
////                $( "#answer" ).toggle( "slow", function() {
////                  // Animation complete.
////                  alert("test");
////                });
////              });'
//            . '</script>'


            ''
            . '<h1 title="' . htmlspecialchars($_REQUEST['test'], ENT_QUOTES, mb_internal_encoding()) . '">Question #' . $countQuestionRand . '/' . $countQuestionsAvailable . ':</h1>'
            . '<div>' . trim($Parsedown->text($question['q'])) . '</div>'
            . '<hr>'
            . '<button id="showAnswer" onclick="$( \'#answer\' ).toggle();">Answer</button>'
            . '<div id="answer" style="display: none;">' . trim($Parsedown->text($question['a'])) . '</div>';

    }


}

echo '<hr style="margin-top: 10em">';

if ($countQuestionsAvailable > 1) {
    echo sprintf('<button id="randQuestion" onclick="window.location=\'?test=%s\';">Random</button>', urlencode($_REQUEST['test']));
}

$previousQuestion = $countQuestionRand - 1;
if ($countQuestionsAvailable > 1 && $previousQuestion > 0) {
    echo '<span style="width: 5em;">&nbsp;</span>';
    echo sprintf('<button id="previousQuestion" onclick="window.location=\'?test=%s&question=%s\';">Prev</button>', urlencode($_REQUEST['test']), $previousQuestion);
} elseif ($countQuestionsAvailable > 1) {
    echo '<span style="width: 5em;">&nbsp;</span>';
    echo sprintf('<button id="previousQuestion" disabled>Prev</button>');
} else {
    // Do not show
}

$nextQuestion = $countQuestionRand + 1;
if ($countQuestionsAvailable > 1 && $nextQuestion <= $countQuestionsAvailable) {
    echo sprintf('<button id="nextQuestion" onclick="window.location=\'?test=%s&question=%s\';">Next</button>', urlencode($_REQUEST['test']), $nextQuestion);
} elseif ($countQuestionsAvailable > 1) {
    echo sprintf('<button id="nextQuestion" disabled>Next</button>');
} else {
    // Do not show
}

if ($countQuestionsAvailable > 1) {
    echo '<span style="width: 5em;">&nbsp;</span>';
    //echo sprintf('<button id="lastQuestion" onclick="window.location=\'?test=%s&question=%s\';">Last</button>', urlencode($_REQUEST['test']), $countQuestionsAvailable);
}

if($countQuestionsAvailable > 1 && $countQuestionRand === 1) {
    echo sprintf('<button id="firstQuestion" disabled>First</button>');
} elseif($countQuestionsAvailable > 1) {
    echo sprintf('<button id="firstQuestion" onclick="window.location=\'?test=%s&question=%s\';">First</button>', urlencode($_REQUEST['test']), 1);
}

if($countQuestionsAvailable > 1 && $countQuestionRand === $countQuestionsAvailable) {
    echo sprintf('<button id="lastQuestion" disabled>Last</button>');
} elseif($countQuestionsAvailable > 1) {
    echo sprintf('<button id="lastQuestion" onclick="window.location=\'?test=%s&question=%s\';">Last</button>', urlencode($_REQUEST['test']), $countQuestionsAvailable);
}


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
        if (is_file($pathToDirOrFile)) {
            $files[$pathToDirOrFile] = $pathToDirOrFile;
            continue;
        }
        if (is_dir($pathToDirOrFile)) {
            scanDirForFiles($pathToDirOrFile, $files);
        }
    }
}
