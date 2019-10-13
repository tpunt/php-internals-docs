<?php

namespace PHPInternalsDocs\Models;

class Article implements Formatter, \JsonSerializable
{
    public $title = '';
    public $url = '';
    public $date = '';
    public $author = '';
    public $series_name = '';
    public $series_url = '';
    public $excerpt = '';
    public $body = '';
    public $categories = [];

    public function format() {}

    public function jsonSerialize()
    {
        $categories = [];

        foreach ($this->categories as $url => $name) {
            $categories[] = ['name' => $name, 'url' => $url];
        }

        return [
            'article' => [
                'title' => $this->title,
                'url' => $this->url,
                'date' => $this->date,
                'author' => ['name' => $this->author],
                'series_name' => $this->series_name,
                'series_url' => $this->series_url,
                'excerpt' => $this->excerpt,
                'body' => $this->body,
                'categories' => $categories,
            ]
        ];
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
        $series_name = $this->series_name;
        $series_url = $this->series_url;

        /* for the DB import only
        $date = substr($this->date, 0, 4) . '-'
            . substr($this->date, -4, 2) . '-'
            . substr($this->date, -2, 2);
        */

        if ($series_name) {
            $series_name .= "\n";
        }

        if ($series_url) {
            $series_url .= "\n";
        }

        return "[[[title]]]\n{$this->title}\n\n"
            . "[[[url]]]\n{$this->url}\n\n"
            . "[[[date]]]\n{$this->date}\n\n"
            . "[[[author]]]\n{$this->author}\n\n"
            . "[[[series_name]]]\n{$series_name}\n"
            . "[[[series_url]]]\n{$series_url}\n"
            . "[[[excerpt]]]\n{$this->excerpt}\n\n"
            . "[[[body]]]\n{$this->body}\n";
    }
}
