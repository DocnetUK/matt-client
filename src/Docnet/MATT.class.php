<?php
/**
 * MATT Client
 *
 * Used to set Matt Daemon's Expectations!
 *
 * Please note: our expectations of him are quite low.
 *
 * @author Tom Walder <tom@docnet.nu>
 */
namespace Docnet;
class MATT
{

    /**
     * Sent yet? We only want to do this once!
     *
     * @var bool
     */
    private $bol_sent = FALSE;

    /**
     * What event are we monitoring?
     *
     * @var string
     */
    private $str_event = NULL;

    /**
     * What is the source of the event?
     *
     * @var string
     */
    private $str_source = NULL;

    /**
     * Who should we tell via email when something goes wrong?
     *
     * @var string
     */
    private $str_email = NULL;

    /**
     * Who should we tell via SMS when something goes wrong?
     *
     * @var string
     */
    private $str_sms = NULL;

    /**
     * Expected Interval
     *
     * @var null
     */
    private $str_every = NULL;

    /**
     * Set up the event and hostname on construction.
     *
     * Objects should be crated by the expect() factory method
     *
     * @param $str_event
     */
    private function __construct($str_event)
    {
        $this->str_event = $str_event;
        $this->str_source = gethostname();
    }

    /**
     * MATT factory
     *
     * Don't create too many MATTs, or we'll be overrun!
     *
     * @param String $str_event Event UID (max 32 characters)
     * @return MATT
     */
    public static function expect($str_event)
    {
        if (strlen($str_event) > 32) {
            trigger_error(__METHOD__ . '() truncating event string to 32 characters', E_USER_WARNING);
            $str_event = substr($str_event, 0, 32);
        }
        return new self($str_event);
    }

    /**
     * What is the 'source' of the event?
     *
     * @param $str_from
     * @return $this
     */
    public function from($str_from)
    {
        $this->str_source = $str_from;
        return $this;
    }

    /**
     * This is a bad thing that should never have happened
     */
    public function never()
    {
        $this->str_every = 'never';
        return $this;
    }

    /**
     * Whatever is happening, should happen every INTERVAL
     *
     * Supported intervals are one of the following standard strings
     * - minute, hour, day, week, month
     *
     * OR, one of the following time representations, where N is a number
     * - Nm, Nh, Nd
     *
     * @param $str_interval
     * @return $this
     */
    public function every($str_interval)
    {
        $this->str_every = $str_interval;
        return $this;
    }

    /**
     * Cancel the notifications (i.e. tell the server to stop checking)
     *
     * @return $this
     */
    public function cancel()
    {
        $this->str_every = 'cancel';
        return $this;
    }

    /**
     * Who should we try and tell via email?
     *
     * @param $str_recipient
     * @return $this
     */
    public function email($str_recipient)
    {
        $this->str_email = $str_recipient;
        return $this;
    }

    /**
     * Who should we try and tell via email?
     *
     * @param $str_recipient
     * @return $this
     */
    public function sms($str_recipient)
    {
        $this->str_sms = $str_recipient;
        return $this;
    }

    /**
     * Send the data off to MattDaemon
     *
     * @todo Work on CA file location
     *
     * @return bool
     */
    public function send()
    {
        $arr_data = [
            'datatype' => 'auto-matt',
            'host' => $this->str_source,
            'app' => defined('DOCNET_APP_ID') ? DOCNET_APP_ID : 'unknown',
            'event' => $this->str_event,
            'time' => time(),
            'every' => $this->str_every,
            'email' => $this->str_email,
            'sms' => $this->str_sms,
            'cancel' => ('cancel' == $this->str_every)
        ];
        $arr_opts = [
            'ssl' => [
                'verify_peer' => true,
                'CN_match' => '*.appspot.com',
                'disable_compression' => true,
                // 'cafile' => '/path/to/cafile.pem',
                // 'ciphers' => 'HIGH:!SSLv2:!SSLv3',
            ],
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => http_build_query($arr_data)
            ]
        ];
        $this->bol_sent = TRUE;

        // Make the request
        $str_response = @file_get_contents('https://matt-daemon-eu.appspot.com/expect', FALSE, stream_context_create($arr_opts));
        return $this->process_response($str_response);
    }

    /**
     * Process any response data from Matt Daemon
     *
     * @param $str_response
     * @return bool
     */
    private function process_response($str_response)
    {
        if (FALSE === $str_response) {
            trigger_error(__METHOD__ . '() comms error', E_USER_WARNING);
            return FALSE;
        }
        $obj_response = json_decode($str_response);
        if (!is_object($obj_response)) {
            trigger_error(__METHOD__ . '() response error - invalid JSON', E_USER_WARNING);
            return FALSE;
        }
        if (isset($obj_response->message)) {
            trigger_error(__METHOD__ . '() response message: ' . $obj_response->message, E_USER_NOTICE);
        }
        if (isset($obj_response->success) && $obj_response->success == TRUE) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Send on shutdown, if not already sent
     */
    public function __destruct()
    {
        if (!$this->bol_sent) {
            $this->send();
        }
    }

}
