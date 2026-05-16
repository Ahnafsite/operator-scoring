<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PM2 Binary Path
    |--------------------------------------------------------------------------
    |
    | The full path to the pm2 executable. If pm2 is in your global PATH,
    | you can leave this as 'pm2'. Otherwise, specify the absolute path.
    |
    */
    'binary' => env('PM2_BINARY', 'pm2'),
];
