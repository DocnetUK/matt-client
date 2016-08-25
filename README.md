# PHP Client for MATT notifications #

This is a trivial PHP client (one class with a fluent interface) that helps you to monitor success or failure of stuff.

One thing that is often overlooked when writing crons and other regular processes is health monitoring.  This usually takes the form of "Oh my god, such-and-such a script has not run successfully for 2 weeks, help!".

## Absence of Success ##

One key thing here is that this system looks for the absence of a success message. So, if your cron starts hitting a Fatal error, this will tell you. Regular "log/email" stuff in a script will not even get hit.

## Getting Started ##

When you need to know if some regular process stops working, do something like this at the end of your script (when you know the job is done to satisfaction)

```php
Docnet\MATT::expect('My cron finished OK')->every('15m')->sms('07777123456');
```

If something should never happen, do this (perhaps in the main 'catch' block)

```php
Docnet\MATT::expect('Some horrible error')->never()->email('bill@microsoft.com');
```

### Install with Composer ###

Here's the require line for Composer users...

`"docnet/matt-client": "dev-master"`

### No set-up required ###

You don't need to go and set up monitors in a web interface or anything. The first time you make a call (as above) the server will start watching.

You'll get a single set up message too, on first call for a unique event reference.

### Named recipients ###

You can used named targets and groups that have been set up too, so in this example 'support' means several people

```php
Docnet\MATT::expect('Some other horrible error')->never()->email('support');
```

At the time of writing, named recipients must be set up by the author.

## Event Names ##

The `expect()` singleton factory method takes an event name/identifier as a parameter.  These are limited to 32 characters and will be truncated.

We trigger a `E_USER_WARNING` when we do this.

## Intervals ##

The `every()` method take a string parameter to represent intervals. Supported interval strings are one of

- minute
- hour
- day
- week
- month

OR, one of the following time representations, where N is a number

- Nm
- Nh
- Nd

### Changing Intervals ###

If you start off with

```php
Docnet\MATT::expect('My cron finished OK')->every('5m')->sms('07777123456');
```

And then decide you want to change the interval, just change the value you are passing to `every()`, like this:

```php
Docnet\MATT::expect('My cron finished OK')->every('15m')->sms('07777123456');
```

The system will update it's watch interval automatically for you, and send you another confirmation message.

## Alert Frequency ##

MATT will send you alerts once every skipped interval.

So, a failed cron due to run once per hour will send you alerts once per hour until it runs successfully again.

Watch out - If you ask for an SMS every minute - you'll get one!

## Monitors are HOST and DOCNET_APP_ID unique ##

By default, the `MATT` client will include your `hostname` as part of the request and, if defined, the `DOCNET_APP_ID`.

Monitors are unique to host+app_id pairs.

This means a monitor for "Google feed uploaded OK" can run and each client check will be independent of each other.

If you need to override the `hostname`, you can use the `from()` method as follows (but watch overlap with development).

```php
Docnet\MATT::expect('Clustered cron')->from('cluster')->every('hour')->email('support');
```

## Cancellation (stop monitoring) ##

If you need to stop the server monitoring a particular event, use `cancel()` like this

```php
MATT::expect('Some other horrible error')->cancel();
```
The system will notify current SMS and EMAIL recipients of the cancellation.

## Silent watching/cancelling ##

If you need to suppress the "Now watching..." and "Stopped watching..." messages, use `suppress_watch_message()` like this

```php
Docnet\MATT::expect('Clustered cron')->from('cluster')->every('hour')->email('support')->suppress_watch_message();

Docnet\MATT::expect('Clustered cron')->cancel()->suppress_watch_message();
```

## API calls over HTTPS with PHP Streams ##

As we don't know what sort of platforms we are going to be deployed on to, we don't use Curl in case it's not installed.

So, API calls are made over HTTPS using PHP native Streams and associated context options. If HTTPS using PHP native Streams fails 
and Curl is available, the API call will be attempted via Curl

API calls are made on `__destruct()` of each `MATT` instance. If the call fails, an `E_USER_WARNING` triggered.

If the response from the server includes a textual message, this is pushed out with an `E_USER_NOTICE`.

## PHP Version Support ##

Desired compatibility with PHP 5.3.0 and above.

## Coding Standards ##

Desired adherence to [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md).
