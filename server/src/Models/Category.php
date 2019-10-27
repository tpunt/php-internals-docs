<?php

namespace PHPInternalsDocs\Models;

class Category implements Formatter, \JsonSerializable
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
            if (!$this->subcategories) {
                $this->subcategories = [];
            } else {
                $this->subcategories = explode("\n", $this->subcategories);
            }
        }

        if (is_string($this->supercategories)) {
            if (!$this->supercategories) {
                $this->supercategories = [];
            } else {
                $this->supercategories = explode("\n", $this->supercategories);
            }
        }
    }

    public function jsonSerialize()
    {
        $subcategories = [];
        $supercategories = [];

        foreach ($this->subcategories as $url => $name) {
            $subcategories[] = ['category' => ['name' => $name, 'url' => $url]];
        }

        foreach ($this->supercategories as $url => $name) {
            $supercategories[] = ['category' => ['name' => $name, 'url' => $url]];
        }

        $category = [
            'category' => [
                'name' => $this->name,
                'url' => $this->url,
                'subcategories' => $subcategories,
                'supercategories' => $supercategories,
            ]
        ];

        if ($this->body) {
            $category['category']['introduction'] = $this->body;
        }

        return $category;
    }

    /*
    Used to create compact forms of categories (erasing unnecessary fields)
    */
    public function __clone()
    {
        $this->body = '';
        $this->articles = [];
        $this->symbols = [];
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
