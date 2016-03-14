<?php
/**
 * Examples for MATT client
 */

require(dirname(__FILE__) . '/../src/Docnet/MATT.php');

// This should happen once per day
Docnet\MATT::expect('Google upload OK [test]')->every('day');

// Something we expect to happen at least every 15 minutes
Docnet\MATT::expect('Order placed [test]')->every('15m')->sms('07715122253');

// This thing should NEVER happen
Docnet\MATT::expect('Horrible error [test]')->never()->email('tom');

