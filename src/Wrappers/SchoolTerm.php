<?php

namespace spkm\isams\Wrappers;

use Carbon\Carbon;
use Illuminate\Support\Str;
use spkm\isams\Wrapper;

class SchoolTerm extends Wrapper
{
    protected function handle()
    {
        // Convert the Dates into Carbon Objects!
        foreach (get_object_vars($this) as $key => $var) {
            if (Str::is('*Date', $key)) {
                $this->$key = Carbon::parse($var);
            } else {
                $this->$key = $var;
            }
        }
    }
}
