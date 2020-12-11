<?php 
namespace WHMCS\Cron\Task;


class AffiliateReports extends \WHMCS\Scheduling\Task\AbstractTask
{
    protected $defaultPriority = 1630;
    protected $defaultFrequency = 43200;
    protected $defaultDescription = "Send Monthly Affiliate Reports";
    protected $defaultName = "Affiliate Reports";
    protected $systemName = "AffiliateReports";
    protected $outputs = array( "sent" => array( "defaultValue" => 0, "identifier" => "sent", "name" => "Affiliate Reports Sent" ) );
    protected $icon = "fa-money";
    protected $isBooleanStatus = false;
    protected $successCountIdentifier = "sent";
    protected $successKeyword = "Emails Sent";

    public function monthlyDayOfExecution()
    {
        return \Carbon\Carbon::now()->startOfDay()->startOfMonth();
    }

    public function anticipatedNextRun(\Carbon\Carbon $date = NULL)
    {
        $startNextMonth = \Carbon\Carbon::now()->startOfMonth()->addMonth();
        $correctDayDate = $this->anticipatedNextMonthlyRun((int) $startNextMonth->format("d"), $date);
        if( $date ) 
        {
            $correctDayDate->hour($date->format("H"))->minute($date->format("i"));
        }

        return $correctDayDate;
    }

    public function __invoke()
    {
        if( !\WHMCS\Config\Setting::getValue("SendAffiliateReportMonthly") ) 
        {
            return $this;
        }

        $query = "SELECT aff.* FROM tblaffiliates aff" . " JOIN tblclients client on aff.clientid = client.id" . " WHERE client.status = 'Active'";
        $result = full_query($query);
        $reportsSent = 0;
        while( $data = mysql_fetch_array($result) ) 
        {
            $id = $data["id"];
            $reportsSent++;
            sendMessage("Affiliate Monthly Referrals Report", $id);
        }
        $this->output("sent")->write($reportsSent);
        return $this;
    }

}


