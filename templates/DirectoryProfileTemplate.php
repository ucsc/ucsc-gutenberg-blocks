<?php

get_header();
$cruzid = get_query_var('cruzid');
echo "<h1>Hello Profile Page $cruzid</h1>";
get_footer();
