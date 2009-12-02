<?php/** * Spoon Library * * This source file is part of the Spoon Library. More information, * documentation and tutorials can be found @ http://www.spoon-library.be * * @package		email * * * @author		Davy Hellemans <davy@spoon-library.be> * @author 		Tijs Verkoyen <tijs@spoon-library.be> * @author		Dave Lens <dave@spoon-library.be> * @since		1.0.0 *//** SpoonTemplate class */require_once 'spoon/template/template.php';/** * This exception is used to handle email related exceptions. * * @package		email * * * @author		Dave Lens <dave@spoon-library.be> * @since		1.0.0 */class SpoonEmailException extends SpoonException {}/** * This class is used to send emails * * @package		email * * * @author		Dave Lens <dave@spoon-library.be> * @since		1.0.0 */class SpoonEmail{	/**	 * Carriage return line feed, in hex values	 *	 * @var	string	 */	const CRLF = "\x0d\x0a";	/**	 * Attachments storage	 *	 * @var	array	 */	private $attachments = array();	/**	 * BCC storage	 *	 * @var	array	 */	private $bcc = array();	/**	 * CC storage	 *	 * @var	array	 */	private $cc = array();	/**	 * Charset storage	 *	 * @var string	 */	private $charset = 'utf-8';	/**	 * Template compile directory	 *	 * @var string	 */	private $compileDirectory;	/**	 * Email content storage	 *	 * @var array	 */	private $content = array('html' => '', 'plain' => '');	/**	 * Content type. Multipart/alternative by default	 *	 * @var	string	 */	private $contentType = 'multipart/alternative';	/**	 * Debug status	 *	 * @var	bool	 */	private $debug = false;	/**	 * Sender information	 *	 * @var	array	 */	private $from = array('name' => '', 'email' => '');	/**	 * Headers string storage	 *	 * @var string	 */	private $headers = '';	/**	 * This contains an email address suffix based on the host (like 'spoon-library.be')	 *	 * @var string	 */	private	$hostSuffix = 'localhost';	/**	 * Mailing method. Can be 'mail' or 'smtp'	 *	 * @var	string	 */	private $mailMethod = 'mail';	/**	 * E-mail priority storage (1 = high, 3 = normal, 5 = low)	 *	 * @var	int	 */	private $priority = 3;	/**	 * Regular recipients storage	 *	 * @var	array	 */	private $recipients = array();	/**	 * Reply-To storage	 *	 * @var array	 */	private $replyTo = array('name' => '', 'email' => '');	/**	 * SMTP object instance	 *	 * @var	SpoonSMTP	 */	private $smtp;	/**	 * E-mail subject storage	 *	 * @var	string	 */	private $subject;	/**	 * Connection timeout storage	 *	 * @var	int	 */	private $timeout;	/**	 * Initial To: storage	 *	 * @var	array	 */	private $to = array('name' => '', 'email' => '');	/**	 * Class constructor	 *	 * @return	void	 */	public function __construct()	{		// store the host suffix for use in the following functions		if(isset($_SERVER['HTTP_HOST'])) $this->hostSuffix = (strstr($_SERVER['HTTP_HOST'], 'www.')) ? str_replace('www.', '', $_SERVER['HTTP_HOST']) : $_SERVER['HTTP_HOST'];		// set starting values for the sender		$this->setFrom();		$this->setReplyTo();	}	/**	 * Adds an attachment to the headers.	 *	 * @return	void	 * @param	string $filename	 * @param	string[optional] $newName	 * @param	string[optional] $disposition	 * @param	string[optional] $encoding	 */	public function addAttachment($filename, $newName = null, $disposition = 'attachment', $encoding = 'base64')	{		// check input		if(!SpoonFile::exists($filename)) throw new SpoonException('File not found.');		// no name was found in the input		if(empty($newName))		{			// split up path to find the current filename			$aTemp = explode('/', $filename);			// sort by key in reverse order so the current file is in the first element			krsort($aTemp);			// store first element			$newName = reset($aTemp);		}		// store file extension		$extension = SpoonFile::getExtension($newName);		// store attachment disposition		$disposition = SpoonFilter::getValue($disposition, array('attachment', 'inline'), 'attachment');		// store type according to disposition		if($disposition === 'attachment') $extension = 'default';		// store file info		$this->attachments[] = array(			'file' => $filename,			'name' => $newName,			'encoding' => $encoding,			'type' => $this->getAttachmentContentType($extension),			'disposition' => $disposition,			'data' => chunk_split(base64_encode(SpoonFile::getContent($filename)))		);	}	/**	 * Adds a blind carbon copy recipient to the BCC stack.	 *	 * @return	void	 * @param	string $email	 * @param	string[optional] $name	 */	public function addBCC($email, $name = null)	{		// check input		if(!SpoonFilter::isEmail($email)) throw new SpoonEmailException('No valid e-mail address given.');		// add CC email and name to stack		$this->bcc[] = array('name' => (string) $name, 'email' => (string) $email);	}	/**	 * Adds a carbon copy recipient to the CC stack.	 *	 * @return	void	 * @param	string $email	 * @param	string[optional] $name	 */	public function addCC($email, $name = null)	{		// check input		if(!SpoonFilter::isEmail($email)) throw new SpoonEmailException('No valid e-mail address given.');		// add CC email and name to stack		$this->cc[] = array('name' => (string) $name, 'email' => (string) $email);	}	/**	 * Adds a single-line header to the email headers.	 *	 * @return	void	 * @param	string $header	 */	public function addHeader($header)	{		$this->headers .= (string) $header . self::CRLF;	}	/**	 * Adds a regular recipient to the recipients stack.	 *	 * @return	void	 * @param	string $email	 * @param	string[optional] $name	 */	public function addRecipient($email, $name = null)	{		// check input		if(!SpoonFilter::isEmail($email)) throw new SpoonEmailException('No valid e-mail address given.');		// add recipient email and name to stack		$this->recipients[] = array('name' => (string) $name, 'email' => (string) $email);	}	/**	 * Adds an array of recipients to the recipients stack.	 *	 * @return	void	 * @param	array $recipients	 */	public function addRecipientArray(array $recipients)	{		// loop recipients		foreach($recipients as $recipient)		{			// we need the values, not the keys			$recipient = array_values($recipient);			// store recipient parameters			(SpoonFilter::isEmail($recipient[0])) ? $email = $recipient[0] : $name = $recipient[1];			(SpoonFilter::isEmail($recipient[1])) ? $email = $recipient[1] : $name = $recipient[0];			// check if there's an email found, if so we store it			if(SpoonFilter::isEmail($email)) $this->addRecipient($email, $name);		}	}	/**	 * Closes the current SMTP connection.	 *	 * @return	void	 */	public function closeSMTPConnection()	{		// no smtp instance found		if($this->smtp === null) throw new SpoonEmailException('You can\'t close what isn\'t open.');		// close connection		$this->smtp->quit();	}	/**	 * Gets attachment content MIME type for given file extension	 *	 * @return	string	 * @param	string $extension	 */	private function getAttachmentContentType($extension)	{		// content types listed by extension		$types = array(    	'default' => 'application/octet-stream',			'ai'    =>  'application/postscript',			'aif'   =>  'audio/x-aiff',			'aifc'  =>  'audio/x-aiff',			'aiff'  =>  'audio/x-aiff',			'avi'   =>  'video/x-msvideo',			'bin'   =>  'application/macbinary',			'bmp'   =>  'image/bmp',			'cpt'   =>  'application/mac-compactpro',			'css'   =>  'text/css',			'dcr'   =>  'application/x-director',			'dir'   =>  'application/x-director',			'doc'   =>  'application/msword',			'doc'   =>  'application/msword',			'dvi'   =>  'application/x-dvi',			'dxr'   =>  'application/x-director',			'eml'   =>  'message/rfc822',			'eps'   =>  'application/postscript',			'gif'   =>  'image/gif',			'gtar'  =>  'application/x-gtar',			'hqx'   =>  'application/mac-binhex40',			'htm'   =>  'text/html',			'html'  =>  'text/html',			'jpe'   =>  'image/jpeg',			'jpeg'  =>  'image/jpeg',			'jpg'   =>  'image/jpeg',			'js'    =>  'application/x-javascript',			'log'   =>  'text/plain',			'mid'   =>  'audio/midi',			'midi'  =>  'audio/midi',			'mif'   =>  'application/vnd.mif',			'mov'   =>  'video/quicktime',			'movie' =>  'video/x-sgi-movie',			'mp2'   =>  'audio/mpeg',			'mp3'   =>  'audio/mpeg',			'mpe'   =>  'video/mpeg',			'mpeg'  =>  'video/mpeg',			'mpg'   =>  'video/mpeg',			'mpga'  =>  'audio/mpeg',			'oda'   =>  'application/oda',			'pdf'   =>  'application/pdf',			'php'   =>  'application/x-httpd-php',			'php3'  =>  'application/x-httpd-php',			'php4'  =>  'application/x-httpd-php',			'phps'  =>  'application/x-httpd-php-source',			'phtml' =>  'application/x-httpd-php',			'png'   =>  'image/png',			'ppt'   =>  'application/vnd.ms-powerpoint',			'ps'    =>  'application/postscript',			'qt'    =>  'video/quicktime',			'ra'    =>  'audio/x-realaudio',			'ram'   =>  'audio/x-pn-realaudio',			'rm'    =>  'audio/x-pn-realaudio',			'rpm'   =>  'audio/x-pn-realaudio-plugin',			'rtf'   =>  'text/rtf',			'rtx'   =>  'text/richtext',			'rv'    =>  'video/vnd.rn-realvideo',			'shtml' =>  'text/html',			'sit'   =>  'application/x-stuffit',			'smi'   =>  'application/smil',			'smil'  =>  'application/smil',			'swf'   =>  'application/x-shockwave-flash',			'tar'   =>  'application/x-tar',			'text'  =>  'text/plain',			'tgz'   =>  'application/x-tar',			'tif'   =>  'image/tiff',			'tiff'  =>  'image/tiff',			'txt'   =>  'text/plain',			'wav'   =>  'audio/x-wav',			'wbxml' =>  'application/vnd.wap.wbxml',			'wmlc'  =>  'application/vnd.wap.wmlc',			'word'  =>  'application/msword',			'xht'   =>  'application/xhtml+xml',			'xhtml' =>  'application/xhtml+xml',			'xl'    =>  'application/excel',			'xls'   =>  'application/vnd.ms-excel',			'xml'   =>  'text/xml',			'xsl'   =>  'text/xml',			'zip'   =>  'application/zip'		);		// return default if no (or unknown) extension is provided		return ($extension === null || empty($types[$extension])) ? $types['default'] : $types[$extension];	}	/**	 * Returns the SpoonEmail debugging status.	 *	 * @return	bool	 */	public function getDebug()	{		return (bool) $this->debug;	}	/**	 * Builds the e-mail headers	 *	 * @return	void	 */	private function getHeaders()	{		// create boundaries		$uniqueId = md5(uniqid(time()));		$boundary = 'SpoonEmail_Boundary1_'. $uniqueId;		$secondBoundary = 'SpoonEmail_Boundary2_'. $uniqueId;		// if plain body is not set, we'll strip the HTML tags from the HTML body		if(empty($this->content['plain'])) $this->content['plain'] = strip_tags($this->content['html']);		// build headers		$this->addHeader('Date: '. SpoonDate::getDate('r'));		$this->addHeader('From: '. $this->from['name'] .' <'. $this->from['email'] .'>');		// check mailmethod, some media don't need these (like mail())		if($this->mailMethod == 'smtp')		{			// set subject			$this->addHeader('Subject: '. $this->subject);			// set general To: header. useful if you prefer to customize it			if(!empty($this->to['name'])) $this->addHeader('To: '. $this->to['name'] .' <'. $this->to['email'] .'>');			// no To: set so we add recipients to the headers			else $this->addHeader('To: '. $this->reformatRecipientString($this->recipients));		}		// loop and add CCs to headers		if(!empty($this->cc)) $this->addHeader('cc: '. $this->reformatRecipientString($this->cc));		// loop and add BCCs to headers		if(!empty($this->bcc)) $this->addHeader('bcc: '. $this->reformatRecipientString($this->bcc));		// if attachments are set, change the mail content type		if(!empty($this->attachments)) $this->contentType = 'multipart/mixed';		// continue the rest of the headers		$this->addHeader('Reply-To: '. $this->replyTo['name'] .' <'. $this->replyTo['email'].'>');		$this->addHeader('Return-Path: '. $this->from['email']);		$this->addHeader('X-Priority: '. $this->priority);		$this->addHeader('X-Mailer: SpoonEmail (part of Spoon library - http://www.spoon-library.be)');		$this->addHeader('MIME-Version: 1.0');		$this->addHeader('Content-Type: '. $this->contentType .'; boundary="'. $boundary .'"'. self::CRLF);		$this->addHeader('Importance: normal');		$this->addHeader('Priority: normal');		$this->addHeader('This is a multi-part message in MIME format.'. self::CRLF);		$this->addHeader('--'. $boundary);		// attachments found		if(!empty($this->attachments))		{			// means we need a second boundary defined to send html/plain mails.			$this->addHeader('Content-Type: multipart/alternative; boundary="'. $secondBoundary .'"'. self::CRLF);			$this->addHeader('--'. $secondBoundary);			$this->addHeader('Content-Type: text/plain; charset="'. $this->charset .'"');			$this->addHeader('Content-Disposition: inline');			$this->addHeader('Content-Transfer-Encoding: 8bit'. self::CRLF);			$this->addHeader($this->content['plain'] . self::CRLF);			$this->addHeader('--'. $secondBoundary);			$this->addHeader('Content-Type: text/html; charset="'. $this->charset .'"');			$this->addHeader('Content-Disposition: inline');			$this->addHeader('Content-Transfer-Encoding: 8bit'. self::CRLF);			$this->addHeader($this->content['html'] . self::CRLF);			$this->addHeader('--'. $secondBoundary .'--');		}		// no attachments		else		{			// continue the rest of the headers			$this->addHeader('Content-Type: text/plain; charset="'. $this->charset .'"');			$this->addHeader('Content-Disposition: inline');			$this->addHeader('Content-Transfer-Encoding: 8bit'. self::CRLF);			$this->addHeader($this->content['plain'] . self::CRLF);			$this->addHeader('--'. $boundary);			$this->addHeader('Content-Type: text/html; charset="'. $this->charset .'"');			$this->addHeader('Content-Disposition: inline');			$this->addHeader('Content-Transfer-Encoding: 8bit'. self::CRLF);			$this->addHeader($this->content['html'] . self::CRLF);		}		// attachments found		if(!empty($this->attachments))		{			// loop attachments			foreach($this->attachments as $attachment)			{				// set attachment headers				$this->addHeader('--'. $boundary);				$this->addHeader('Content-Type: '. $attachment['type'] .'; name="'. $attachment['name'] .'"');				$this->addHeader('Content-Transfer-Encoding: '. $attachment['encoding']);				$this->addHeader('Content-Disposition: '. $attachment['disposition'] .'; filename="'. $attachment['name'] .'"' . self::CRLF);				$this->addHeader($attachment['data'] . self::CRLF);			}		}		// final boundary, closes the headers		$this->headers .= '--'. $boundary .'--';		// return headers string		return $this->headers;	}	/**	 * Returns the output of the current SpoonEmail instance. This will only have effect if you use SMTP to send mails.	 *	 * @return	string	 */	public function getOutput()	{		// debugging mode		if($this->debug)		{			// SMTP enabled			if($this->smtp !== null) return $this->smtp->getOutput();		}	}	/**	 * Returns the parsed content of a given template with optional variables	 *	 * @return	string	 * @param	string $template	 * @param	array[optional] $variables	 */	private function getTemplateContent($template, array $variables = null)	{		// declare template		$tpl = new SpoonTemplate();		$tpl->setCompileDirectory($this->compileDirectory);		$tpl->setForceCompile(true);		// parse variables in the template if any are found		if(!empty($variables)) $tpl->assign($variables);		// turn on output buffering		ob_start();		// html body		$tpl->display($template);		// return template content		return ob_get_clean();	}	/**	 * Function to store the actual content for either HTML or plain text	 *	 * @return	void	 * @param	string $type	 * @param	array $variables	 * @param	string $type	 */	private function processContent($content, $variables, $type)	{		// check if compile directory is set		if(empty($this->compileDirectory)) throw new SpoonEmailException('Compile directory is not set. Use setTemplateCompileDirectory.');		// check for type		$type = SpoonFilter::getValue($type, array('html', 'plain'), 'html');		// exploded string		$aExploded = explode('/', str_replace('\\', '/', $content));		// check if the string provided is a formatted as a file		if(SpoonFilter::isFilename(end($aExploded)) && preg_match('/^[\S]+\.\w{2,3}[\S]$/', end($aExploded)) && !strstr(end($aExploded), ' '))		{			// check if template exists			if(!SpoonFile::exists($content)) throw new SpoonEmailException('Template not found. ('.$content.')');			// store content			$this->content[$type] = (string) $this->getTemplateContent($content, $variables);		}		// string needs to be stored into a temporary file		else		{			// set the name for the temporary file			$tempFile = $this->compileDirectory .'/'. md5(uniqid()) .'.tpl';			// write temp file			SpoonFile::setContent($tempFile, $content);			// store content			$this->content[$type] = (string) $this->getTemplateContent($tempFile, $variables);			// delete the temporary			SpoonFile::delete($tempFile);		}	}	/**	 * Takes the name and e-mail in the given array and separates them with commas so they fit in a header	 *	 * @return	string	 * @param	array $recipients	 */	private function reformatRecipientString(array $recipients)	{		// recipients found		if(!empty($recipients))		{			// init var			$string = '';			// loop recipients			foreach($recipients as $recipient)			{				// reformat to a proper string				$stack = $recipient['name'] .' <'. $recipient['email'] .'>';				// just the email will do if no name is set				if(empty($recipient['name'])) $stack = $recipient['email'];				// add a comma as separator and store in new recipients stack				$string .= $stack .', ';			}			// return the reformatted string			return mb_substr($string, 0, -2, SPOON_CHARSET);		}	}	/**	 * Attempts to send the actual email.	 *	 * @return	bool	 */	public function send()	{		// no recipients found		if(empty($this->recipients)) throw new SpoonEmailException('Sending an email to no one is pretty redundant. Add some recipients first.');		// builds the headers for this email		$headers = $this->getHeaders();		// start with failed status		$status = false;		// check for mailmethod		switch($this->mailMethod)		{			// send with SMTP protocol			case 'smtp':				// pass MAIL FROM command				$this->smtp->mailFrom($this->from['email'], $this->from['name']);				// pass regular/CC/BCC recipients with RCPT TO command				if(!empty($this->recipients)) foreach($this->recipients as $recipient) $this->smtp->rcptTo($recipient['email']);				if(!empty($this->cc)) foreach($this->cc as $recipient) $this->smtp->rcptTo($recipient['email']);				if(!empty($this->bcc)) foreach($this->bcc as $recipient) $this->smtp->rcptTo($recipient['email']);				// initiate SMTP send				$status = $this->smtp->send($headers);			break;			// send with PHP's native mail() function			case 'mail':				// send mail				$status = mail($this->reformatRecipientString($this->recipients), $this->subject, null, $headers);			break;			// no one should be here			default:				throw new SpoonEmailException('Invalid mailmethod');		}		// clear the recipient lists and the headers		unset($this->recipients, $this->cc, $this->bcc, $this->headers);		// return status		return $status;	}	/**	 * Changes the charset from standard iso-8859-1 to your preferred value.	 *	 * @return	void	 * @param	string[optional] $charset	 */	public function setCharset($charset = 'utf-8')	{		$this->charset = ($charset !== null) ? SpoonFilter::getValue($charset, Spoon::getCharsets(), SPOON_CHARSET) : SPOON_CHARSET;	}	/**	 * Sets the debug mode on/off.	 *	 * @return	void	 * @param	bool[optional] $on	 */	public function setDebug($on = true)	{		$this->debug = (bool) $on;	}	/**	 * Adds the sender information.	 *	 * @return	void	 * @param	string[optional] $email	 * @param	string[optional] $name	 */	public function setFrom($email = null, $name = null)	{		// check input and replace empty values		if($email === null) $email = 'noreply@'. $this->hostSuffix;		if($name === null) $name = $this->hostSuffix;		// check for valid email address		if(!SpoonFilter::isEmail($email)) throw new SpoonEmailException('No valid email given.');		// save the 'from' information		$this->from['name'] = (string) $name;		$this->from['email'] = (string) $email;	}	/**	 * Sets the HTML content, which can be a template or just a string.	 *	 * @return	void	 * @param	string $content	 * @param	array[optional] $variables	 */	public function setHTMLContent($content, array $variables = null)	{		// check input		if($content === null) throw new SpoonEmailException('No content string or template given.');		// process content for html		$this->processContent($content, $variables, 'html');	}	/**	 * Sets the plain text content, which can be a template or just a string.	 *	 * @return	void	 * @param	string $content	 * @param	array[optional] $variables	 */	public function setPlainContent($content, array $variables = null)	{		// check input		if($content === null) throw new SpoonEmailException('Template not found.');		// process content for plain text		$this->processContent($content, $variables, 'plain');	}	/**	 * Sets the email priority level.	 *	 * @return	void	 * @param	int[optional] $level	 */	public function setPriority($level = 3)	{		// check input		if(!SpoonFilter::isInteger($level) || !SpoonFilter::getValue($level, range(1, 5, 1), 3, 'int')) throw new SpoonEmailException('No valid priority level given, integer from 1 to 5 required.');		// store priority level		$this->priority = $level;	}	/**	 * Sets the Reply-To header. If you don't use this function, the default will be noreply@yourhost.com (where yourhost.com is the value of $this->hostSuffix).	 *	 * @return	void	 * @param	string[optional] $email	 * @param	string[optional] $name	 */	public function setReplyTo($email = null, $name = null)	{		// check input and replace empty values		if($email === null) $email = 'noreply@'. $this->hostSuffix;		if($name === null) $name = $this->hostSuffix;		// check for valid email address		if(!SpoonFilter::isEmail($email)) throw new SpoonEmailException('No valid email given.');		// save the 'reply-to' information		$this->replyTo['name'] = (string) $name;		$this->replyTo['email']	= (string) $email;	}	/**	 * Sets authentication info for the current SMTP connection.	 *	 * @return	void	 * @param	string $username	 * @param	string $password	 */	public function setSMTPAuth($username, $password)	{		// no smtp instance found		if(!$this->smtp) throw new SpoonEmailException('Make an SMTP connection first.');		// push user and pass to the smtp object		$this->smtp->authenticate($username, $password);	}	/**	 * Sets the SMTP connection.	 *	 * @return	void	 * @param	string[optional] $host	 * @param	int[optional] $port	 * @param	int[optional] $timeout	 */	public function setSMTPConnection($host = 'localhost', $port = 25, $timeout = 30)	{		// set mailing method to smtp		$this->mailMethod = 'smtp';		// check if smtp.php is present		if(!SpoonFile::exists(dirname(__FILE__) .'/smtp.php')) throw new SpoonEmailException('SpoonSMTP not found, relocate and put it in the same folder as SpoonEmail.');		// require SpoonSMTP at this point		require_once 'smtp.php';		// store server information		$this->smtp = new SpoonSMTP($host, $port, $timeout);	}	/**	 * Sets the email's subject header.	 *	 * @return	void	 * @param	string $value	 */	public function setSubject($value)	{		$this->subject = (string) $value;	}	/**	 * Sets the email body template compile folder.	 *	 * @return	void	 * @param	string $path	 */	public function setTemplateCompileDirectory($path)	{		$this->compileDirectory = (string) $path;	}	/**	 * Sets the initial To: header to your liking, and thus masks a list of multiple recipients.	 * This will have no effect if you don't use SMTP (the mail() function does not accept it).	 *	 * @return	void	 * @param	string $name	 * @param	string[optional] $email	 */	public function setTo($name, $email = null)	{		// check input		if(!SpoonFilter::isEmail($email)) $email = SpoonFilter::urlise($name).'@'.$this->hostSuffix;		// save the 'to' information		$this->to['name'] = (string) $name;		$this->to['email']	= (string) $email;	}}?>