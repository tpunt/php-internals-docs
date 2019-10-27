<?php

namespace PHPInternalsDocs\Models;

interface Formatter
{
    // Used to format the data to an in-memory representation from the string-
    // based representation in the docs repo
    public function format();

    // A relic from importing the DB - may be reused in future if client app
    // allows for saving data
    public function __toString();
}
