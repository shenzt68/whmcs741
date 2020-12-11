<?php 
namespace WHMCS\Module\Notification\Slack;


class Slack implements \WHMCS\Module\Contracts\NotificationModuleInterface
{
    use \WHMCS\Module\Notification\DescriptionTrait;

    const API_URL = "https://slack.com/api/";

    public function __construct()
    {
        $this->setDisplayName("Slack")->setLogoFileName("logo.svg");
    }

    public function settings()
    {
        return array( "oauth_token" => array( "FriendlyName" => "OAuth Access Token", "Type" => "text", "Description" => "An OAuth token for the Custom App you have installed in your Slack workspace. Your App needs the \"channels:read\" and \"chat:write:bot\" scopes." ) );
    }

    public function testConnection($settings)
    {
        $uri = "channels.list";
        $postdata = array( "limit" => "1" );
        try
        {
            $this->call($settings, $uri, $postdata);
        }
        catch( \WHMCS\Exception $e ) 
        {
            $errorMsg = $e->getMessage();
            if( $errorMsg == "An error occurred: invalid_auth" ) 
            {
                $errorMsg = "Token is invalid. Please check your input and try again.";
            }

            throw new \WHMCS\Exception($errorMsg);
        }
    }

    public function notificationSettings()
    {
        return array( "channel" => array( "FriendlyName" => "Channel", "Type" => "dynamic", "Description" => "Select the desired channel for a notification.", "Required" => true ), "message" => array( "FriendlyName" => "Customise Message", "Type" => "text", "Description" => "Allows you to customise the primary display message shown in the notification." ) );
    }

    public function getDynamicField($fieldName, $settings)
    {
        if( $fieldName == "channel" ) 
        {
            $uri = "channels.list";
            $postdata = array( "limit" => "1000" );
            $response = $this->call($settings, $uri, $postdata);
            $channels = array(  );
            foreach( $response->channels as $channel ) 
            {
                $channels[] = array( "id" => $channel->id, "name" => $channel->name );
            }
            return array( "values" => $channels );
        }
        else
        {
            return array(  );
        }

    }

    public function sendNotification(\WHMCS\Notification\Contracts\NotificationInterface $notification, $moduleSettings, $notificationSettings)
    {
        $messageBody = $notification->getMessage();
        if( $notificationSettings["message"] ) 
        {
            $messageBody = $notificationSettings["message"];
        }

        $attachment = (new Attachment())->fallback($messageBody . " " . $notification->getUrl())->title($notification->getTitle())->title_link($notification->getUrl())->text($messageBody);
        foreach( $notification->getAttributes() as $attribute ) 
        {
            $value = $attribute->getValue();
            if( $attribute->getUrl() ) 
            {
                $value = "<" . $attribute->getUrl() . "|" . $value . ">";
            }

            $attachment->addField((new Field())->title($attribute->getLabel())->value($value)->short());
        }
        $channel = $notificationSettings["channel"];
        $channel = explode("|", $channel, 2);
        $channelId = $channel[0];
        $message = (new Message())->channel($channelId)->username("WHMCS Bot")->attachment($attachment);
        $uri = "chat.postMessage";
        $this->call($moduleSettings, $uri, $message->toArray());
    }

    protected function call($settings, $uri, array $postdata = array(  ))
    {
        $postdata["token"] = $settings["oauth_token"];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::API_URL . $uri);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postdata));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
        $decoded = json_decode($response);
        logModuleCall("slack", $uri, $postdata, $response, $decoded, array( $settings["oauth_token"] ));
        if( !isset($decoded->ok) ) 
        {
            throw new \WHMCS\Exception("Bad response: " . $response);
        }

        if( $decoded->ok == false ) 
        {
            throw new \WHMCS\Exception("An error occurred: " . $decoded->error);
        }

        return $decoded;
    }

}


