## Translation module for Kohana framework 3.3

Auto translate, detect language.
API offers text translation function for more than 20 language pairs.

Uses Yandex API (http://api.yandex.ru/translate/) .
Terms of use (http://legal.yandex.ru/translate_api/) .

Current version can:
<ul>
  <li>Translate text and html</li>
  <li>Detect text and html language</li>
  <li>Translated languages list</li>
  <li>Translate direction list</li>
  <li>Cached requests to API</li>
</ul>

Planned add:
<ul>
  <li>Translate i18n/messages files</li>
  <li>Update and expand caching</li>
  <li>Support others translation services</li>
</ul>

### Setup

To use, download the source, extract and rename to `translation`. 
Move that folder into your modules directory, activate in your bootstrap,
set `Translation::$key` - (http://api.yandex.ru/key/form.xml?service=trnsl) 
and `Translation::$certificate` - path to certificate *.crt file
(certificate chain in the format pem).

### Usage

Configure:
~~~
Translation::$key = 'trnsl.1 .. 32';
Translation::$certificate = APPPATH.'YandexTranslate.crt';

Translation::$to = 'fr';
Translation::$from = 'en';
~~~

Translate:
~~~
$i18n = Kohana::load('... i18n/ru.php');
$i18n = Translation::translate($i18n, 'ru', 'en', TRUE);
~~~

Language detect:
~~~
echo Translation::detect('тест <b>тост</b>', TRUE);
~~~

Translate direction:
~~~
$dirs = Translation::direction();
~~~

Translated language list:
~~~
$langs = Translation::langs('ru');
~~~
