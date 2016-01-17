<?php

namespace bpteam\Parser\Phone;

use bpteam\GenRegEx\GenRegEx;
use libphonenumber\PhoneNumberUtil;

class Phone
{
    private $separator = '(?:\(|\)|\-|\_|\[|\]|\s+){0,3}';
    private $phoneTemplateName = 'phone';
    private $phoneRegEx;
    protected $country = 'RU';
    /**
     * @var PhoneNumberUtil $phoneNumberUtil
     */
    protected $phoneNumberUtil;

    public function getUtil()
    {
        return $this->phoneNumberUtil;
    }

    public function setCountry($country)
    {
        $this->country = $country;
        $this->loadConfig();
    }

    /**
     * @return string
     */
    public function getPhoneRegEx()
    {
        return $this->phoneRegEx;
    }

    function __construct($country = 'RU')
    {
        $this->phoneNumberUtil = PhoneNumberUtil::getInstance();
        $this->setCountry($country);
    }

    public function loadConfig()
    {
        $this->phoneRegEx = $this->genPhoneRegEx();
    }

    /**
     * @return string
     */
    protected function genPhoneRegEx()
    {
        $countryCodeRegEx = GenRegEx::separateString($this->getUtil()->getCountryCodeForRegion($this->country));

        $countryCodes = $nationalCodes = [];
        foreach ($this->getUtil()->getMetadataForRegion($this->country)->numberFormats() as $nFormat) {

            $pattern = $nFormat->getPattern();

            $resultNumber = GenRegEx::textByPattern($pattern);

            if ($size = $nFormat->leadingDigitsPatternSize()) {

                $carrierCode = '(' . preg_replace('~\s+~', '', $nFormat->getLeadingDigitsPattern($size -1)) . ')';
                //$patterns = preg_replace('~\s+~', '', $nFormat->leadingDigitPatterns());
                //$carrierCode = '(' . (count($patterns) > 1 ? '(' . implode(')|(', $patterns) . ')' : current($patterns)) . ')' ;

                $resultCarrier = GenRegEx::textByPattern($carrierCode);

                $numberSize = strlen($resultNumber) - strlen($resultCarrier);
                $regEx = GenRegEx::separateArray([$carrierCode, $this->numberRegEx($this->separator, $numberSize)], $this->separator);
            } else {
                $numberSize = strlen($resultNumber);
                $regEx = $this->numberRegEx($this->separator, $numberSize);
            }
            $nationalCode = $nFormat->getNationalPrefixFormattingRule();
            $nationalCodes[] = str_replace('$1', $regEx, $this->nationalCodeRegEx($nationalCode));
            $countryCodes[] = $regEx;
        }

        $withCountryCode = $countryCodeRegEx . $this->separator . '(' . (count($countryCodes) > 1 ? '(' . implode(')|(', $countryCodes) . ')' : current($countryCodes)) . ')';
        $withNationalCode = count($nationalCodes) > 1 ? '(' . implode(')|(', $nationalCodes) . ')' : current($nationalCodes);

        return "~(?<{$this->phoneTemplateName}>({$withCountryCode})|($withNationalCode))~Uu";
    }

    /**
     * @param string $text
     * @return array
     */
    public function find($text)
    {
        $data = [];
        preg_match_all($this->phoneRegEx, $text, $parseData);
        if (isset($parseData[$this->phoneTemplateName])) {
            foreach ($parseData[$this->phoneTemplateName] as $result) {
                $data[] = $this->toNumber($result);
            }
        }
        return $data;
    }

    public function hidePhone($text, $replaceChar = '', $countNumbers = null)
    {
        preg_match_all($this->phoneRegEx, $text, $parseData);
        if (isset($parseData[$this->phoneTemplateName])) {
            foreach ($parseData[$this->phoneTemplateName] as $phone) {
                $countNumbers = $countNumbers?:strlen($phone);
                $replaceChar = str_pad($replaceChar, $countNumbers, $replaceChar);
                $text = preg_replace('~(' . preg_quote(substr($phone, $countNumbers * (-1)), '~') . ')~msu', $replaceChar, $text);
            }
        }

        return $text;
    }

    public function toNumber($data)
    {
        $number = $this->getUtil()->parse($this->onlyNumbers($data), $this->country);
        return $number->getCountryCode() . $number->getNationalNumber();
    }

    protected function onlyNumbers($text)
    {
        return preg_replace('~\D+~', '', $text);
    }

    protected function numberRegEx( $glue, $size)
    {
        $numbers = [];
        for ($i = 0; $i < $size; $i++) {
            $numbers[] = '\d';
        }

        return implode($glue, $numbers);
    }

    protected function nationalCodeRegEx($code)
    {
        $data = explode('$1', $code);
        $result = $this->onlyNumbers($data[0]) . $this->separator;
        $result .= '$1';
        if (isset($data[1])) {
            $result .= $this->separator . $this->onlyNumbers($data[1]);
        }

        return $result;
    }
}