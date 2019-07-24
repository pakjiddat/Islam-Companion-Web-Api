<?php

declare(strict_types=1);

namespace Framework\Utilities;

/**
 * This class provides functions for sending emails 
 * 
 * It includes functions such as sending email with attachment
 * It uses pear Mail_Mime package (https://pear.php.net/package/Mail_Mime/)
 * And Mail package (https://pear.php.net/package/Mail/) 
 * 
 * @category   UtilityClass
 * @author     Nadir Latif <nadir@pakjiddat.pk>
 * @license    https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 */
final class Email
{
    /** @var Email $instance The single static instance */
    protected static $instance;
    /** @var array $params The parameters for the email */
    private $params;
    /** @var string $backend The backend to use with the email */
    private $backend;    
    /**
     * Used to return a single instance of the class
     *
     * Checks if instance already exists. If it does not exist then it is created. The instance is returned
     *
     * @return Email static::$instance name the instance of the correct child class is returned
     */
    public static function GetInstance(array $parameters) : Email
    {
        if (static::$instance == null) {
            static::$instance = new static ($parameters);
        }
        return static ::$instance;
    }
    /**
     * Class constructor
     *
     * @param array $parameters config information for the class
     *    params => array the parameters for the email. empty array may be passed See: https://pear.php.net/manual/en/package.mail.mail.factory.php
     *    backend => string [mail,smtp,sendmail] the backend to use for the email
     */
    public function __construct(array $parameters)
    {
    	/** The email parameters are set */
        $this->params             = $parameters['params'];
		/** The email backend is set */
        $this->backend            = $parameters['backend'];
    } 
	
    /**
     * Used to send email
     *
     * It allows sending attachments. It also allows sending email in html format
	 * 
     * @param string $from the sender of the email
     * @param string $to the reciever of the email
     * @param string $subject the subject of the email
     * @param string $text the message of the email
     * @param array $custom_headers optional the headers to include with the email message
     * @param array $attachment_files optional an array containing files to be attached with email     
     *								 
     * @throws Exception throws an exception if the attachment file could not be copied or if the email could not be sent
     * 
     * @return boolean $is_sent used to indicate if the email was sent.
     */
    public function SendEmail(
        string $from,
        string $to,
        string $subject,
        string $text,
        ?array $custom_headers = array(),
        ?array $attachment_files = array()
    ) : bool {
    
        try {
		    /** The email text is encoded */
		    $processed    = htmlentities($text);
			/** If the encoded text is same as the original text then the text is considered to be plain text */
		    if ($processed == $text)
		        $is_html = false;
			/** Otherwise the text is considered to be html */
		    else
		        $is_html = true;
		        
		    /** Mail_mine object is created */
			$message = new \Mail_mime();
			/** If the message is not html */
			if (!$is_html)
				$message->setTXTBody($text);
			/** If the message is html */
			else
				$message->setHTMLBody($text);
				
			/** If the attachment files were given */
			if (is_array($attachment_files) && count($attachment_files) > 0) {
			
				/** Each given file is attached */
				for ($count = 0; $count < count($attachment_files); $count++) {
					/** The absolute path to the attachment file */					
					$path_of_uploaded_file = $attachment_files[$count];
					
					/** If the file does not exist, then an exception is thrown */
					if (!is_file($path_of_uploaded_file))
					    throw new \Error("Email could not be sent");
					/** The file is attached */
					$message->addAttachment($path_of_uploaded_file);                   
				}
				
				/** The message body is fetched */
				$body         = $message->get();
				/** The extra headers */
				$headers      = array(
					                "From" => $from,
					                "Subject" => $subject,
					                "Reply-To" => $from
				                );
				/** If the user has given custom headers */
				if (is_array($custom_headers)) {
					/** The headers given by the user are merged */
					$headers  = array_merge($headers, $custom_headers);
				}
				
				/** The email headers */
				$headers      = $message->headers($headers);
		    }
		    else {
				/** The message body */
				$body         = $text;
				/** The message headers */
				$headers      = array(
				                    "From" => $from,
				                    "Subject" => $subject,
				                    "Reply-To" => $from,
				                    "Content-Type" => "text/html; charset=UTF-8"
				                );
				/** The headers given by the user are merged */
				$headers      = array_merge($headers, $custom_headers);				
				/** The mime parameters */
				$mime_params  = array(
								  'text_encoding' => '8bit',
								  'html_encoding' => '8bit',
								  'text_charset'  => 'UTF-8',
								  'html_charset'  => 'UTF-8',
								  'head_charset'  => 'UTF-8'
								);
				$body         = $message->get($mime_params);
				$headers      = $message->headers($headers);
			}
		    /** The Mail class object is created */
		    $mail             = \Mail::factory($this->backend, $this->params);
			/** The email is sent */
		    $is_sent          = $mail->send($to, $headers, $body);
			/** If the email is not sent, then an exception is thrown */
		    if (!$is_sent)
		        throw new \Error("Email could not be sent");
		    else
		        return true;
        }
        catch (\Error $e) {
            /** An exception is thrown */
            throw new \Error("Email could not be sent. Details: " . $e->getMessage());
        }
    }
}
