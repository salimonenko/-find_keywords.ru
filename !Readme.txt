The software is launched using the link http://find_keywords.ru/index.php 
Checked for PHP5.3, PHP8.0. 
The parameters for working are set in the keywords_parameters.php file.

This script allows you to search by keywords among the files of the site's web pages, links to which are provided in the site map (Sitemap.xml file). The keywords must be contained in the meta tags of each file of the form: <meta name="keywords" content="word, word1, word2"/> 
The directory with this software can be placed anywhere on a website, for example, in the root directory. Then one must add a line in the index.php file - a JS script, taking into account the location of this directory, for example:
<script type="text/javascript" src="/FIND_keywords/js/check_keywords.js"></script>

To speed up the work, for you not to have to review all the web pages of the site each time, when you click the "Create a keyword file based on Sitemap.xml" button, a URL_keywords.csv file is created on the secret subdomain "secret", containing lists of links to the site files and the corresponding keywords. When changing keywords in any of the files and/or when changing the URL of web pages, adding new ones or deleting existing ones, you need to create a new keyword file. That is, this is essentially indexing keywords by the URL of the corresponding web pages (among those present in the sitemap file). 
Once the keyword file is created, you can search by keywords. To do this, enter the search keywords separated by spaces in the search area. The search results will be most relevant, firstly, to the number of keywords found in the corresponding web pages and, secondly, to the order of the keywords in the search area. For example, if you enter the following keywords: newspaper car shock absorber the following links to web pages will be shown: 
    http://www.4846d.ru/raznoe/amortizatory.html (car, shock absorber, car) 
    http://www.4846d.ru/raznoe/dudaev.html (Dudaev, Dzhokhar, newspaper, video, media) 
    http://www.4846d.ru/raznoe/akkumulatory.html (battery, car, car) 
1. That is, first of all, links to those web pages will be shown, among the keywords (in meta tags) of which there is the largest number of the searched keywords. In this case, the web page, having among the keywords the words "newspaper car", i.e. http://www.4846d.ru/raznoe/amortizatory.html. 
2. Secondly, links to web pages will be shown, the keywords of which correspond to the specified (during the search) keywords in the order of their sequence in the query string. In this case, this is a web page that has the word "newspaper" among the keywords, i.e. http://www.4846d.ru/raznoe/dudaev.html. 
3. And, finally, after these links, all other links corresponding to the keywords will be shown (taking into account point 2). To be able to search for fuzzy matches, the metaphone() function of the PHP language is used. 

If the site is implemented not on files, as in this software, but on databases (as is customary at present), then the script will have to be slightly modified to be able to obtain the content of web pages of the corresponding URLs from databases (and not from files).


Запуск ПО производится при помощи ссылки http://find_keywords.ru/index.php
Проверена работа в PHP5.3, PHP8.0.
Параметры для работы задаются в файле keywords_parameters.php.

Этот скрипт позволяет делать поиск по ключевым словам среди файлов вебстраниц сайта, ссылки на которые приведены в карте сайта (файл Sitemap.xml). Ключевые слова должны содержаться в метатегах каждого из файлов вида: <meta name="keywords" content="слово, слово1, слово2"/>
Каталог с данным ПО можно разместить в любом месте сайта, например, в корневом каталоге. Затем в файле index.php добавить строчку - JS-скрипт, с учетом местоположения этого каталога, например:
<script type="text/javascript" src="/FIND_keywords/js/check_keywords.js"></script>

Для ускорения работы, чтобы каждый раз не просматривать заново все вебстраницы сайта, при нажатии на кнопку "Создать файл ключевых слов на основе Sitemap.xml" на секретном поддомене secret создается файл URL_keywords.csv, содержащий перечни ссылок на файлы сайта и соответствующие ключевые слова. При изменении ключевых слов в каком-либо из файлов и/или при изменении URL вебстраниц, добавлении новых или удалении имеющихся нужно создать файл ключевых слов заново. Т.е. это, по сути, индексация ключевых слов по URL соответствующих вебстраниц (среди тех, что присутствуют в файле карты сайта). 
Когда файл ключевых слов создан, можно делать поиск по ключевым словам. Для этого в области поиска нужно ввести искомые ключевые слова через пробелы. Результаты поиска будут наиболее релевантны, во-первых, количеству ключевых слов, встретившихся в соответствующих вебстраницах и, во-вторых, порядку следования ключевых слов в области поиска. Например, если ввести такие ключевые слова: 
газета машина амортизатор
будут показаны следующие ссылки на вебстраницы:
    http://www.4846d.ru/raznoe/amortizatory.html (автомобиль, амортизатор, машина)
    http://www.4846d.ru/raznoe/dudaev.html (Дудаев, Джохар, газета, видео, сми)
    http://www.4846d.ru/raznoe/akkumulatory.html (акумулятор, автомобиль, машина)
1. Т.е. в первую очередь будут показаны ссылки на те вебстраницы, среди ключевых слов (в метатегах) которых встречается наибольшее количество искомых ключевых слов. В данном случае вебстраница, имеющая среди ключевых слов слова "газета машина", т.е. http://www.4846d.ru/raznoe/amortizatory.html.
2. Во вторую очередь будут показаны ссылки на вебстраницы, ключевые слова которых соответствуют заданным (при поиске) ключевым словам в порядке их очередности в строке запроса. В данном случае это вебстраница, имеющая среди ключевых слов слово "газета", т.е. http://www.4846d.ru/raznoe/dudaev.html.
3. И, наконец, после этих ссылок будут показаны все остальные ссылки, соответствующие ключевым словам (с учетом п.2). 
Для возможности поиска по неточным совпадениям использована функция metaphone() языка РНР.

Если сайт реализован не на файлах, как в данном ПО, а на базах данных (как это принято в настоящее время), то скрипт придется немного доработать, чтобы можно было получать содержимое (контент) вебстраниц соответствующих URL из баз данных (а не из файлов).
