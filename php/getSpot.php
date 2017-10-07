<?php
/**
 * Created by PhpStorm.
 * User: fixright
 * Date: 2017/10/7
 * Time: 9:37
 * E-mail: 1397153057@qq.com
 */
include "./spots.php";


class date
{
//    讲十进制所有位数相加
    public function addAllNum($string)
    {

        $result = 0;
        $len = strlen($string);
        for ($i = 0; $i < $len; $i++) {
            $num = substr($string, $i, 1);
            $result = $result + $num;
        };
        return $result;
    }
//讲行号和列号缩小到实际范围
    public function form($size, $num)
    {
        for (; $num > $size;) {
            $num = $num / 2;
        }
        return intval($num);
    }
//获取腾讯开盘价
    public function openPrice()
    {
        $stockNum = '00700';
        $appkey = '4ed25d7a78f48dd4847918c29ea3dc8f';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://web.juhe.cn:8080/finance/stock/hk?num=" . $stockNum . "&key=" . $appkey);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $output = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($output);
        $openPrice = $data->result[0]->data->openpri;
        $result = [
            'resultcode' => $data->resultcode,
            'reason' => $data->reason,
            'openprice' => $openPrice
        ];
        return json_encode($result);
    }
//获取聚会地点A，B
    public function getSpots($openprice, $spots)
    {
        $date = date("Y.m.d");
        $result = md5($date + $openprice);
        $columnA = hexdec(substr($result, 0, 8));
        $rowA = hexdec(substr($result, 8, 8));

        //备选方案
        $columnB = hexdec(substr($result, 16, 8));
        $rowB = hexdec(substr($result, 24, 8));

        //将所有数字加起来作为行号
        $rowA = $this->addAllNum($rowA);
        $rowB = $this->addAllNum($rowB);

        //最多只有19行
        $totalRows = count($spots);

        //若行号超出19行则缩小到19行内
        $firstRow = $this->form($totalRows, $rowA);
        $secondRow = $this->form($totalRows, $rowB);

        //获取那一行有多少个位置
        $totalColumnA = count($spots[$firstRow - 1]);
        $totalColumnB = count($spots[$secondRow - 1]);

        //若列号超过了位置则缩小到位置数内
        $firstColumn = $this->form($totalColumnA, $columnA);
        $secondColumn = $this->form($totalColumnB, $columnB);

        $spots = [
            'first_choise' =>  ['column'=>$firstColumn, 'row'=>$firstRow],
            'second_choise' => ['column'=>$secondColumn, 'row'=>$secondRow]
        ];

        return json_encode($spots);
    }
//根据聚会地点A，B输出二维数组，方便前端遍历输出
    public function setMatrix($spots=[],$location){
        $spots = $spots;
        $location = json_decode($location);

        $rowA = $location->first_choise->row - 1;
        $columnA = $location->first_choise->column - 1;
        $rowB = $location->second_choise->row - 1;
        $columnB = $location->second_choise->column - 1;

//        将相应位置的值由0改为1
        $spots[$rowA] [$columnA]= 1;
        $spots[$rowB] [$columnB]= 1;

        return $spots;
    }
}

$date = new date();

$openPrice = 350;//$date->openPrice();

$location = $date->getSpots($openPrice,$spots);

$martix = $date->setMatrix($spots,$location);

$result = [
    'openPrice' => $openPrice,
    'date'      => date("Y.m.d"),
    'location'      => $location,
    'martix'    => $martix
];
echo json_encode($result);




