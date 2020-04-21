<?php
namespace Hyperf\PhoneSearch;

class PhoneSearch {

    private $prefmap=array();
    private $phonemap=array();
    private $phoneArr=array();
    private $addrArr=array();
    private $ispArr=array();
    private $fp;

    /**
     * PhoneSearch constructor.
     * @throws \Exception
     */
    function __construct() {
        $path = __DIR__.'/../qqzeng-phone.dat';
        if(is_null($path)){
            throw new \Exception('config:phonesearch.path is null');
        }
        $this->fp = fopen($path, 'rb');
        if(!$this->fp) throw new \Exception($path.' file is null');
        //获取长度
        $fsize = filesize($path);
        //读取全部数据
        $data = fread( $this->fp, $fsize);
        $PrefSize =$this->BytesToLong($data[0], $data[1], $data[2], $data[3]);
        $RecordSize =$this->BytesToLong($data[4], $data[5], $data[6], $data[7]);
        $descLength =$this->BytesToLong($data[8], $data[9], $data[10], $data[11]);
        $ispLength =$this->BytesToLong($data[12], $data[13], $data[14], $data[15]);

        //内容数组
        $descOffset = 16 + $PrefSize * 9 + $RecordSize * 7;
        fseek( $this->fp, $descOffset);
        $this->addrArr =explode('&',fread( $this->fp,  $descLength));
        //运营商数组
        $ispOffset = 16 + $PrefSize * 9 + $RecordSize * 7 + $descLength;
        fseek( $this->fp, $ispOffset);
        $this->ispArr =explode('&',fread( $this->fp,  $ispLength));
        //前缀区
        $m = 0;
        for ($k = 0; $k < $PrefSize;$k++)
        {
            $i = $k * 9 + 16;
            $n = ord($data[$i]) & 0xFF;

            $this->prefmap[$n][0] = $this->BytesToLong($data[$i + 1], $data[$i + 2], $data[$i + 3], $data[$i + 4]);
            $this->prefmap[$n][1] =$this-> BytesToLong($data[$i + 5], $data[$i + 6], $data[$i + 7], $data[$i + 8]);
            if ($m < $n)
            {
                for (; $m < $n; $m++)
                {
                    $this->prefmap[$m][0] = 0;
                    $this->prefmap[$m][1] = 0;
                }
                $m++;
            }
            else
            {
                $m++;
            }
        }
        //索引区
        for ($i = 0; $i < $RecordSize; $i++)
        {
            $p = 16 + $PrefSize * 9 + ($i * 7);
            $this->phoneArr[$i] = $this->BytesToLong($data[$p], $data[1 + $p], $data[2 + $p], $data[3 + $p]);
            $this->phonemap[$i][0] =$this->BytesToLong2($data[4 + $p],$data[5 + $p]);
            $this->phonemap[$i][1] = ord($data[6 + $p])<<0;
        }
        unset($data);
        fclose($this->fp);
    }

    function get($phone) {
        $pref= (int)(substr($phone,0, 3));
        $val = (int)(substr($phone,0,7));


        $low = $this->prefmap[$pref][0];
        $high = $this->prefmap[$pref][1];
        if ($high == 0)
        {
            return "";
        }
        $cur = $low == $high ? $low : $this->BinarySearch($low, $high, $val);
        if ($cur != -1)
        {
            return $this->addrArr[$this->phonemap[$cur][0]].'|'.$this->ispArr[$this->phonemap[$cur][1]];
        }
        else
        {
            return "";
        }

    }

    function BinarySearch($low, $high, $k) {

        if ($low > $high)	 {	 return -1;	 }
        else
        {
            $mid = ($low + $high)>>1;
            $phoneNum = $this->phoneArr[$mid];
            if ($phoneNum == $k) return (int)$mid;
            else if ($phoneNum > $k) return $this->BinarySearch($low, $mid - 1, $k);
            else return $this->BinarySearch($mid + 1, $high, $k);
        }


    }

    function BytesToLong($a, $b, $c, $d) {
        $iplong = (ord($a) << 0) | (ord($b) << 8) | (ord($c) << 16) | (ord($d) << 24);
        if ($iplong < 0) {

            $iplong+= 4294967296;//负数时
        };
        return $iplong;
    }


    function BytesToLong2($a, $b) {
        $iplong = (ord($a) << 0) | (ord($b) << 8) ;
        if ($iplong < 0) {

            $iplong+= 4294967296;//负数时
        };
        return $iplong;
    }
    function getMb(){
        $p = 0;
        $format='bytes';
        $num =memory_get_usage();
        if ($num>=pow(1024, 2) && $num<pow(1024, 3)) {
            $p = 2;
            $format = 'MB';
        }
        $num /= pow(1024, $p);
        return number_format($num, 3).' '.$format;
    }

}

?>