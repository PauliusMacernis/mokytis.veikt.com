<?php

namespace App;

class Test
{
    private $testFile;

    public function __construct(string $testFile)
    {
        $this->setTestFile($testFile);
    }

    public function getTestFile(): string
    {
        return $this->testFile;
    }

    public function setTestFile(string $testFile): void
    {
        $this->testFile = $testFile;
    }

    public function getQuestions(): array
    {
        $testFileContent = file_get_contents($this->getTestFile());
        return $this->extractTests($testFileContent);
    }

    private function extractTests(string $testFileContent): array
    {
        $linesDelimiter = "\n";
        $lines = explode($linesDelimiter, $testFileContent);
        $questionsAndAnswers = [];

        $questionId = 0;
        $isQuestionBlockOpen = false;
        foreach($lines as $line) {

            if($isQuestionBlockOpen !== true && $this->isQuestionStarting($line)) {
                $questionId++;
                $questionsAndAnswers[$questionId]['q'] = $line;
                $questionsAndAnswers[$questionId]['a'] = null;
                continue;
            }

            if($questionId === 0) {
                // Do not continue while first question is not found
                continue;
            }

            if($isQuestionBlockOpen !== true && $this->isQuestionBlockStarting($line, $questionsAndAnswers[$questionId]['q'])) {
                $isQuestionBlockOpen = true;
                $questionsAndAnswers[$questionId]['q'] .= $linesDelimiter . $line;
                continue;
            }

            if($this->isQuestionBlockEnding($line)) {
                $isQuestionBlockOpen = false;
                $questionsAndAnswers[$questionId]['q'] .= $linesDelimiter . $line;
                continue;
            }

            if($isQuestionBlockOpen === true) {
                $questionsAndAnswers[$questionId]['q'] .= $linesDelimiter . $line;
                continue;
            }

//            if($this->isEmptyLine($line)) {
//                $questionsAndAnswers[$questionId]['q'] .= $linesDelimiter . $line;
//                continue;
//            }

            $questionsAndAnswers[$questionId]['a'] .= $linesDelimiter . $line;
        }

        return $questionsAndAnswers;

    }

    private function isQuestionStarting(string $line): bool
    {
        // In case it is ASCII
        $lineStripped = preg_replace('/\s+/', '', $line);
        if($this->startsLikeQuestion($lineStripped)) {
            return true;
        }

        // In case it is UTF8
        $lineStripped = mb_ereg_replace('/\s+/', '', $line);
        if($this->startsLikeQuestion($lineStripped)) {
            return true;
        }

        return false;
    }

    /**
     * @param false|string $lineStripped
     * @return bool
     */
    private function startsLikeQuestion($lineStripped): bool
    {
        // If whitespaces removed from the string then
        // each question starts with:
        // -**

        return $lineStripped !== false && mb_strpos($lineStripped, '-**') === 0;
    }

    private function isQuestionBlockStarting($line, $previousLine = null): bool
    {
        // Previous line

        $previousLineAllowsToContinue = true;
        if($previousLine !== null) {
            $previousLineAllowsToContinue = $this->isPreviousLineLikeQuestionAskingForContinuation($previousLine);
        }

        if($previousLineAllowsToContinue === false) {
            return false;
        }

        // Current line

        // In case it is ASCII
        $lineStripped = preg_replace('/\s+/', '', $line);
        if($this->continuousLikeQuestion($lineStripped)) {
            return true;
        }

        // In case it is UTF8
        $lineStripped = mb_ereg_replace('/\s+/', '', $line);
        if($this->continuousLikeQuestion($lineStripped)) {
            return true;
        }

        return false;
    }

    private function isQuestionBlockEnding($line): bool
    {
        // It ends the same way it starts (```).
        return $this->isQuestionBlockStarting($line);
    }

    /**
     * @param false|string $lineStripped
     * @return bool
     */
    private function continuousLikeQuestion($lineStripped): bool
    {
        // If whitespaces removed from the string then
        // each question may continue with code block or empty line:
        // ```

        return $lineStripped !== false && mb_strpos($lineStripped, '```') === 0;
    }

    public function getQuestion(int $questionId)
    {
        $testFileContent = file_get_contents($this->getTestFile());
        $questions = $this->extractTests($testFileContent);

        if(!isset($questions[$questionId])) {
            return [];
        }

        return $questions[$questionId];
    }

    private function isEmptyLine(string $line): bool
    {
        // In case it is ASCII
        $lineStripped = preg_replace('/\s+/', '', $line);
        if($lineStripped === '') {
            return true;
        }

        // In case it is UTF8
        $lineStripped = mb_ereg_replace('/\s+/', '', $line);
        if($lineStripped === '') {
            return true;
        }

        return false;
    }

    private function isEndingWithSemicolon($lineStripped): bool
    {
        return $lineStripped !== false && mb_strpos($lineStripped, ':') === mb_strlen($lineStripped);
    }

    private function isEndingWithSemicolonBoldded($lineStripped): bool
    {
        //$lineStripped = '- **Explain the following:**  ';

        return $lineStripped !== false &&
            (
                mb_strpos($lineStripped, ':**') + 1 === mb_strlen($lineStripped) - mb_strlen(':**') -1
                || mb_strpos($lineStripped, '**:') + 1 === mb_strlen($lineStripped) - mb_strlen('**:') -1
            );
        //var_dump($lineStripped, $a, mb_strpos($lineStripped, ':**'), mb_strlen($lineStripped) - mb_strlen(':**'), mb_strpos($lineStripped, ':**') === mb_strlen($lineStripped)); die();
    }

    private function isPreviousLineLikeQuestionAskingForContinuation($previousLine): bool
    {
        // In case it is ASCII
        $lineStripped = preg_replace('/\s+/', '', $previousLine);
        if ($this->isEndingWithSemicolon($lineStripped) || $this->isEndingWithSemicolonBoldded($lineStripped)) {
            return true;
        }

        // In case it is UTF8
        $lineStripped = mb_ereg_replace('/\s+/', '', $previousLine);
        if ($this->isEndingWithSemicolon($lineStripped) || $this->isEndingWithSemicolonBoldded($lineStripped)) {
            return true;
        }

        return false;
    }
}