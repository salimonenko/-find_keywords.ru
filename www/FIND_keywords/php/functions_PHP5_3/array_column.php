<?php

if(!defined('perform') || (perform !== '123')) {die("<span style='font-weight:bold'>Error: Access forbidden.</span>");}

function array_column($array,$column_name){  // В РНР5.3 (Denwer) нет функции array_column, поэтому эмулируем ее

    return array_map(function($element) use($column_name){
		return 	isset($element[$column_name])? $element[$column_name] : null; }, $array);
}
				
 