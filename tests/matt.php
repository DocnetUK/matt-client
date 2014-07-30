<?php
/**
 * Tests and examples for MATT client
 */
namespace Docnet;

require(dirname(__FILE__) . '/../src/Docnet/MATT.class.php');



// Examples

// MATT::expect('CLIENT X - Google upload OK')->every('day');

// Something we expect to happen at least every 15 minutes
MATT::expect('Order placed')->every('15m')->sms('07715122253');

// This thing should NEVER happen
// MATT::expect('horrible error')->never()->email('tom');





