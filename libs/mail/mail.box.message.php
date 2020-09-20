<?php

class MBMessage {
    /**
     *
     * @var MailboxReader
     */
    private $mailboxReader;

    /**
     * @var string - imap unique id
     */
    public $uid;

    /**
     * @var string - The message ID in actual imap stream
     */
    public $msgId;

    /**
     * Subject of the message
     * @var string
     */
    public $subject;

    /**
     * Type of content
     * @var string
     */
    public $typ;
    /**
     * Plain text message body
     * @var string
     */
    public $plain;

    /**
     * HTML message body
     * @var string
     */
    public $html;

    /**
     * Other parts such as attachments and images embedded in the HTML version of the mail
     * format is part_id => part_data
     * eg.:
     * array('1.2' => stdObj)
     * @var array
     */
    public $parts = [];

    public $attachments = [];

    public $attachmentDownloads = [];

    /**
     * assoc array containing the parts of the mail (But only relevant parts, multiparts will be left out.)
     * @var array
     */
    public $partIds = [];

    public $sender;
    public $from;
    public $to = [];

    public $header;

    public $raw_header;
    public $raw_body;

    /**
     * @var string - unique key
     */
    public $message_uid = null;

    public $sended = null;
    public $year = null;
    public $date = null;

    /**
     * Constructs a new MBMessage.
     * @param MailboxReader $mbreader
     */
    public function __construct($mbreader){
        $this->mailboxReader = $mbreader;
    }

    /**
     * Save an attachment specified by partId
     * @param string $partId
     * @param string $filename
     * @param string $path
     * @return string returns the filename of the downloaded attachment.
     * @throws MailboxReaderException
     */
    public function saveAttachment($partId,$filename=false,$path=MBREADER_ATTACHMENT_DIR){

        if(!isset($this->parts[$partId])){
        	throw new MailboxReaderException ('Invalid partId.');
        }
        $part = $this->parts[$partId];

        if($part->ifparameters){
        	$params = MailboxReader::parseParametersAssoc($part->parameters);
        }

        if(!$filename){
            $fname = $params['name']
                .($part->ifid ? md5($part->id) : ($part->bytes.'_'.rand(0,99999))).'.'.$part->subtype;

            $filename = md5(time().'_'.$fname).'.'.$part->subtype;
        }

        //Treat as tainted
        $filename = str_replace(['/','\\',"\0"],'_',$filename);

        $data = imap_fetchbody($this->mailboxReader->getIMAPRessource(),$this->msgId,$partId);

        switch($part->encoding){
            case 3 /*'base64'*/:
                $data = imap_base64($data);
            break;
            case 4 /*'quoted-printable'*/:
                $data = imap_qprint($data);
            break;
        }

        file_put_contents($path.'/'.$filename, $data);

        return $filename;
    }

    /**
     * Sets flags on messages
     * Default is \\SEEN.
     * See http://php.net/manual/en/function.imap-setflag-full.php
     * @param type $flag
     */
    public function setFlag($flag){
        $this->mailboxReader->setFlag($this->msgId, $flag);
    }

    /**
     * Mark a message for deletion from current mailbox
     * see http://php.net/manual/en/function.imap-delete.php
     * @param int $options
     */
    public function delete($options = 0){
        $this->mailboxReader->delete($this->msgId, $options);
    }

    /**
     * Mark message as seen;
     */
    public function setFlagSeen(){
        $this->mailboxReader->setFlag($this->msgId, "\\SEEN");
    }

    public function mime_decode($prop){
    	if(isset($this->header[$prop])){
    		return imap_mime_header_decode($this->header[$prop]);
    	}
    }
}