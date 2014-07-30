# PHP Client for MATT notifications #

## Getting Started ##

When you need to know if some regular process stops working, do this

```php
MATT::expect('My cron finished OK')->every('15m')->sms('07777123456');
```

If something should never happen, do this

```php
MATT::expect('Some horrible error')->never()->email('bill@microsoft.com');
```

You can used named targets and groups that have been set up too, so in this example 'support' means several people

```php
MATT::expect('Some other horrible error')->never()->email('support');
```

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

## Cancellation (stop monitoring) ##

If you need to stop the server monitoring a particular event, use `cancel` like this

```php
MATT::expect('Some other horrible error')->cancel();
```