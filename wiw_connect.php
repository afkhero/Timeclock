<?php

include 'Wheniwork.php';

$token = "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJhcHAiOjEsImlhdCI6MTUwODM1NDMzNywibG9naW4iOiI5NzYzMTA3IiwicGlkIjoiOTc2MzEwNyJ9.eW3h0DjO3ZlzBoB9J6GDUm90j82Ig9x4pZUTiCxI4TyB8GkOTRuGLVfrUKVBxAxrFGFYBKq543xiKgePUThPiwfHcqfrlcO_AjrazFGcHO_YBdCRox7Kxkp8aNJjyobe4zr2F1QyiioqdOMk2uQSqN5ps0IfBejZsUZ5x6jQmYP7Xt8vc8FHjzIPOEI4NC-2_uvOqa00YSj7xWvNO7DBO4YZmwdBCiDYpF_GJAC7QCSv12Enym2LqCu3U2VFrCgcESt9OZtubcBp6HjSz3G_MY2Xo-AIHApQ0KZ9p__9Z0aV8BH5Snif3MDhaZ-bNl-k3VBXbZz5-AIlgwcKVsQc0Q";

$wiw = new Wheniwork($token);

unset($token);

?>