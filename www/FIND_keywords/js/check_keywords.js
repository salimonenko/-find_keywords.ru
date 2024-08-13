(function () {
var idr_const = id_initial();

var idr = idr_const;
    var i = 0;
        while (document.getElementById(idr_const + i++)){
            if(i > 1000){
                alert('Невозможно подобрать уникальный идентификатор, начинающийся с id='+idr_const+ '...');
                break;
            }
            idr = idr_const + i;
        }



    var div = document.createElement('div');
    div.id = idr;
    div.style.cssText = 'border: solid 2px blue; background-color: #f1f1f1; line-height: 120%; max-width: 30%; display: inline-block; margin: 2px 0 0 50px; max-height: 80vh; min-height: 160px';
    document.body.appendChild(div);

    var div0 = document.createElement('div');

<!--   КНОПКА ДЛЯ СОЗДАНИЯ ФАЙЛА КЛЮЧЕВЫХ СЛОВ (там строки вида   URL; keyword1, keyword2,...   -->
        div0.innerHTML = '<div title="Создать файл ключевых слов - на основе файлов html-страниц, URL которых содержатся в Sitemap.xml и в которых есть раздел <head>...</head>" class="finding_words"><div class="finding_words-border-radius-8px string_find" style="position: relative; "><div class="" ><div>Создать файл ключевых слов на основе <span style="margin-top: 0px; display: inline; line-height: 50%;">Sitemap.xml</span></div></div></div></div>';
        div.title = "Создать файл ключевых слов - на основе файлов html-страниц, URL которых содержатся в Sitemap.xml и в которых есть раздел <head>...</head>";
        div0.children[0].onclick = DO_URL_keywords;
        div.appendChild(div0);
<!--   КНОПКА ДЛЯ ФОРМИРОВАНИЯ И ВЫВОДА НА ЭКРАН ССЫЛОК НА СТАТЬИ, НАЙДЕННЫЕ ПО КЛЮЧЕВЫМ СЛОВАМ   -->
        var div01 = document.createElement('div');
        div01.setAttribute('class', 'finding_words');
        div01.title = 'Найти релевантные статьи по ключевым словам';
        div01.innerHTML = '<div  class="finding_words-border-radius-8px string_find" ><div class="" ><div>Найти статьи по ключевым словам <span style="margin-top: 0; display: inline; line-height: 50%;"></span></div></div></div>';
        div01.onclick = SHOW_keywords_links;


        div0.appendChild(div01);

        var textarea = document.createElement('textarea');
        textarea.style.cssText = 'width: 220px; height: 66px;  overflow-wrap: normal; word-wrap: normal; word-break: break-all; line-break: auto;  display: block; vertical-align: top;';
        textarea.setAttribute('placeholder', "Введите ключевые слова...  например: газета машина амортизатор");
        div0.appendChild(textarea);

// Если перед обновлением страницы производился поиск по ключевым словам, то они будут помещены в textarea
            var finded_keywords = sessionStorage.getItem('finded_keywords');
            if(finded_keywords){
                textarea.value = finded_keywords;
            }

        var div1 = document.createElement('div');
        div1.innerHTML = '&nbsp;';
        div.appendChild(div1);




get_FIND_keywords_CSS(idr); // Подгружаем файл стилей css

// Подгружаем файл стилей css
function get_FIND_keywords_CSS(idr) {
/* Каталоги js, php, css ДОЛЖНЫ находиться в одном и том же родительском каталоге!
 */
    var script = document.currentScript;
    var this_script_path = script.src;

    var url = new URL(this_script_path);
    var pathname = url.pathname;

    pathname = pathname.replace(/\/js\/.+$/, ''); // Путь к родительскому каталогу
    document.getElementById(idr).children[0].children[0].setAttribute('data-srs', pathname);// Запасаем src текущего скрипта в кнопке запуска этой функции

    document.head.insertAdjacentHTML(
        'beforeend',
        '<link rel="stylesheet" href="'+ pathname + '/css/finding_words.css" />');
    return pathname;
}


function DO_URL_keywords() {

    idr = this.parentNode.parentNode.id;

    var result = confirm('Действительно создать файл ключевых слов для ВСЕХ URL, имеющихся в Sitemap.xml и файлы которых имеют раздел <head>...</head> ?');
    if (result) {
        try{
            var path = document.getElementById(idr).children[0].children[0].getAttribute('data-srs');

        sending('to_do=DO_URL_keywords', ':'+ port_number()+ path +'/php/getter.php?'+'random='+Math.random(), idr, false); // Запускаем создание файла ключевых слов для html-страниц сайта
            }catch (er){
            alert('Произошла ошибка, поэтому формирование списка ключевых слов невозможно. См. также сообщение в консоли.');
            console.log(er)
        }

    } else {

    }
}


function SHOW_keywords_links() {

    idr = this.parentNode.parentNode.id;
    var word = document.getElementById(idr).getElementsByTagName('textarea')[0].value;
    word = word.toLowerCase();

// Сохраняем введенные слова в сессии браузера, чтобы восстановить их при обновлении страницы (до закрытия браузера)

//    alert(sessionStorage.getItem('finded_keywords'));
/*    var finded_keywords = sessionStorage.getItem('finded_keywords');

    if(!word && finded_keywords){
        document.getElementById(idr).getElementsByTagName('textarea')[0].value = finded_keywords;
    }*/

    sessionStorage.setItem('finded_keywords', word);

    if (word.length > 30 || word.length < 3) {
        alert('Можно ввести не менее 3 и не более 30 символов в ключевых словах');
        return;
    }

    var reg = reg_meta_keywords_wrong();
//    if(/[^абвгдеёжзийклмнопрстуфхцчшщъыьэюяqwertyuiopasdfghjklzxcvbnm\,\s_0-9\-\+]/.test((word)))
    if (reg.test(word)) {
        alert(' Разрешается ввести только следующие символы: цифры, пробелы, русские, латинские буквы и символы _ , - +');
        return;
    }

    try {
        var path = document.getElementById(idr).children[0].children[0].getAttribute('data-srs');

        var result = confirm('Найти статьи по выбранным вами ключевым словам?');
        if (result) {
            word = encodeURIComponent(word);

            sending('to_do=find_links&word=' + word, ':' + port_number() + path + '/php/getter.php?' + 'random=' + Math.random(), 'response', false); // Запускаем создание файла ключевых слов для html-страниц сайта
        }
    } catch (er) {
        alert('Произошла ошибка, поэтому поиск по ключевым словам невозможен. См. также сообщение в консоли.');
        console.log(er)
    }
}

function port_number() {
    var protocol = window.location.protocol.replace(':', '').toLowerCase();
    var port = window.location.port;
    if(!port) {
        if (protocol === 'http') {
            port = 80;
        }
        if (protocol === 'https') {
            port = 443;
        }
    }
return port;
}


function sending(body, prog_PHP, DIV_id, flag_do_something) {
//            document.getElementById('response').className=''; // Предварительно очищаем класс
            var docum_location = window.location.protocol+"//"+document.location.hostname;
            var xhr = new XMLHttpRequest();

            xhr.open("POST", docum_location+prog_PHP, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function xhr_state() { // (3)
                if (xhr.readyState != 4) return;

                var xhr_status = xhr.status;
                if (xhr_status == 200 || xhr_status === 201) {
                    if(xhr.responseText != 1){
                        var ankor = '/*do_something*/';
                        if(xhr.responseText.substr(0, ankor.length) != ankor){
                            if(flag_do_something == true){ // Добавлять содержимое в блок или переписывать его заново
                            document.getElementById(DIV_id).children[1].innerHTML += xhr.responseText;
                            }else{
                                document.getElementById(DIV_id).children[1].innerHTML = xhr.responseText;
                            }
                        }else{
                        var y = document.createElement('script'); // новый тег SCRIPT
                        y.defer = true; //Даём разрешение на исполнение скрипта после его "приживления" на странице
                        y.text = xhr.responseText; //Записываем полученный от сервера "набор символов" как JS-код
                        document.body.appendChild(y);
                        }
//                        After_xhr(body, DIV_id, xhr.responseText); // После получения сообщения выполняем другие действия

                    }else{
                        alert('Ошибка загрузки ...');  //location.reload();
                    }
                } else {
                    if(xhr_status == 400 || xhr_status == 500){
                        document.getElementById(DIV_id).children[1].innerHTML = xhr.responseText;
                    }

                    alert('xhr error '+xhr.statusText);
                }
            };
            xhr.send(body);
            return false;
        }


/* Эти функции формируются сервером (при помощи PHP) */
/* Theese functions done by PHP */

function reg_meta_keywords_wrong() {
    return /[^абвгдеёжзийклмнопрстуфхцчшщъыьэюяqwertyuiopasdfghjklzxcvbnm\,\s_0-9\-\+]/;
}

function id_initial() {
    return "response";
}

})();
