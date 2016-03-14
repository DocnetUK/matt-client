<?php
/**
 * Examples for Rate Limited MATT client
 */

require(dirname(__FILE__) . '/../src/Docnet/MATT.php');
require(dirname(__FILE__) . '/../src/Docnet/MATT/RateLimited.php');

$email = 'cmcmahon@example.com';

$i = 0;
while (true) {
    \Docnet\MATT\RateLimited::expect('Somethings Broken')->never()->email($email)->send();
    $i++;
    if (0 === ($i % 5)) {
        \Docnet\MATT\RateLimited::expect('Oh Noes')->never()->setApp('APP1')->email($email)->send();
    }
    if (0 === ($i % 6)) {
        \Docnet\MATT\RateLimited::expect('Oh Noes')->never()->setApp('APP2')->email($email)->send();
    }
    sleep(5);
}
