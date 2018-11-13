<?php

namespace System\Components;

/**
 * Email component for use by the system
 */
class Email extends AppComponent
{

    /**
     * The email address to send to
     * 
     * @var string
     */
    protected $to;

    /**
     * The email address to send from
     * 
     * @var string
     */
    protected $from;

    /**
     * The email subject
     * 
     * @var string
     */
    protected $subject;

    /**
     * The email message body
     * 
     * @var string
     */
    protected $message;

    /**
     * The email headers
     * 
     * @var string
     */
    private $_headers = array();

    /**
     * Create a new email
     * @param string $to      The email address to send to
     * @param string $subject The email subject
     * @param string $message The email message body
     */
    public function __construct($to = null, $subject = null, $message = null)
    {
        $this->to = $to;
        $this->from = $this->config['emailfrom'];
        $this->subject = $subject;
        $this->message = $message;

        $this->_headers = array(
            "MIME-Version" => "1.0",
            "Content-Type" => "text/html;charset=UTF-8",
        );
    }

    /**
     * Get headers for email
     */
    public function printHeaders()
    {
        if(isset($this->from)) {
            $this->_headers['From'] = $this->from;
        }
        $headers = array();
        foreach($this->_headers as $key => $val) {
            $headers[] = "$key:$val";
        }

        return implode("\r\n", $headers)."\r\n";
    }

    /**
     * Get headers for altering
     */
    public function getHeaders()
    {
        return $this->_headers;
    }

    /**
     * Get headers for email
     * @param array $headers Key/Value header pairs
     */
    public function setHeaders(array $headers)
    {
        $this->_headers = $headers;
    }

    /**
     * Add a header to the list
     * @param string $key The key for adding to the array
     * @param string $key The value to add to the array
     */
    public function addHeader($key, $val)
    {
        $this->_headers[$key] = $val;
    }

    /**
     * Set the email address to send to
     * @param string $email Email address
     */
    public function setTo($email)
    {
        $this->to = $email;
    }

    /**
     * Get the email address to send to
     * @return string Email address
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * The email address to send from
     * @param string $email Email address
     */
    public function setFrom($email)
    {
        $this->from = $email;
    }

    /**
     * Get the email address to send from
     * @return string Email address
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Set the email subject
     * @param string $subject Email subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * Get the email subject
     * @param string $subject Email subject
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set the email body
     * @param string $msg The email body to send
     */
    public function setMessage($msg)
    {
        $this->message = $msg;
    }

    /**
     * Get the email body
     * @return string The email body to send
     */
    public function getMessage()
    {
        return $this->message;
    }
}
