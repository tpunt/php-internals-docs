<?php

namespace PHPInternalsDocs\Models;

class Symbol implements Formatter
{
    public $id = 0;
    public $name = '';
    public $url = '';
    public $type = '';
    public $declaration = '';
    public $parameters = [];
    public $definition = '';
    public $source_location = '';
    public $description = '';
    public $additional_information = '';
    public $categories = [];

    public function format()
    {
        if (is_string($this->parameters) && $this->parameters) {
            $parameter_pairs = explode("\n\n", $this->parameters);
            $this->parameters = [];

            foreach ($parameter_pairs as $parameter_pair) {
                $parameter = explode("\n", $parameter_pair);

                $this->parameters[] = $parameter[0];
                $this->parameters[] = $parameter[1];
            }
        }
    }

    /*
    Used to create compact forms of symbols (erasing unnecessary fields)
    */
    public function __clone()
    {
        $this->description = '';
        $this->additional_information = '';
        $this->declaration = '';
        $this->definition = '';
        $this->parameters = [];
    }

    // A relic from importing the DB - may be reused in future if client app
    // allows for saving data
    public function __toString()
    {
        $params_pairs = array_chunk($this->parameters ?? [], 2);
        $parameters = [];

        foreach ($params_pairs as $params_pair) {
            $parameters[] = implode("\n", $params_pair);
        }

        $parameters = implode("\n\n", $parameters);

        if ($parameters) {
            $parameters .= "\n";
        }

        $definition = $this->definition;

        if ($definition) {
            $definition .= "\n";
        }

        $additional_information = $this->additional_information;

        if ($additional_information) {
            $additional_information .= "\n";
        }

        return "[[[id]]]\n{$this->id}\n\n"
            . "[[[name]]]\n{$this->name}\n\n"
            . "[[[url]]]\n{$this->url}\n\n"
            . "[[[type]]]\n{$this->type}\n\n"
            . "[[[declaration]]]\n{$this->declaration}\n\n"
            . "[[[parameters]]]\n{$parameters}\n"
            . "[[[definition]]]\n{$definition}\n"
            . "[[[source_location]]]\n{$this->source_location}\n\n"
            . "[[[description]]]\n{$this->description}\n\n"
            . "[[[additional_information]]]\n{$additional_information}";
    }
}
