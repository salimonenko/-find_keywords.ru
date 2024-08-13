<?php
// Функция читает URL файлов html из файла карты сайта, из каждого файла (соответственно ссылке) читает метатег <meta name="keywords" content="..." />, оттуда получает ключевые слова, создает строку вида (URL; keyword1, keyword2,...) и записывает ее в файл URL_keywords.csv

//header("Content-type: utf-8");

/* Установка внутренней кодировки этого скрипта в UTF-8 */
mb_internal_encoding("UTF-8");
$internal_enc = mb_internal_encoding();
mb_regex_encoding("utf-8");



if(!defined('perform') || (perform !== '123')) {die(mb_convert_encoding('Error 3: Эту программу нельзя запускать непосредственно. Access forbidden.', $internal_enc, $internal_enc));}


function DO_quick_tags($encoding_mess, $internal_enc, $reg_meta_wrong, $path0_abs, $path, $path1, $path2, $id_tag_response){

// 1. Добавляем (обновляем) функции в файл check_keywords.js
$adding_res = adding_functions($reg_meta_wrong, $id_tag_response);

    if($adding_res[0] < 0){ // Если была ошибка
        return $adding_res;
    }


// 2. Создаем файл со списком ключевых URL и слов для всего сайта (для тех URL, которые есть на сервере и ссылки на которые есть в карте сайта)
$file_map = 'Sitemap.xml';
$message_to_user = 'Слишком длинные ключевые слова (можно не более 30 символов). Или подделка запроса.';

// Читаем файл карты сайта и переводим его содержимое в XML
$file_xml = @file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/' . $file_map);

// проверка на ошибки
    if((error_get_last() != '') || (is_array(error_get_last()) && (error_get_last() != array()) )){
        $message_to_user = 'Невозможно открыть файл карты сайта '. $file_map;
        return array(-1, $message_to_user, 500);
    }


//$file_xml = mb_convert_encoding($file_xml, "cp1251", "utf-8");

$domdocument = new domDocument('1.0', $encoding_mess);
@$domdocument->loadXML(mb_convert_encoding($file_xml, $internal_enc, $internal_enc));

  if((error_get_last() != '') || (is_array(error_get_last()) && (error_get_last() != array()) )){
        $message_to_user = 'Ошибка при формировании XML из файла карты сайта '. $file_map;
        return array(-1, $message_to_user, 500);
    }

$loc_tags = $domdocument->getElementsByTagName("loc");


$non_existing = '';

    if(is_file($path0_abs. $path1)){
        $initial_size1 = filesize($path0_abs. $path1);
    }else{
        $initial_size1 = 0;
    }

    if(is_file($path0_abs. $path2)){
        $initial_size2 = filesize($path0_abs. $path2);
    }else{
        $initial_size2 = 0;
    }

$input = fopen($path0_abs. $path, 'w');
$input1 = fopen($path0_abs. $path1, 'w');
$input2 = fopen($path0_abs. $path2, 'w');


$userdata = array($non_existing, $input, $encoding_mess, $internal_enc, $input1, $path0_abs, $path, $path1, $input2, $path2,  $reg_meta_wrong);

// Просматриваем массив (точнее, DOMNodelist) $loc_tags, содержащий ссылки на файлы, взятые из карты сайта. И - формируем 3 файла:
// 1. Файл с перечнем URL и ключевых слов (по данному URL)
// 2. Файл с перечнем ссылок на файлы, имеющиейся в карте сайта, но отсутствующие на сервере
// 3. Файл, содержащий ссылки на файлы, содержащие недопустимые символы в ключевых словак


    $loc_tags_Arr = iterator_to_array($loc_tags);
    unset($loc_tags); // Для экономии оперативной памяти

    $loc_tags_len = sizeof($loc_tags_Arr);

    for ($i = 0; $i < $loc_tags_len; $i++){
        $rez = show_loc($loc_tags_Arr[$i], $userdata);
        if($rez[0] < 0){ // Если ошибка
            return array(-1, $message_to_user, 500);
        }
    }
//array_walk( iterator_to_array( $loc_tags ), 'show_loc', $userdata ) ; // К сожалению, даже медленнее, чем просто цикл

fclose($input);
fclose($input1);
fclose($input2);


$message_to_user = 'Файл с URL и ключевыми словами создан успешно.<br/>';

clearstatcache();
if(filesize($path0_abs . $path1) > 0){ // Если есть ссылки на файлы в карте сайта, а самих этих файлов нет на сервере

    $message_to_user .= 'В файле карты сайта есть ссылки, для которых нет файлов на сервере. Проверьте файл '. '<span style="font-weight:bold">..../'. $userdata[7] .'</span>';

    $URL_keywords = file_get_contents($path0_abs . $path1);
    $URL_keywords = "Здесь содержатся ссылки на файлы, которых НЕТ на сервере (но в файле карты сайта ссылки на них есть):" . PHP_EOL . $URL_keywords;
    file_put_contents($path0_abs . $path1, $URL_keywords);

clearstatcache();
}

if(filesize($path0_abs . $path2) > 0){ // Если файл с URL и ключевыми словами, содержащими недопустимые символы, непустой

    $message_to_user .= '<br/>В некоторых файлах html-страниц содержатся ключевые слова с недопустимыми символами или в другой кодировке (не UTF-8). Проверьте файл '. '<span style="font-weight:bold">..../'. $userdata[9] .'</span>';

    $URL_keywords = file_get_contents($path0_abs . $path2);
    $URL_keywords = "Здесь содержатся ссылки на файлы, в метатегах ключевых слов которых (<meta name=\"keywords\" content=\"...\" />) есть недопустимые символы или в неверной кодировке:" . PHP_EOL . $URL_keywords;
    file_put_contents($path0_abs . $path2, $URL_keywords);

clearstatcache();
}


return array(1, $message_to_user, 201);
}





// ******************  ДОПОЛНИТЕЛЬНЫЕ ФУНКЦИИ   *************************************************************************

// Функция читает файл, ссылка на который иммется в файле карты сайта, открывает его и читает содержимое метатега <meta name="keywords" content="..." />, оттуда получает ключевые слова, создает строку вида (URL; keyword1, keyword2,...) и записывает ее в файл URL_keywords.csv
function show_loc($item, $userdata){
/* $item - тег <loc> из файла карты сайта
*/

    $URL = $item->textContent;
    $PHP_URL_QUERY = parse_url($URL, PHP_URL_QUERY) ? '?'. parse_url($URL, PHP_URL_QUERY) : '';
    $file_name = $_SERVER['DOCUMENT_ROOT']. parse_url($URL, PHP_URL_PATH). $PHP_URL_QUERY; // Абсолютный путь к файлу с учетом строки запроса

    $URL = parse_url($URL, PHP_URL_PATH). $PHP_URL_QUERY; // Берем относительный путь


    $file_name = mb_convert_encoding($file_name, $userdata[2] , $userdata[3]); // Для кирилических имен файлов

    if(is_file($file_name)){ // Если на файл с таким путем есть ссылка в карте сайта
        $file_content = file_get_contents($file_name);

        $utf8 = "utf-8";
        $cp1251 = "cp1251";
        $true_encoding = check_enc($URL, $file_content, $userdata[2], $userdata[3], $utf8, $cp1251);


        $reg_meta = '[<meta\s+name=\"keywords\"\s+content=\"([^"]*)\"[^>]*>' .']';

        if(preg_match($reg_meta, $file_content, $matches, PREG_OFFSET_CAPTURE)){ // Если в файле страницы есть такой метатег

            // Проверяем ключевые слова на наличие недопустимых символов
            $reg_meta_wrong = $userdata[10];
//   Что-то вроде       '~[^абвгдеёжзийклмнопрстуфхцчшщъыьэюяqwertyuiopasdfghjklzxcvbnm\,\s_]~';

            if( preg_match($reg_meta_wrong, mb_convert_encoding(mb_strtolower($matches[1][0], $true_encoding), $userdata[3], $true_encoding)) ){
                $URL_wrong_keywords = $URL. '>'.  $matches[1][0];

                if (fwrite($userdata[8], mb_convert_encoding($URL_wrong_keywords, $userdata[3], $true_encoding). PHP_EOL) === FALSE) {
                     $message_to_user = 'В процессе формирования файла '. $userdata[9] .' с URL и соответствующими ключевыми словами, содержащими недопустимые символы, при записи строки произошла ошибка: Error6';
                    return array(1, $message_to_user);
                }
            }

            $URL_keywords = $URL. '>'.  mb_convert_encoding($matches[1][0], $userdata[3], $true_encoding);

            if (fwrite($userdata[1], $URL_keywords. PHP_EOL) === FALSE) {
                $message_to_user = 'В процессе формирования файла '. $userdata[6] .' с URL и соответствующими ключевыми словами при записи строки произошла ошибка: Error4';
                return array(1, $message_to_user);
            }

        }

    }else{ // Ссылка на файл есть в карте сайта, но его почему-то нет в каталогах сервера

        if (fwrite($userdata[4], $URL. PHP_EOL) === FALSE) { // Сохраняем ссылку на этот файл в соответствующем файле
            $message_to_user = 'В процессе формирования файла '. $userdata[7] .' с URL, которые есть в файле карты сайта, но которых нет на сервере, при записи строки произошла ошибка: Error3';
            return array(-1, $message_to_user);
        }

    }
    return array(1, '');
}

function check_enc($pathNAME, $text_html,  $encoding_mess, $internal_enc, $utf8, $cp1251){
    $true_encoding = '';

    if(mb_check_encoding($text_html, $utf8)){
        $true_encoding =  $utf8;
    }
    if (mb_check_encoding($text_html, $cp1251)){
        $true_encoding = $cp1251;
    }

    $reg_cyr = '/[а-яёА-ЯЁ№]/ui'; // Кириллический текст в кодировке этого файла, т.е. в utf-8

    if(preg_match($reg_cyr, $text_html)){
        $true_encoding = 'utf-8';
    }


    if($true_encoding === ''){
        die(mb_convert_encoding("Невозможно определить кодировку файла <br/>" . $pathNAME. '<br/>' , $encoding_mess, $internal_enc));
    }

return $true_encoding;
}

// Добавляем (обновляем) функции в файл check_keywords.js
function adding_functions($reg_meta_wrong, $id_tag_response){
    $script_filename_short = 'check_keywords.js';
    $script_filename_short_COPY = 'check_keywords_COPY.js';

    $script_filename = realpath(__DIR__ . '/../js/'. $script_filename_short);
    $script_filename_COPY = __DIR__ . '/../js/'. $script_filename_short_COPY;

    if(is_file($script_filename)){
        $script = @file_get_contents($script_filename);

        if($script != false){
            $replacement = 'function reg_meta_keywords_wrong() {
    return '.  $reg_meta_wrong .';
}';
            $script = preg_replace('~function\s+reg_meta_keywords_wrong\s*\(\)\s*\{\s*return[^}]*\}~', $replacement, $script);

            $replacement = 'function id_initial() {
    return "'.  $id_tag_response .'";
}';
            $script = preg_replace('~function\s+id_initial\s*\(\)\s*\{\s*return[^}]*\}~', $replacement, $script);

        }else{
            $message_to_user = 'Ошибка при создании файла '.$script_filename;
            return array(-1, $message_to_user, 500);
        }

            // проверка на ошибки
            if((error_get_last() != '') || (is_array(error_get_last()) && (error_get_last() != array()) )){
                $message_to_user = 'При вставке функций в файл .../FIND_keywords/js/check_keywords.js произошла ошибка. Возможно, он был поврежден.';
                return array(-1, $message_to_user, 500);
            }

clearstatcache();
        if(!is_file($script_filename_COPY) || filesize($script_filename) === filesize($script_filename_COPY)){
            if(@!copy($script_filename, $script_filename_COPY)){ // Запасаем копию файла (на всякий случай).
                $message_to_user = 'Ошибка сервера: не удалось сохранить копию файла .../FIND_keywords/js/'.$script_filename_short. '. Попробуйте повторить операцию еще раз. ';
                return array(-1, $message_to_user, 500);
            }

        }else{ // Если размер ранее сделанной копии не совпадает с размером исходного файла перед его изменением, значит, исходный файл был испорчен, поэтому ранее запасенную его копию оставляем неприкосновенной
            $message_to_user = 'Ошибка: размеры файлов .../FIND_keywords/js/'.$script_filename_short. ' и .../FIND_keywords/js/'. $script_filename_short_COPY. ' (копия) не совпадают, т.е., возможно, что '. $script_filename_short. ' был испорчен, возможно, в результате ошибки сервера. Или в него были внесены изменения вручную, о чем скрипт пока не знает. Поэтому его обновление не делалось. Восстановить этот файл можно из его копии, стерев в конце ее имени символы _COPY. Или следует удалить копию, если Вы уверены, что она - неактуальная.';
            return array(-1, $message_to_user, 500);
        }

        if(@file_put_contents($script_filename. '_TMP', $script)){ // Создаем временный файл js-скриптов
            if(@copy($script_filename. '_TMP', $script_filename)){
                @unlink($script_filename. '_TMP');
                    if(is_file($script_filename. '_TMP')){
                        $message_to_user = 'Ошибка: почему-то не удалился файл .../FIND_keywords/js/'. $script_filename_short. '_TMP';
                        return array(-1, $message_to_user, 500);
                    }
                return array(1);

            }else{
                $message_to_user = 'Ошибка при копировании файла .../FIND_keywords/js/'.$script_filename_short. '.  Попробуйте повторить операцию снова.';
                return array(-1, $message_to_user, 500);
            }


        }else{
            $message_to_user = 'Ошибка: файл .../FIND_keywords/js/'.$script_filename_short. ' не существует.';
            return array(-1, $message_to_user, 500);
        }



    }else{
        $message_to_user = 'Ошибка: файл .../FIND_keywords/js/'.$script_filename_short. ' не существует.';
        return array(-1, $message_to_user, 500);
    }


// Если все-таки дошли досюда, значит, операции не завершились корректно, была ошибка
    $message_to_user = 'Ошибка: при добавлении функций в файл  .../FIND_keywords/js/'. $script_filename_short. ' произошла неизвестная ошибка.';
    return array(-1, $message_to_user, 500);
}
