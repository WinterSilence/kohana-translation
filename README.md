### Translation module for Kohana framework 3.3

Auto translate, detect language.
API offers text translation function for more than 20 language pairs.

Uses Yandex API http://api.yandex.ru/translate/
Terms of use http://legal.yandex.ru/translate_api/

### Setup:

To use, download the source, extract and rename to `translation`. 
Move that folder into your modules directory, activate in your bootstrap,
set `Translation::$key` - http://api.yandex.ru/key/form.xml?service=trnsl 
and `Translation::$certificate` - path to certificate *.crt file
(certificate chain in the format pem).

### Usage:

Configure:
<pre>
Translation::$key = 'trnsl.1 .. 32';
Translation::$certificate = APPPATH.'YandexTranslate.crt';

Translation::$to = 'fr';
Translation::$from = 'en';
</pre>

Translate:
<pre>
$i18n = Kohana::load('... i18n/ru.php');
$i18n = Translation::translate($i18n, 'ru', 'en', TRUE);
</pre>

Language detect:
<pre>
echo Translation::detect('тест <b>тост</b>', TRUE);
</pre>

Translate direction:
<pre>
$dirs = Translation::direction();
</pre>

Translated language list:
<pre>
$langs = Translation::langs('ru');
</pre>