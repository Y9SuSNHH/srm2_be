<?php 

namespace App\Http\Support;

class Helper
{
    public static function formatVnFirstLetter($str)
    {
        // dd($str);
        $check_str = explode(' ',$str);
        $final_str = [];
        foreach($check_str as $check)
        {
            $noaccent = Helper::removeAccent($check);
            $word_length = strlen($noaccent);
            if($word_length == 1)
            {
                $final_word = Helper::vnVowelToUpperCase($check);
                array_push($final_str,$final_word);
                continue;
            }

            $str_split = substr($check,-$word_length+1,$word_length-1);
            $first_letter = trim($check,$str_split);
            $first_letter = Helper::vnVowelToUpperCase($first_letter);
            $final_word = $first_letter.$str_split;
            array_push($final_str,$final_word);
        }
        $final_str = implode(' ',$final_str);
        // dd($final_str);
        return $final_str;
    }

    public static function vnVowelToLowerCase($str)
    {
            $str = preg_replace('/(Á)/','á',$str);
            $str = preg_replace('/(À)/','à',$str);
            $str = preg_replace('/(Ạ)/','ạ',$str);
            $str = preg_replace('/(Ả)/','ả',$str);
            $str = preg_replace('/(Ã)/','ã',$str);
            $str = preg_replace('/(Ă)/','ă',$str);
            $str = preg_replace('/(Ắ)/','ắ',$str);
            $str = preg_replace('/(Ằ)/','ằ',$str);
            $str = preg_replace('/(Ặ)/','ặ',$str);
            $str = preg_replace('/(Ẳ)/','ẳ',$str);
            $str = preg_replace('/(Ẵ)/','ẵ',$str);
            $str = preg_replace('/(Â)/','â',$str);
            $str = preg_replace('/(Ấ)/','ấ',$str);
            $str = preg_replace('/(Ầ)/','ầ',$str);
            $str = preg_replace('/(Ậ)/','ậ',$str);
            $str = preg_replace('/(Ẩ)/','ẩ',$str);
            $str = preg_replace('/(Ẫ)/','ẫ',$str);
            $str = preg_replace('/(É)/','é',$str);
            $str = preg_replace('/(È)/','è',$str);
            $str = preg_replace('/(Ẹ)/','ẹ',$str);
            $str = preg_replace('/(Ẻ)/','ẻ',$str);
            $str = preg_replace('/(Ẽ)/','ẽ',$str);
            $str = preg_replace('/(Ê)/','ê',$str);
            $str = preg_replace('/(Ế)/','ế',$str);
            $str = preg_replace('/(Ề)/','ề',$str);
            $str = preg_replace('/(Ệ)/','ệ',$str);
            $str = preg_replace('/(Ể)/','ể',$str);
            $str = preg_replace('/(Ễ)/','ễ',$str);
            $str = preg_replace('/(Í)/','í',$str);
            $str = preg_replace('/(Ì)/','ì',$str);
            $str = preg_replace('/(Ị)/','ị',$str);
            $str = preg_replace('/(Ỉ)/','ỉ',$str);
            $str = preg_replace('/(Ĩ)/','ĩ',$str);
            $str = preg_replace('/(Ó)/','ó',$str);
            $str = preg_replace('/(Ò)/','ò',$str);
            $str = preg_replace('/(Ỏ)/','ỏ',$str);
            $str = preg_replace('/(Ọ)/','ọ',$str);
            $str = preg_replace('/(Õ)/','õ',$str);
            $str = preg_replace('/(Ô)/','ô',$str);
            $str = preg_replace('/(Ố)/','ố',$str);
            $str = preg_replace('/(Ồ)/','ồ',$str);
            $str = preg_replace('/(Ổ)/','ổ',$str);
            $str = preg_replace('/(Ộ)/','ộ',$str);
            $str = preg_replace('/(Ỗ)/','ỗ',$str);
            $str = preg_replace('/(Ơ)/','ơ',$str);
            $str = preg_replace('/(Ớ)/','ớ',$str);
            $str = preg_replace('/(Ờ)/','ờ',$str);
            $str = preg_replace('/(Ở)/','ở',$str);
            $str = preg_replace('/(Ợ)/','ợ',$str);
            $str = preg_replace('/(Ỡ)/','ỡ',$str);
            $str = preg_replace('/(Ú)/','ú',$str);
            $str = preg_replace('/(Ù)/','ù',$str);
            $str = preg_replace('/(Ủ)/','ủ',$str);
            $str = preg_replace('/(Ụ)/','ụ',$str);
            $str = preg_replace('/(Ũ)/','ũ',$str);
            $str = preg_replace('/(Ư)/','ư',$str);
            $str = preg_replace('/(Ứ)/','ứ',$str);
            $str = preg_replace('/(Ừ)/','ừ',$str);
            $str = preg_replace('/(Ự)/','ự',$str);
            $str = preg_replace('/(Ử)/','ử',$str);
            $str = preg_replace('/(Ữ)/','ữ',$str);
            $str = preg_replace('/(Ý)/','ý',$str);
            $str = preg_replace('/(Ỳ)/','ỳ',$str);
            $str = preg_replace('/(Ỷ)/','ỷ',$str);
            $str = preg_replace('/(Ỵ)/','ỵ',$str);
            $str = preg_replace('/(Ỹ)/','ỹ',$str);
            $str = preg_replace('/(đ)/','Đ',$str);
        return $str;
    }

    public static function vnVowelToUpperCase($str)
    {
            $str = preg_replace('/(á)/','Á',$str);
            $str = preg_replace('/(à)/','À',$str);
            $str = preg_replace('/(ạ)/','Ạ',$str);
            $str = preg_replace('/(ả)/','Ả',$str);
            $str = preg_replace('/(ã)/','Ã',$str);
            $str = preg_replace('/(ă)/','Ă',$str);
            $str = preg_replace('/(ắ)/','Ắ',$str);
            $str = preg_replace('/(ằ)/','Ằ',$str);
            $str = preg_replace('/(ặ)/','Ặ',$str);
            $str = preg_replace('/(ẳ)/','Ẳ',$str);
            $str = preg_replace('/(ẵ)/','Ẵ',$str);
            $str = preg_replace('/(â)/','Â',$str);
            $str = preg_replace('/(ấ)/','Ấ',$str);
            $str = preg_replace('/(ầ)/','Ầ',$str);
            $str = preg_replace('/(ậ)/','Ậ',$str);
            $str = preg_replace('/(ẩ)/','Ẩ',$str);
            $str = preg_replace('/(ẫ)/','Ẫ',$str);
            $str = preg_replace('/(é)/','É',$str);
            $str = preg_replace('/(è)/','È',$str);
            $str = preg_replace('/(ẹ)/','Ẹ',$str);
            $str = preg_replace('/(ẻ)/','Ẻ',$str);
            $str = preg_replace('/(ẽ)/','Ẽ',$str);
            $str = preg_replace('/(ê)/','Ê',$str);
            $str = preg_replace('/(ế)/','Ế',$str);
            $str = preg_replace('/(ề)/','Ề',$str);
            $str = preg_replace('/(ệ)/','Ệ',$str);
            $str = preg_replace('/(ể)/','Ể',$str);
            $str = preg_replace('/(ễ)/','Ễ',$str);
            $str = preg_replace('/(í)/','Í',$str);
            $str = preg_replace('/(ì)/','Ì',$str);
            $str = preg_replace('/(ị)/','Ị',$str);
            $str = preg_replace('/(ỉ)/','Ỉ',$str);
            $str = preg_replace('/(ĩ)/','Ĩ',$str);
            $str = preg_replace('/(ó)/','Ó',$str);
            $str = preg_replace('/(ò)/','Ò',$str);
            $str = preg_replace('/(ỏ)/','Ỏ',$str);
            $str = preg_replace('/(ọ)/','Ọ',$str);
            $str = preg_replace('/(õ)/','Õ',$str);
            $str = preg_replace('/(ô)/','Ô',$str);
            $str = preg_replace('/(ố)/','Ố',$str);
            $str = preg_replace('/(ồ)/','Ồ',$str);
            $str = preg_replace('/(ổ)/','Ổ',$str);
            $str = preg_replace('/(ộ)/','Ộ',$str);
            $str = preg_replace('/(ỗ)/','Ỗ',$str);
            $str = preg_replace('/(ơ)/','Ơ',$str);
            $str = preg_replace('/(ớ)/','Ớ',$str);
            $str = preg_replace('/(ờ)/','Ờ',$str);
            $str = preg_replace('/(ở)/','Ở',$str);
            $str = preg_replace('/(ợ)/','Ợ',$str);
            $str = preg_replace('/(ỡ)/','Ỡ',$str);
            $str = preg_replace('/(ú)/','Ú',$str);
            $str = preg_replace('/(ù)/','Ù',$str);
            $str = preg_replace('/(ủ)/','Ủ',$str);
            $str = preg_replace('/(ụ)/','Ụ',$str);
            $str = preg_replace('/(ũ)/','Ũ',$str);
            $str = preg_replace('/(ư)/','Ư',$str);
            $str = preg_replace('/(ứ)/','Ứ',$str);
            $str = preg_replace('/(ừ)/','Ừ',$str);
            $str = preg_replace('/(ự)/','Ự',$str);
            $str = preg_replace('/(ử)/','Ử',$str);
            $str = preg_replace('/(ữ)/','Ữ',$str);
            $str = preg_replace('/(ý)/','Ý',$str);
            $str = preg_replace('/(ỳ)/','Ỳ',$str);
            $str = preg_replace('/(ỷ)/','Ỷ',$str);
            $str = preg_replace('/(ỵ)/','Ỵ',$str);
            $str = preg_replace('/(ỹ)/','Ỹ',$str);
            $str = preg_replace('/(đ)/','Đ',$str);
        return $str;
    }

    public static function removeAccent($str)
    {
        $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $str);
		$str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $str);
		$str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $str);
		$str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $str);
		$str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $str);
		$str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", 'y', $str);
		$str = preg_replace("/(đ)/", 'd', $str);
		$str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", 'A', $str);
		$str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", 'E', $str);
		$str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", 'I', $str);
		$str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", 'O', $str);
		$str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", 'U', $str);
		$str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", 'Y', $str);
		$str = preg_replace("/(Đ)/", 'D', $str);
		$str = preg_replace("/(\“|\”|\‘|\’|\,|\!|\&|\;|\@|\#|\%|\~|\`|\=|\_|\'|\]|\[|\}|\{|\)|\(|\+|\^)/", '-', $str);
		$str = preg_replace("/( )/", '-', $str);

		return $str;
    }

    public static function generateXMLElement(string $style,string $value_type,string $value)
    {
        $element = new \DOMDocument('1.0','UTF-8');
        $new_el = $element->createElement('table:table-cell');
        $element->appendChild($new_el);

        //Create style attribute
        $style_attr = $element->createAttribute('table:style-name');
        $style_attr->value = $style;
        $new_el->appendChild($style_attr);

        //Create value type attribute
        $value_type_attr = $element->createAttribute('office:value-type');
        $value_type_attr->value = $value_type;
        $new_el->appendChild($value_type_attr);

        //Create value attribute
        $value_attr = $element->createAttribute('office:value');
        $value_attr->value = htmlspecialchars($value,ENT_QUOTES,'UTF-8');
        $new_el->appendChild($value_attr);
        // $line_break = $element->createElement('br');
        // $new_el->appendChild($line_break);
        $text_field = $element->createElement('text:p');
        $new_el->appendChild($text_field);
        $text_field->textContent = $value;
        $raw_output = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $element->saveXML());
        $out_put = trim($raw_output);
        // dd($out_put);
        return $out_put;
    }
}
