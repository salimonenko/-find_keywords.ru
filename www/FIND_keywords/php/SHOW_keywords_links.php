<?php
// Функция читает файл с ключевыми словами и выбирает среди них наиболее подходящее

//header("Content-type: utf-8");

/* Установка внутренней кодировки этого скрипта в UTF-8 */
mb_internal_encoding("UTF-8");
$internal_enc = mb_internal_encoding();
mb_regex_encoding("utf-8");

if(!defined('perform') || (perform !== '123')) {die(mb_convert_encoding('Error 4: Эту программу нельзя запускать непосредственно. Access forbidden.', $internal_enc, $internal_enc));}



function SHOW_keywords_links($encoding_mess, $internal_enc, $reg_meta_wrong, $path_php, $path_php5_3, $path, $path0_abs, $max_len_Arr){

$max_search_len = $max_len_Arr[0];
$max_keywords_count = $max_len_Arr[1];
$max_links_count = $max_len_Arr[2];
$max_num_keyWords = $max_len_Arr[3]; // Максимальное число ключевых слов в тексте каждой ссылки
$metaphone_len = $max_len_Arr[4]; // Максимальное число символов (параметр) для функции  metaphone

$word = '';
$word_raw = '';

$this_FILE_name = str_replace(str_replace('\\','/', $_SERVER['DOCUMENT_ROOT']), '', str_replace('\\','/', __FILE__));



$path_str_getcsv_Arr = "str_getcsv_Arr.php";
    @(include_once $path_str_getcsv_Arr) or die('Отсутствует подключаемый файл '. $path_str_getcsv_Arr);


    if(isset($_REQUEST['word'])){
        $word_raw = mb_strtolower(mb_convert_encoding($_REQUEST['word'], $encoding_mess, $internal_enc));
    }else{
        $message_to_user = 'Неверный запрос. Похоже, ваш браузер работает неправильно.';
        return array(-1, $message_to_user, 400);
    }

    preg_match($reg_meta_wrong, $word_raw, $mathes1);

    if((mb_strlen($word_raw, $internal_enc) > $max_search_len) || preg_match($reg_meta_wrong, $word_raw, $mathes)){
        $message_to_user = 'Слишком длинные ключевые слова (можно не более 30 символов). Или подделка запроса.';

        return array(-1, $message_to_user, 500);
    }else{
        $word = $_REQUEST['word'];
    }

    $word = preg_replace($reg_meta_wrong, '', $word);
    $word = str_replace(array(',', '.'), ' ', $word);
	$word = preg_replace('/\s+/', ' ', $word);

    $translit = "translit.php";
    @(include_once $translit) or die('Отсутствует подключаемый файл '. $translit);

	
	$word_Arr = explode(' ', $word);
    $word_Arr_addition = array_filter($word_Arr, function ($elem) use ($internal_enc){
        return TEST_translit_pseudo($elem, $internal_enc); // Если слово короче, чем 5 букв
    });

    $word_Arr_addition = array_map('translit_pseudo', $word_Arr_addition);
    
    $word = $word. ' ' .implode(' ', $word_Arr_addition); // К строке искомых ключевых слов (из запроса) добавляем короткие псевдотранслитированные слова (в которых русские буквы, схожие с английскими, заменяются на последние)

    $word_str = translit($word);
    $word_str = trim(preg_replace('/\s+/', ' ', $word_str)); // Оставляем только по одному пробелу между словами
    $words = explode(' ', $word_str); // Массив отдельных ключевых слов (по одному) из запроса клиента


    $data = str_getcsv_Arr($path0_abs. $path, '>');

    if(!function_exists("array_column")){
        $path_array_column = $path_php5_3. 'array_column.php';
        @(include_once $path_array_column) or die('Отсутствует подключаемый файл '. $path_array_column);
    }

    $keywords = array_column($data, 1); // Массив ключевых слов всех html-страниц

    if((error_get_last() != '') || (is_array(error_get_last()) && (error_get_last() != array()) )){
        $message_to_user = 'Похоже, на сайте слишком много ключевых слов. Выполнение поиска невозможно. ';
        return array(-1, $message_to_user, 500);
    }

    $best_word = array();
    $best_word_count = array();
    $message_to_user = '';

$t2 = microtime(1);

$keywords_len = sizeof($keywords);
$k = 0;

$words_num_const = sizeof($words);



foreach ($words as $word){

    $RANG_order = 1 / ($words_num_const * 10);
    $words_num_const++; // Для слов, идущих в начале строки запроса (перечня искомых ключевых слов) $RANG_order будет выше; и, чем дальше, тем ниже

//    echo 'word='. metaphone($word). ' **************************** <br>';
    if($k > $max_keywords_count){
        break;
    }

//    $word_metaphone = metaphone($word, $metaphone_len);
    $word_metaphone = do_metaphone($encoding_mess, $word, $metaphone_len);
    $word_metaphone_len = strlen($word_metaphone); // Чтобы можно было сравнивать даже короткие слова из запроса с более длинными ключевыми словами

    if(!$word_metaphone_len){
        continue;
    }


// По массиву ВСЕХ ключевых слов сайта, каждый элемент которого содержит перечень ключевых слов для конкретной страницы
    for ($i = 0; $i < $keywords_len; $i++){
        $arr = explode(',', $keywords[$i]); // Массив ключевых слов конкретной html-страницы
        $arr_tr = array_map('translit', $arr); // Массив ключевых слов конкретной html-страницы в транслите
        $arr_tr = array_map('trim', $arr_tr);
        $arr_tr = array_map('mb_strtolower', $arr_tr);


        if($k > $max_keywords_count){ // Если число совпадающих ключевых слов больше максимального
            $message_to_user = 'Примечание: число совпадающих ключевых слов получилось больше максимально допустимого.';
            break;
        }


        foreach ($arr_tr as $keyw){ // По каждому из ключевых слов (для конкретной html-страницы): по точному совпадению или приближенно

            $expression_bool = $keyw === $word_metaphone || metaphone($keyw, $word_metaphone_len) === $word_metaphone;

            if ($expression_bool) {

                $best_word[$i] = isset($best_word[$i]) ? $best_word[$i] : '';
                $best_word[$i] .= trim($keyw). ',';

                $RANG_add = 1 + $RANG_order;

                $best_word_count[$i] = isset($best_word_count[$i]) ? $best_word_count[$i] + $RANG_add : $RANG_add; // Чем больше совпадающих (со словами, полученными из запроса) ключевых слов, тем выше это значение (и тем ближе к началу оно будет в списке найденных ссылок)

//echo $best_word_count[$i]. '['. $i. '] '.$keyw.'<br>';
                $k++;
//                break;
            }

        }

    }
}

    arsort($best_word_count); // Элементы массива, соответствующие максимальному числу совпадающих ключевых слов, будут среди его первых элементов
    reset($best_word_count);

    $maximum = array();

    $size = sizeof($best_word_count);

    $num_links = min($max_links_count, $size);

    if($max_links_count < $size){
        $flag_links_count = 'Найдено всего примерно '. $size. ' статей, соответствующих вашему запросу, но из них показано только '. $max_links_count. ' ссылок на статьи. Если вас не устраивают найденные результаты, вы можете повторить поиск, указав дополнительные слова:';
    }elseif(!$best_word_count){
        $flag_links_count = 'По вашему запросу ничего не найдено.';
    }else{
        $flag_links_count = 'Найдено всего примерно '. $size. ' статей, соответствующих вашему запросу. Если вас не устраивают найденные результаты, вы можете повторить поиск, указав дополнительные слова:';
    }

    $links = '<div style="border: 2px solid green;"><p class="dline">'. $flag_links_count .'</p><ul class="dline" style="padding-left: 25px;">';
              for($i = 0; $i < $num_links; $i++){
                // Массив содержит $num_links индексов (из файла URL_keywords.csv), URL которых наиболее соответствуют словам из запроса клиента
                        $index = key($best_word_count);
                        $finded = $data[$index][0];
                            $maximum[$index + 1] = $finded;
                            $textContent = implode(', ', array_slice(explode(',', $data[$index][1]), 0, $max_num_keyWords));

                            $links .= '<li style="margin: 10px 0"><a title="Читать статью" href="'. $finded. '">'. $textContent .'</a></li>';
                        next($best_word_count);
                    }
                    $links .= '</ul></div>';


$t3 = microtime(1);
// echo 'time2= '.($t3 - $t2);


// Окончательная проверка на ошибки
    if((error_get_last() != '') || (is_array(error_get_last()) && (error_get_last() != array()) )){

        $message_to_user .= 'При поиске по ключевым словам возникла неизвестная ошибка. Выполнение поиска невозможно. ';
        return array(-1, $message_to_user, 500);
    }



    $message_to_user .= mb_convert_encoding($links, $internal_enc);

    return array(1, $message_to_user, 200);
}




function do_metaphone($encoding_mess, $word, $metaphone_len){

    if(mb_strlen($word, $encoding_mess) < $metaphone_len || preg_match('/[\d\+]/', $word)){ // Если слово короткое или содержит цифру или +, то будем искать точное соответствие (в транслите)
        return $word;
    }

    $word_metaphone = metaphone($word, $metaphone_len);

    if(strlen($word_metaphone) < ($metaphone_len-1)){ // Если функция metaphone дала слишком короткую строку-код
        return $word;
    }else{
        return $word_metaphone;
    }

}

function TEST_translit_pseudo($elem, $internal_enc){
        if(mb_strlen($elem, $internal_enc) > 5){
            return false;
        }else{
            return true;
        }
}