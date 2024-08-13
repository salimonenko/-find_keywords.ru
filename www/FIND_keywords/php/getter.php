<?php

header('Content-Type: text/html; charset=utf-8');

/* Установка внутренней кодировки этого скрипта в UTF-8 */
mb_internal_encoding("UTF-8");
$internal_enc = mb_internal_encoding();
mb_regex_encoding("utf-8");


define('perform', '123');

// Предварительно, для безопасности:
$reg_meta_wrong = '';
$path_php = '/php/';
$path_php5_3 = '';
$path0_abs = '';
$path =  '';
$path0 =  '';
$path1 = '';
$path2 = '';
$max_len_Arr = array();
$id_tag_response = '';

$path_keywords_parameters = "keywords_parameters.php";
    @(include_once $path_keywords_parameters) or die('Отсутствует подключаемый файл '. $path_keywords_parameters);

    if(!$reg_meta_wrong || !$path_php5_3 || !$path || !$path0 || !$path1 || !$path2){ // Проверка на всякий случай
        die('Файл '. $path_keywords_parameters. ' подключен неправильно или содержит пустые значения некоторых параметров. ');
    }


$path_SHOW_keywords_links = "SHOW_keywords_links.php";
    @(include_once $path_SHOW_keywords_links) or die('Отсутствует подключаемый файл '. $path_SHOW_keywords_links);

if (!function_exists('http_response_code')){
$path_http_response_code = $path_php5_3. "http_response_code.php";
    @(include_once $path_http_response_code) or die('Отсутствует подключаемый файл '. $path_http_response_code);
}

$path_DO_quick_tags = "DO_quick_tags.php";
    @(include_once $path_DO_quick_tags) or die('Отсутствует подключаемый файл '. $path_DO_quick_tags);


// ****************************************************************************************
$rez = array();
$code = 400;
// Не выполнено ни одной операции (если параметр to_do задан неверно). Возможно - подделка запроса
$err_mes = 'Не выполнено ничего, т.к. параметр to_do задан неверно. Возможно - подделка запроса или браузер функционирует неправильно.';

// Что выполнить?...
// 1. Поиск файлов по ключевым словам
    if(isset($_REQUEST['to_do']) && $_REQUEST['to_do'] === 'find_links'){
        $rez = SHOW_keywords_links($internal_enc, $internal_enc, $reg_meta_wrong, $path_php, $path_php5_3, $path, $path0_abs, $max_len_Arr);
    }

// 2. ....
    if(isset($_REQUEST['to_do']) && $_REQUEST['to_do'] === 'DO_URL_keywords'){
        $rez = DO_quick_tags($internal_enc, $internal_enc, $reg_meta_wrong, $path0_abs, $path, $path1, $path2, $id_tag_response);
    }

// ...


        $err_mes = $rez[1];
        $code = $rez[2];

        http_response_code($code);
        die($err_mes );

// ****************************************************************************************
