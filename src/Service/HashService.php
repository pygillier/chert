<?php

namespace pygillier\Chert\Service;

class HashService 
{
    const ALNUM = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
    const ALNUM_ALT = "23456789ABCDEFGHJKLMNPRSTUVWXYZabcdefghjkmnpqrstuvwxyz";
    
    private $base;
    
    public function __construct($use_simple_alphabet=false)
    {
        $this->base = ($use_simple_alphabet) ? self::ALNUM_ALT:self::ALNUM;
    }
    
    public function getHash($value)
    {
        return self::encode(self::computeHash($value), $this->base);
    }
    
    public function getValue($hash)
    {
        return self::computeHash(self::decode($hash, $this->base));
    }
    
    /**
     * Computes a hash from an integer value using the Feistel cipher
     *
     * This method is bidirectional so it can returns the original integer.
     *
     * @param integer $value The value to compute
     */
    private static function computeHash($value)
    {
        $l1 = ($value >> 16) & 65535;
        $r1 = $value & 65535;
        for ($i = 0; $i < 3; $i++) {
            $l2 = $r1;
            $r2 = $l1 ^ (int) ((((1366 * $r1 + 150889) % 714025) / 714025) * 32767);
            $l1 = $l2;
            $r1 = $r2;
        }
        return ($r1 << 16) + $l1;
    }
    
    /*
     * Convert an integer to the given base
     *
     * @param integer $num the integer to convert
     * @param string $base the base to use
     * @return string The converted value
     */
    private static function encode($num, $base)
    {
        $b = strlen($base);
        $r = $num  % $b ;
        $res = $base[$r];
        $q = floor($num/$b);
        while ($q) {
            $r = $q % $b;
            $q =floor($q/$b);
            $res = $base[$r].$res;
        }
        return $res;
    }

    private static function decode($num, $base)
    {
        $b = strlen($base);
        $limit = strlen($num);
        $res=strpos($base,$num[0]);
        for($i=1;$i<$limit;$i++)
        {
            $res = $b * $res + strpos($base,$num[$i]);
        }
        return $res;
    }
}
