<?php

namespace PHPInternalsDocs\Models;

class Category implements Formatter
{
    public $name = '';
    public $url = '';
    public $articles = [];
    public $symbols = [];
    public $subcategories = [];
    public $supercategories = [];
    public $body = '';

    public function format()
    {
        if (is_string($this->articles)) {
            if (!$this->articles) {
                $this->articles = [];
            } else {
                $this->articles = explode("\n", $this->articles);
            }
        }

        if (is_string($this->symbols)) {
            if (!$this->symbols) {
                $this->symbols = [];
            } else {
                $this->symbols = explode("\n", $this->symbols);
            }
        }

        if (is_string($this->subcategories)) {
            $this->subcategories = explode("\n", $this->subcategories);
        }

        if (is_string($this->supercategories)) {
            $this->supercategories = explode("\n", $this->supercategories);
        }
    }

    /*
    Used to create compact forms of articles (erasing unnecessary fields)
    */
    public function __clone()
    {
        $this->body = '';
    }

    // A relic from importing the DB - may be reused in future if client app
    // allows for saving data
    public function __toString()
    {
        $articles = implode("\n", $this->articles);
        $symbols = implode("\n", $this->symbols);
        $subcategories = implode("\n", $this->subcategories);
        $supercategories = implode("\n", $this->supercategories);

        if ($articles) {
            $articles .= "\n";
        }

        if ($symbols) {
            $symbols .= "\n";
        }

        if ($subcategories) {
            $subcategories .= "\n";
        }

        if ($supercategories) {
            $supercategories .= "\n";
        }

        return "[[[name]]]\n{$this->name}\n\n"
            . "[[[url]]]\n{$this->url}\n\n"
            . "[[[subcategories]]]\n{$subcategories}\n"
            . "[[[supercategories]]]\n{$supercategories}\n"
            . "[[[articles]]]\n{$articles}\n"
            . "[[[symbols]]]\n{$symbols}\n"
            . "[[[body]]]\n{$this->body}\n";
    }
}
