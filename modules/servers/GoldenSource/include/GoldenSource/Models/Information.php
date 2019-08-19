<?php
/**
 * GoldenSource module by Amirhossein Matini (matiniamirhossein@gmail.com) 
 * © All right reserved for GoldenSource Team (GoldenSource.Pro)
 */

namespace GoldenSource\Models;
use GoldenSource\GoldenSourceAPIClient;

class Information extends Model
{
    public $total_licenses;
    public $credit;
    public $exchangeRateRial;
    public $exchangeRateToman;
    public $discount;
    public $partnerLevel;
    public $email;
    public $dedicatedLink;
    public static function parse($input, GoldenSourceAPIClient $APIClient)
    {
        $obj = new self($APIClient);
        $obj->total_licenses = $input->TotalLicenses;
        $obj->credit = $input->Credit;
        $obj->exchangeRateRial = $input->ExchangeRateRial;
        $obj->exchangeRateToman = $input->ExchangeRateToman;
        $obj->discount = $input->MonthlyDiscount;
        $obj->partnerLevel = $input->PartnerLevel;
        $obj->email = $input->Email;
        $obj->dedicatedLink = $input->LinkDedicated ? $input->LicenseDomain : false;
        return $obj;
    }
}