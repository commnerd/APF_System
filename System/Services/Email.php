<?php

namespace System\Services;

use System\Components\Email as Message;

/**
 * Service for managing email interaction
 */
class Email extends BaseService
{
	/**
	 * Create an email from static call to service
	 * @param  string $to             Recipient email address
	 * @param  string $subject        Email Subject
	 * @param  string $message        Message body
	 * @return System\Component\Email Email object
	 */
	public static function create($to = null, $subject = null, $message = null)
	{
		return new Message($to, $subject, $message);
	}

	/**
	 * Generate an email
	 * @param  string $to             Recipient email address
	 * @param  string $subject        Email Subject
	 * @param  string $message        Message body
	 * @return System\Component\Email Email object
	 */
	public function generateEmail($to = null, $subject = null, $message = null)
	{
		return Email::create($to, $subject, $message);
	}

	/**
	 * Send an email
	 * @param  System\Components\Email $msg Email object to send
	 * @return Hash value of the address parameter, or FALSE on failure.
	 */
	public function send(Message $msg)
	{
		return mail($msg->getTo(),$msg->getSubject(),$msg->getMessage(),$msg->printHeaders());
	}
}

?>
