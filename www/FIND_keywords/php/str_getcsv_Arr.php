<?php

/*1.  Создает массив массивов из строки файла $file (csv) с учетом разделителей $delim. Каждая строка будет разбита по разделителю в подмассив
 * Из файла с содержимым: may_DO_redacting;true
 *						  ecre;1234;qqqqqqqqqqqq
 *	СОЗДАЕТ МАССИВ: 	
 Array
(
    [0] => Array
        (
            [0] => may_DO_redacting
            [1] => true
        )

    [1] => Array
        (
            [0] => ecre
            [1] => 1234
            [2] => qqqqqqqqqqqq
        )
)		
*/
function str_getcsv_Arr($file, $delim){
    $csv = array_map(function ($elem) use ($delim){

                        return str_getcsv($elem, $delim);
                        }, file($file));
return $csv;
}