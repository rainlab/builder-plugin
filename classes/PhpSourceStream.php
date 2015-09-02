<?php namespace RainLab\Builder\Classes;

use File;
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
     * Moves the head forward.
     * @return boolean Returns true if the head was successfully moved.
     * Returns false if the head can't be moved because it has reached the end of the steam.
     */
    public function forward()
    {
        return $this->setHead($this->getHead()+1);
    }

    /**
     * Returns the stream text from the head position to the next semicolon and updates the head.
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

        // The semicolon wasn't found
        return null;
    }
}