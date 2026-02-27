<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('app:product-import')->everyFiveMinutes()->withoutOverlapping();
