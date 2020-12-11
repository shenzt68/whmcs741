<?php 
namespace WHMCS\Mail;


class Emailer
{
    protected $message = NULL;
    protected $entityId = NULL;
    protected $extraParams = NULL;
    protected $isNonClientEmail = false;
    protected $recipientUserId = NULL;
    protected $recipientContactId = NULL;
    protected $mergeData = array(  );

    const ENTITY_MAP = array( "admin" => "Admin", "affiliate" => "Affiliate", "domain" => "Domain", "general" => "General", "invoice" => "Invoice", "notification" => "Notification", "product" => "Product", "support" => "Support" );

    public function __construct(Message $message, $entityId, $extraParams = NULL)
    {
        $this->message = $message;
        $this->entityId = $entityId;
        $this->extraParams = $extraParams;
    }

    public static function factory(Message $message, $entityId, $extraParams = NULL)
    {
        if( !$message->getType() ) 
        {
            throw new \WHMCS\Exception("A message type is required");
        }

        $entityName = (array_key_exists($message->getType(), static::ENTITY_MAP) ? static::ENTITY_MAP[$message->getType()] : ucfirst($message->getType()));
        $entityClass = "WHMCS\\Mail\\Entity\\" . $entityName;
        return new $entityClass($message, $entityId, $extraParams);
    }

    public static function factoryByTemplate($template, $entityId = 0, $extraParams = NULL)
    {
        if( !$template instanceof Template ) 
        {
            $template = self::getTemplate($template, $entityId);
        }

        if( !$template instanceof Template ) 
        {
            throw new \WHMCS\Exception\Mail\InvalidTemplate("Email Template Not Found");
        }

        if( $template->disabled ) 
        {
            throw new \WHMCS\Exception\Mail\TemplateDisabled("Email Template Disabled");
        }

        $message = Message::createFromTemplate($template);
        $entityName = (array_key_exists($message->getType(), static::ENTITY_MAP) ? static::ENTITY_MAP[$message->getType()] : ucfirst($message->getType()));
        $entityClass = "WHMCS\\Mail\\Entity\\" . $entityName;
        return new $entityClass($message, $entityId, $extraParams);
    }

    public static function getTemplate($templateName, $entityId = 0)
    {
        if( $templateName == "defaultnewacc" ) 
        {
            $templateId = get_query_val("tblproducts", "tblproducts.welcomeemail", array( "tblhosting.id" => $entityId ), "", "", "", "tblhosting ON tblhosting.packageid=tblproducts.id");
            return Template::find($templateId);
        }

        return Template::where("name", "=", $templateName)->where("language", "=", "")->orWhere("language", "=", null)->first();
    }

    protected function getExtra($key)
    {
        if( is_array($this->extraParams) && array_key_exists($key, $this->extraParams) ) 
        {
            return $this->extraParams[$key];
        }

        return null;
    }

    protected function getClientMergeData()
    {
        $email_merge_fields = array(  );
        $userid = $this->recipientUserId;
        $contactid = 0;
        if( in_array($this->message->getTemplateName(), array( "Password Reset Validation", "Password Reset Confirmation", "Automated Password Reset" )) && $this->getExtra("contactid") ) 
        {
            $contactid = $this->getExtra("contactid");
        }

        if( $contactid ) 
        {
            $result2 = select_query("tblcontacts", "tblcontacts.*,(SELECT groupid FROM tblclients WHERE id=tblcontacts.userid) AS clgroupid,(SELECT groupname FROM tblclientgroups WHERE id=clgroupid) AS clgroupname,(SELECT language FROM tblclients WHERE id=tblcontacts.userid) AS language", array( "id" => $contactid, "userid" => $userid ));
        }
        else
        {
            $result2 = select_query("tblclients", "tblclients.*,tblclients.groupid AS clgroupid,(SELECT groupname FROM tblclientgroups WHERE id=tblclients.groupid) AS clgroupname", array( "id" => $userid ));
        }

        $data2 = mysql_fetch_array($result2);
        $id = $data2["id"];
        if( !$id ) 
        {
            if( $contactid ) 
            {
                throw new \WHMCS\Exception("Invalid contact id provided");
            }

            throw new \WHMCS\Exception("Invalid user id provided");
        }

        $firstname = $data2["firstname"];
        $email = $data2["email"];
        $lastname = $data2["lastname"];
        $companyname = $data2["companyname"];
        $address1 = $data2["address1"];
        $address2 = $data2["address2"];
        $city = $data2["city"];
        $state = $data2["state"];
        $postcode = $data2["postcode"];
        $country = $data2["country"];
        $phonenumber = $data2["phonenumber"];
        $language = $data2["language"];
        $credit = $data2["credit"];
        $status = $data2["status"];
        $language = $data2["language"];
        $clgroupid = $data2["clgroupid"];
        $clgroupname = (string) $data2["clgroupname"];
        $gatewayid = $data2["gatewayid"];
        $datecreated = fromMySQLDate($data2["datecreated"], 0, 1);
        $cardtype = $data2["cardtype"];
        $cardnum = $data2["cardlastfour"];
        $password = "**********";
        if( !function_exists("getCCDetails") ) 
        {
            require_once(ROOTDIR . "/includes/ccfunctions.php");
        }

        $carddetails = getCCDetails($userid);
        $cardexp = $carddetails["expdate"];
        unset($carddetails);
        $currency = getCurrency($userid);
        $totalInvoices = get_query_val("tblinvoices", "SUM(total)", array( "userid" => $userid, "status" => "Unpaid" ));
        $unpaidInvoiceIds = \WHMCS\Database\Capsule::table("tblinvoices")->where("status", "Unpaid")->where("userid", $userid)->lists("id");
        $paidBalance = 0;
        if( $unpaidInvoiceIds ) 
        {
            $paidBalance = get_query_val("tblaccounts", "SUM(amountin-amountout)", "tblaccounts.invoiceid IN (" . db_build_in_array($unpaidInvoiceIds) . ")");
        }

        $balance = floatval($totalInvoices) - floatval($paidBalance);
        $email_merge_fields["client_due_invoices_balance"] = formatCurrency($balance);
        if( $this->message->getTemplateName() == "Automated Password Reset" ) 
        {
            $password = generateFriendlyPassword();
            $hasher = new \WHMCS\Security\Hash\Password();
            $passwordhash = $hasher->hash($password);
            if( $contactid ) 
            {
                update_query("tblcontacts", array( "password" => $passwordhash ), array( "id" => $contactid ));
            }
            else
            {
                update_query("tblclients", array( "password" => $passwordhash ), array( "id" => $userid ));
            }

            run_hook("ClientChangePassword", array( "userid" => $userid, "password" => $password ));
        }

        $fullName = trim($firstname . " " . $lastname);
        if( $companyname ) 
        {
            $fullName .= " (" . $companyname . ")";
        }

        $email = trim($email);
        if( !$email ) 
        {
            throw new \WHMCS\Exception("Email address not set for client");
        }

        $this->message->addRecipient("to", $email, $fullName);
        $email_merge_fields["client_id"] = $userid;
        $email_merge_fields["client_name"] = $fullName;
        $email_merge_fields["client_first_name"] = $firstname;
        $email_merge_fields["client_last_name"] = $lastname;
        $email_merge_fields["client_company_name"] = $companyname;
        $email_merge_fields["client_email"] = $email;
        $email_merge_fields["client_address1"] = $address1;
        $email_merge_fields["client_address2"] = $address2;
        $email_merge_fields["client_city"] = $city;
        $email_merge_fields["client_state"] = $state;
        $email_merge_fields["client_postcode"] = $postcode;
        $email_merge_fields["client_country"] = $country;
        $email_merge_fields["client_phonenumber"] = $phonenumber;
        $email_merge_fields["client_password"] = $password;
        $email_merge_fields["client_signup_date"] = $datecreated;
        $email_merge_fields["client_credit"] = formatCurrency($credit);
        $email_merge_fields["client_cc_type"] = $cardtype;
        $email_merge_fields["client_cc_number"] = $cardnum;
        $email_merge_fields["client_cc_expiry"] = $cardexp;
        $email_merge_fields["client_language"] = $language;
        $email_merge_fields["client_status"] = $status;
        $email_merge_fields["client_group_id"] = $clgroupid;
        $email_merge_fields["client_group_name"] = $clgroupname;
        $email_merge_fields["client_gateway_id"] = $gatewayid;
        $email_merge_fields["unsubscribe_url"] = \App::getSystemUrl() . "unsubscribe.php?email=" . $email . "&key=" . sha1($email . $userid . \App::get_hash());
        if( !function_exists("getCustomFields") ) 
        {
            require_once(ROOTDIR . "/includes/customfieldfunctions.php");
        }

        $customfields = getCustomFields("client", "", $userid, true, "");
        $email_merge_fields["client_custom_fields"] = array(  );
        foreach( $customfields as $customfield ) 
        {
            $customfieldname = preg_replace("/[^0-9a-z]/", "", strtolower($customfield["name"]));
            $email_merge_fields["client_custom_field_" . $customfieldname] = $customfield["value"];
            $email_merge_fields["client_custom_fields"][] = $customfield["value"];
            $email_merge_fields["client_custom_fields_by_name"][] = array( "name" => $customfield["name"], "value" => $customfield["value"] );
        }
        $this->massAssign($email_merge_fields);
    }

    protected function getGenericMergeData()
    {
        $sysurl = \App::getSystemUrl();
        $whmcs = \App::self();
        $email_merge_fields = array(  );
        $email_merge_fields["company_name"] = \WHMCS\Config\Setting::getValue("CompanyName");
        $email_merge_fields["companyname"] = \WHMCS\Config\Setting::getValue("CompanyName");
        $email_merge_fields["company_domain"] = \WHMCS\Config\Setting::getValue("Domain");
        $email_merge_fields["company_logo_url"] = $whmcs->getLogoUrlForEmailTemplate();
        $email_merge_fields["whmcs_url"] = $sysurl;
        $email_merge_fields["whmcs_link"] = "<a href=\"" . $sysurl . "\">" . $sysurl . "</a>";
        $email_merge_fields["signature"] = nl2br(\WHMCS\Input\Sanitize::decode(\WHMCS\Config\Setting::getValue("Signature")));
        $email_merge_fields["date"] = date("l, jS F Y");
        $email_merge_fields["time"] = date("g:ia");
        $email_merge_fields["charset"] = \WHMCS\Config\Setting::getValue("Charset");
        $this->massAssign($email_merge_fields);
    }

    protected function allowCc()
    {
        $doNotCcList = array( "Password Reset Validation", "Password Reset Confirmation", "Automated Password Reset" );
        return !in_array($this->message->getTemplateName(), $doNotCcList);
    }

    protected function prepare()
    {
        $originalLanguage = \Lang::self();
        $this->getEntitySpecificMergeData($this->entityId, $this->extraParams);
        if( !$this->isNonClientEmail ) 
        {
            $this->getClientMergeData();
        }

        swapLang($originalLanguage);
        if( is_array($this->extraParams) ) 
        {
            $this->massAssign($this->extraParams);
        }

        $this->getGenericMergeData();
        $language = null;
        if( \App::isClientAreaRequest() && \WHMCS\Session::get("Language") ) 
        {
            $language = \WHMCS\Session::get("Language");
        }
        else
        {
            if( isset($this->mergeData["client_language"]) && $this->mergeData["client_language"] ) 
            {
                $language = $this->mergeData["client_language"];
            }

        }

        $localizedTemplate = Template::where("name", "=", $this->message->getTemplateName())->where("language", "=", $language)->first();
        if( isset($localizedTemplate->subject) && substr($this->message->getSubject(), 0, 10) != "[Ticket ID" ) 
        {
            $this->message->setSubject($localizedTemplate->subject);
        }

        if( isset($localizedTemplate->message) ) 
        {
            if( $this->message->getPlainText() && !$this->message->getBody() ) 
            {
                $this->message->setPlainText($localizedTemplate->message);
            }
            else
            {
                $this->message->setBodyAndPlainText($localizedTemplate->message);
            }

        }

        $hookresults = run_hook("EmailPreSend", array( "messagename" => $this->message->getTemplateName(), "relid" => $this->entityId ));
        foreach( $hookresults as $hookmergefields ) 
        {
            foreach( $hookmergefields as $key => $value ) 
            {
                if( $key == "abortsend" && $value == true ) 
                {
                    throw new \WHMCS\Exception\Mail\SendHookAbort("Email Send Aborted By Hook");
                }

                $this->assign($key, $value);
            }
        }
        $smarty = new \WHMCS\Smarty(false, "mail");
        $smarty->setMailMessage($this->message);
        $smarty->compile_id = md5($this->message->getSubject() . $this->message->getBody() . ((\App::isExecutingViaCron() || \WHMCS\Environment\Php::isCli() ? "cron" : "")));
        foreach( $this->mergeData as $mergefield => $mergevalue ) 
        {
            $smarty->assign($mergefield, $mergevalue);
        }
        $subject = $smarty->fetch("mailMessage:subject");
        $message = $smarty->fetch("mailMessage:message");
        $messageText = $smarty->fetch("mailMessage:plaintext");
        if( !trim($message) && !trim($messageText) ) 
        {
            throw new \WHMCS\Exception("Email message rendered empty - please check the email message Smarty markup syntax");
        }

        $this->message->setSubject($subject);
        $this->message->setBodyFromSmarty($message);
        $this->message->setPlainText($messageText);
        if( !$this->isNonClientEmail ) 
        {
            if( $this->allowCc() ) 
            {
                $recipients = array(  );
                if( $this->recipientContactId ) 
                {
                    $contact = \WHMCS\User\Client\Contact::find($this->recipientContactId);
                    if( $contact->clientId == $this->recipientUserId ) 
                    {
                        $recipients[] = $contact;
                    }

                }
                else
                {
                    $recipients = \WHMCS\User\Client\Contact::where("userid", $this->recipientUserId)->where($this->message->getType() . "emails", "=", "1")->get(array( "firstname", "lastname", "email" ));
                }

                foreach( $recipients as $recipient ) 
                {
                    $this->message->addRecipient("cc", $recipient->email, $recipient->firstName . " " . $recipient->lastName);
                }
            }
            else
            {
                $this->message->clearRecipients("cc");
                $this->message->clearRecipients("bcc");
            }

        }

    }

    public function getMergeData()
    {
        return $this->mergeData;
    }

    public function getMergeDataByKey($key)
    {
        return (isset($this->mergeData[$key]) ? $this->mergeData[$key] : "");
    }

    public function preview()
    {
        try
        {
            $this->prepare();
        }
        catch( \WHMCS\Exception\Mail\SendHookAbort $e ) 
        {
        }
        catch( \WHMCS\Exception $e ) 
        {
            logActivity("An Error Occurred with the email preview: " . $e->getMessage());
            throw $e;
        }
        return $this->message;
    }

    public function send()
    {
        try
        {
            $this->prepare();
            if( !$this->message->hasRecipients() ) 
            {
                throw new \WHMCS\Exception\Mail\SendFailure("No recipients provided for message");
            }

            $mail = new \WHMCS\Mail();
            $whmcsAppConfig = \App::getApplicationConfig();
            $smtp_debug = (int) $whmcsAppConfig["smtp_debug"];
            if( 0 < $smtp_debug ) 
            {
                $mail->SMTPDebug = $smtp_debug;
                if( !\WHMCS\Environment\Php::isCli() ) 
                {
                    $mail->Debugoutput = "html";
                }

            }

            $mail->sendMessage($this->message);
            $userId = $this->recipientUserId;
            $ticketReplyEmails = array( "Support Ticket Opened by Admin", "Support Ticket Reply" );
            $isTicketReplyEmail = in_array($this->message->getTemplateName(), $ticketReplyEmails);
            if( $userId && !($isTicketReplyEmail && \WHMCS\Config\Setting::getValue("DisableSupportTicketReplyEmailsLogging")) ) 
            {
                $this->message->saveToEmailLog($userId);
            }

            $emailuserlink = (0 < $userId ? " - User ID: " . $userId : "");
            $recipientName = trim($this->getMergeDataByKey("client_first_name") . " " . $this->getMergeDataByKey("client_last_name"));
            if( $recipientName ) 
            {
                logActivity("Email Sent to " . $recipientName . " (" . $mail->Subject . ") " . $emailuserlink, $userId);
            }

            $mail->clearAllRecipients();
            return true;
        }
        catch( \WHMCS\Exception\Mail\SendHookAbort $e ) 
        {
            logActivity("Email Sending Aborted by Hook (Subject: " . $this->message->getSubject() . ")", "none");
            throw $e;
        }
        catch( \phpmailerException $e ) 
        {
            $exceptionMessage = strip_tags($e->getMessage());
            logActivity("Email Sending Failed - " . $exceptionMessage . " (Subject: " . $this->message->getSubject() . ")", "none");
            throw new \WHMCS\Exception\Mail\SendFailure($exceptionMessage);
        }
        catch( \WHMCS\Exception $e ) 
        {
            logActivity("Email Sending Failed - " . $e->getMessage() . " (Subject: " . $this->message->getSubject() . ")", "none");
            throw new \WHMCS\Exception\Mail\SendFailure($e->getMessage());
        }
    }

    protected function setRecipient($userId, $contactId = NULL)
    {
        $this->recipientUserId = (int) $userId;
        $this->recipientContactId = ((int) $contactId ?: null);
        global $_LANG;
        global $currency;
        getUsersLang($userId);
        $currency = getCurrency($userId);
        return $this;
    }

    public function assign($key, $value)
    {
        $this->mergeData[$key] = $value;
        return $this;
    }

    public function massAssign($data)
    {
        foreach( $data as $key => $value ) 
        {
            $this->assign($key, $value);
        }
        return $this;
    }

    public function getMessage()
    {
        return $this->message;
    }

}


