<?php 
namespace WHMCS\Billing\Quote;


class Item extends \WHMCS\Model\AbstractModel
{
    protected $table = "tblquoteitems";
    protected $booleans = array( "taxable" );
    protected $columnMap = array( "isTaxable" => "taxable" );

    public function quote()
    {
        return $this->belongsTo("WHMCS\\Billing\\Quote", "quoteid");
    }

}


