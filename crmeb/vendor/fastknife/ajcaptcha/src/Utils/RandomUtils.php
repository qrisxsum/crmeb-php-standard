<?php
declare(strict_types=1);

namespace Fastknife\Utils;


class RandomUtils
{
    /**
     * 获取随机数
     * @param $min
     * @param $max
     * @return int
     */
    public static function getRandomInt($min, $max): int
    {
        try {
            return random_int(intval($min), intval($max));
        }catch (\Exception $e){
            return mt_rand($min, $max);
        }
    }

    /**
     * 随机获取眼色值
     * @return array
     */
    public static function getRandomColor(): array
    {
         return [self::getRandomInt(1, 255), self::getRandomInt(1, 255), self::getRandomInt(1, 255)];
    }

    /**
     * 随机获取角度
     * @param int $start
     * @param int $end
     * @return int
     */
    public static function getRandomAngle(int $start = -45, int $end = 45): int
    {
         return self::getRandomInt($start, $end);
    }

    /**
     * 随机获取日文平假名
     * @param $num int 生成字符的数量
     * @return array
     */
    public static function getRandomChar(int $num): array
    {
        // 日文平假名字符集（包含清音、浊音、半浊音）
        $hiragana = [
            'あ', 'い', 'う', 'え', 'お',
            'か', 'き', 'く', 'け', 'こ',
            'さ', 'し', 'す', 'せ', 'そ',
            'た', 'ち', 'つ', 'て', 'と',
            'な', 'に', 'ぬ', 'ね', 'の',
            'は', 'ひ', 'ふ', 'へ', 'ほ',
            'ま', 'み', 'む', 'め', 'も',
            'や', 'ゆ', 'よ',
            'ら', 'り', 'る', 'れ', 'ろ',
            'わ', 'を', 'ん',
            'が', 'ぎ', 'ぐ', 'げ', 'ご',
            'ざ', 'じ', 'ず', 'ぜ', 'ぞ',
            'だ', 'ぢ', 'づ', 'で', 'ど',
            'ば', 'び', 'ぶ', 'べ', 'ぼ',
            'ぱ', 'ぴ', 'ぷ', 'ぺ', 'ぽ'
        ];

        $b = [];
        $count = count($hiragana);
        for ($i=0; $i<$num; $i++) {
            // 从平假名字符集中随机选择
            $h = $hiragana[self::getRandomInt(0, $count - 1)];
            if(!in_array($h, $b)){
                $b[] = $h;
            }else{
                $i--; //去重
            }
        }
        return $b;
    }


    /**
     * 类似java一样的uuid
     * @param string $prefix
     * @return string
     */
    public static function getUUID(string $prefix = ''): string
    {
        $chars = md5(uniqid((string) self::getRandomInt(1, 100), true));
        $uuid  = substr($chars,0,8) . '-';
        $uuid .= substr($chars,8,4) . '-';
        $uuid .= substr($chars,12,4) . '-';
        $uuid .= substr($chars,16,4) . '-';
        $uuid .= substr($chars,20,12);
        return $prefix . $uuid;
    }

    /**
     * 获取随机字符串编码
     * @param integer $length 字符串长度
     * @param integer $type 字符串类型(1纯数字,2纯字母,3数字字母)
     * @return string
     */
    public static function getRandomCode(int $length = 10, int $type = 1): string
    {
        $numbs = '0123456789';
        $chars = "abcdefghilkmnopqrstuvwxyz";
        $maps = '';
        if ($type === 1){
            $maps = $numbs;
        }
        if ($type === 2){
            $maps = $chars;
        }
        if ($type === 3){
            $maps = "{$numbs}{$chars}";
        }
        $string = $maps[self::getRandomInt(1, strlen($maps) - 1)];
        while (strlen($string) < $length) {
            $string .= $maps[self::getRandomInt(0, strlen($maps) - 1)];
        }
        return $string;
    }
}
