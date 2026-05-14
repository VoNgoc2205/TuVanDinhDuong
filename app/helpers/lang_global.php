<?php

require_once "app/helpers/Lang.php";

function __($key)
{
    return Lang::get($key);
}