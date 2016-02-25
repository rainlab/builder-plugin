<?php namespace RainLab\Builder\Classes;

use SystemException;

/**
 * Represents a PHP source code token stream.
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 */
class PhpSourceStream
{
    protected $tokens;

    protected $head = 0;

    protected $headBookmarks = [];

    public function __construct($fileContents) {
        $this->tokens = token_get_all($fileContents);
    }

    /**
     * Moves head to the beginning and cleans the internal bookmarks.
     */
    public function reset()
    {
        $this->head = 0;
        $this->headBookmarks = [];
    }

    public function getHead()
    {
        return $this->head;
    }

    /**
     * Updates the head position.
     * @return boolean Returns true if the head was successfully updated. Returns false otherwise.
     */
    public function setHead($head)
    {
        if ($head < 0) {
            return false;
        }

        if ($head > (count($this->tokens) - 1)) {
            return false;
        }

        $this->head = $head;
        return true;
    }

    /**
     * Bookmarks the head position in the internal bookmark stack.
     */
    public function bookmarkHead()
    {
        array_push($this->headBookmarks, $this->head);
    }

    /**
     * Restores the head position from the last stored bookmark.
     */
    public function restoreBookmark()
    {
        $head = array_pop($this->headBookmarks);
        if ($head === null) {
            throw new SystemException("Can't restore PHP token stream bookmark - the bookmark doesn't exist");
        }

        return $this->setHead($head);
    }

    /**
     * Discards the last stored bookmark without changing the head position.
     */
    public function discardBookmark()
    {
        $head = array_pop($this->headBookmarks);
        if ($head === null) {
            throw new SystemException("Can't discard PHP token stream bookmark - the bookmark doesn't exist");
        }
    }

    /**
     * Returns the current token and doesn't move the head.
     */
    public function getCurrent()
    {
        return $this->tokens[$this->head];
    }

    /**
     * Returns the current token's text and doesn't move the head.
     */
    public function getCurrentText()
    {
        $token = $this->getCurrent();
        if (!is_array($token)) {
            return $token;
        }

        return $token[1];
    }

    /**
     * Returns the current token's code and doesn't move the head.
     */
    public function getCurrentCode()
    {
        $token = $this->getCurrent();
        if (!is_array($token)) {
            return null;
        }

        return $token[0];
    }

    /**
     * Returns the next token and moves the head forward.
     */
    public function getNext()
    {
        $nextIndex = $this->head + 1;
        if (!array_key_exists($nextIndex, $this->tokens)) {
            return null;
        }

        $this->head = $nextIndex;
        return $this->tokens[$nextIndex];
    }

    /**
     * Reads the next token, updates the head and and returns the token if it has the expected code.
     * @param integer $expectedCode Specifies the code to expect.
     * @return mixed Returns the token or null if the token code was not expected.
     */
    public function getNextExpected($expectedCode)
    {
        $token = $this->getNext();
        if ($this->getCurrentCode() != $expectedCode) {
            return null;
        }

        return $token;
    }

    /**
     * Reads expected tokens, until the termination token is found.
     * If any unexpected token is found before the termination token, returns null.
     * If the method succeeds, the head is positioned on the termination token.
     * @param array $expectedCodesOrValues Specifies the expected codes or token values.
     * @param integer|string|array $terminationToken Specifies the termination token text or code.
     * The termination tokens could be specified as array.
     * @return string|null Returns the tokens text or null
     */
    public function getNextExpectedTerminated($expectedCodesOrValues, $terminationToken)
    {
        $buffer = null;

        if (!is_array($terminationToken)) {
            $terminationToken = [$terminationToken];
        }

        while (($nextToken = $this->getNext()) !== null) {
            $code = $this->getCurrentCode();
            $text = $this->getCurrentText();

            if (in_array($code, $expectedCodesOrValues) || in_array($text, $expectedCodesOrValues)) {
                $buffer .= $text;
                continue;
            }

            if (in_array($code, $terminationToken)) {
                return $buffer;
            }

            if (in_array($text, $terminationToken)) {
                return $buffer;
            }

            // The token should be either expected or termination. 
            // If something else is found, return null.
            return null;
        }

        return $buffer;
    }

    /**
     * Moves the head forward.
     * @return boolean Returns true if the head was successfully moved.
     * Returns false if the head can't be moved because it has reached the end of the steam.
     */
    public function forward()
    {
        return $this->setHead($this->getHead()+1);
    }

    /**
     * Moves the head backward.
     * @return boolean Returns true if the head was successfully moved.
     * Returns false if the head can't be moved because it has reached the beginning of the steam.
     */
    public function back()
    {
        return $this->setHead($this->getHead()-1);
    }


    /**
     * Returns the stream text from the head position to the next semicolon and updates the head.
     * If the method succeeds, the head is positioned on the semicolon.
     */
    public function getTextToSemicolon()
    {
        $buffer = null;

        while (($nextToken = $this->getNext()) !== null) {
            if ($nextToken == ';') {
                return $buffer;
            }

            $buffer .= $this->getCurrentText();
        }

        // The semicolon wasn't found.
        return null;
    }

    public function unquotePhpString($string)
    {
        if ((substr($string, 0, 1) === '\'' && substr($string, -1) === '\'') || 
            (substr($string, 0, 1) === '"' && substr($string, -1) === '"')) {
            return substr($string, 1, -1);
        }

        return false;
    }
}