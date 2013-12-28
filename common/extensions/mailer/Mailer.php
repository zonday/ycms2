<?php
/**
 * Mailer class file
 *
 * @author Yang <css3@qq.com>
 */

require dirname(__file__) . '/phpmailer/class.phpmailer.php';

/**
 * Mailer 邮件
 *
 * @author Yang <css3@qq.com>
 * @package common.extensions.mailer
 */
class Mailer extends CComponent
{
	public $smtp = true;
	public $host;
	public $username;
	public $password;
	public $secure;
	public $from;
	public $fromName;
	public $auth = true;
	public $port;

	private $_mailer;

	public function init()
	{
		if (!isset($this->from))
			throw new CException('Sendmail from is empty.');

		if (!isset($this->fromName))
			$this->fromName = Yii::app()->name;
	}

	public function getMailer()
	{
		if (!isset($this->_mailer)) {
			$this->_mailer = new PHPMailer(true);
			if ($this->smtp) {
				$this->_mailer->IsSMTP();
				$this->_mailer->Host = $this->host;
				$this->_mailer->Username = $this->username;
				$this->_mailer->Password = $this->password;
				$this->_mailer->SMTPAuth = $this->auth;

				if (isset($this->secure)) {
					$this->_mailer->SMTPSecure = 'ssl';
					if (!isset($this->port)) {
						$this->port = 465;
					}
				}

				if (!isset($this->port)) {
					$this->port = 25;
				}

				$this->_mailer->Port = $this->port;
			} else {
				$this->_mailer->IsMail();
			}

			$this->_mailer->From = $this->from;
			$this->_mailer->FromName = $this->fromName;
			$this->_mailer->CharSet = Yii::app()->charset;
			$this->_mailer->SetLanguage('zh');
		}

		$this->_mailer->ClearAddresses();
		$this->_mailer->ClearAllRecipients();
		$this->_mailer->ClearAttachments();
		$this->_mailer->ClearBCCs();
		$this->_mailer->ClearCCs();
		$this->_mailer->ClearReplyTos();

		return $this->_mailer;
	}

	public function sendmail($to, $subject, $message, $params=array())
	{
		if (!is_array($to)) {
			$to = explode(',', $to);
		}

		$mailer = $this->getMailer();
		foreach ((array) $to as $recipient) {
			try {
				// Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
				$recipient_name = '';
				if( preg_match('/(.*)<(.+)>/', $recipient, $matches ) ) {
					if ( count( $matches ) == 3 ) {
						$recipient_name = $matches[1];
						$recipient = $matches[2];
					}
				}

				$mailer->AddAddress($recipient, $recipient_name);
			} catch (phpmailerException $e ) {
				continue;
			}
		}

		$mailer->Subject = $subject;
		$mailer->Body = $message;

		if (isset($params['content_type'])) {
			$content_type = $params['content_type'];
		} else {
			$content_type = 'text/plain';
		}

		$mailer->ContentType = $content_type;

		if ('text/html' == $content_type )
			$mailer->IsHTML( true );

		Yii::trace("Send mail. \n To:". implode(',', $to) . "\n Subject:$subject");
		try {
			$mailer->Send();
		} catch (phpmailerException $e) {
			Yii::log("Send mail faild. \n To: ". implode(',', $to) . "\n Subject:$subject", CLogger::LEVEL_WARNING);
			throw $e;
		}
	}
}