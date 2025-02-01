<?php

namespace Core;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Model extends Eloquent
{
    public $timestamps = false;  // Disable timestamps if not needed
}
