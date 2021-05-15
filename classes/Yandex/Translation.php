<?php

/**
 * Yandex auto translate text.
 * 
 * @author info@ensostudio.ru
 * @license MIT
 * @link http://api.yandex.ru/translate/ Yandex translate service
 * @link http://legal.yandex.ru/translate_api/ Terms of use API
 */
abstract class Yandex_Translation
{
	/**
	 * @var string Yandex translate URI
	 */
	public const YA_URI = 'https://translate.yandex.net/api/v1.5/tr.json/';

	/**
	 * @var array API error code => human readable name
	 */
	protected static $errors = [
        401 => 'API key invalid',
        402 => 'API key blocked',
        403 => 'Exceeded the daily limit on the number of requests',
        404 => 'Exceeded the daily limit on the amount of translated text',
        413 => 'Exceeds the maximum size of the text',
        422 => 'The text can not be translated',
        501 => 'Set direction of translation is not supported',
	];

	/**
	 * @var string Yandex [API-key](http://api.yandex.ru/key/form.xml?service=trnsl)
	 */
	public static $key;

	/**
	 * Path to certificate `*.crt` file. Certificate chain in the format pem.
	 * 
	 * [!!] You can then use a command simiar to this to translate your 
	 * apache certificate into one that curl likes.
	 * `$ openssl x509 -in server.crt -out outcert.pem -text`
	 * 
	 * @var string
	 */
	public static $certificate;

	/**
	 * @var string Target language. If is empty, used `I18n::$lang` for detect.
	 */
	public static $to;

	/**
	 * @var string Source language. If is empty, used Yandex auto detect.
	 */
	public static $from;

	/**
	 * @var string Regex pattern, uses for find variables in text.
	 */
	public static $var_tag = '~:(\w+)~';

	/**
	 * Request to Yandex API
	 * 
	 * @param string  $uri   URI for detect method
	 * @param array   $data  Request POST params
	 * @return array
	 * @throw RuntimeException
	 */
	protected static function _api_request(string $uri, array $data = []): array
	{
		// Check API key and certificate
		if (!self::$key) {
			throw new RuntimeException('API key is empty');
		}
		if (self::$certificate) {
			throw new RuntimeException('Path to API certificate is empty');
		}

		$data['key'] = self::$key;
		
		// Create and execute request to API
		$request = Request::factory(self::YA_URI . $uri);

		$request
			->client()
			->options(array(
				CURLOPT_RETURNTRANSFER => TRUE, 
				CURLOPT_SSL_VERIFYPEER => TRUE, 
				CURLOPT_CAINFO => self::$certificate,
			));

		$result = $request->method(Request::POST)
			->post($data)
			->execute()
			->body();

		$result = json_decode($result, TRUE);
		
		// Check request API errors
		if (!isset($result['code']) || $result['code'] !== 200) {
			throw new RuntimeException("API method {$uri}: " . self::$_errors[$result['code']]));
		}

		unset($result['code']);

		return $result;
	}

	/**
	 * Returns a list of language supported by the service. If `$ui` specified, then 
	 * the service response will be added to the list of language codes and their 
	 * corresponding names of languages: en (English), ru (Russian), tr (Turkish), uk (Ukrainian).
	 *
	 * @param string|null  $ui  User interface language
     * @param string[] $languages
	 * @return array
	 * @throw RuntimeException
	 */
	protected static function _langs(string $ui = null, array $languages = ['en', 'ru', 'tr', 'uk']): array
	{
		// Check ui value
		if ($ui !== null && ! in_array($ui, $languages)) {
			throw new RuntimeException("Language {$ui} not supported");
		}

		return self::_api_request('getLangs', $ui ? ['ui' => $ui] : []);
	}

	/**
	 * Transforms the [ROT13](http://wikipedia.org/wiki/ROT13) over row for save variables tags at translate.
	 * 
	 * @param string $text
	 * @return string
	 */
	protected static function _rot13(string $text): string
	{
		return preg_replace_callback(
			self::$var_tag,
            static function (array $match) {
                return str_rot13($match[1]);
            },
			$text
		);
	}

	/**
	 * Auto translates text.
	 * 
	 * @param string|string[] $text
	 * @param string $to
	 * @param string|null $from
	 * @param bool $html
	 * @return string|string[]

	 */
	public static function translate($text, string $to, string $from = null, bool $html = false)
	{
		if (!$text) {
			return $text;
		}

		$text = self::_rot13($text);
		
		$result = self::_api_request(
			'translate', 
			array(
				'lang'    => ($from ? $from . '-' : '') . $to,
				'text'    => is_array($text) ? implode('#b#r#', array_values($text)) : $text,
				'format'  => $html ? 'html' : 'plain',
				'options' => (empty($from) || empty($to)) ? 1 : 0,
			)
		);

		$result = implode('', array_column($result, 'text'));
		$result = self::_rot13($result);

		if (!is_array($text)) {
			return $result;
		}
		if ($result) {
			return array_combine(array_keys($text), explode('#b#r#', $result));
		}
	}

	/**
	 * Specifies the language in which is written the specified text.
	 * 
	 * @param string|string[] $text
	 * @param bool $html
	 * @return string
	 */
	public static function detect($text, bool $html = FALSE)
	{
		$result = self::_api_request(
			'detect', 
			[
				'text' => is_array($text) ? implode($text) : $text, 
				'format' => $html ? 'html' : 'plain',
			]
		);

		return Arr::get($result, 'lang');
	}

	/**
	 * Returns a list of supported translation directions.
	 * 
	 * @return array
	 */
	public static function direction(): array
	{
		return array_columns(static::_langs(), 'dirs');
	}

	/**
	 * Returns a list of language supported by the service. 
	 * 
	 * @param string $ui User interface language
	 * @return array
	 */
	public static function langs(string $ui): array
	{
		return array_columns(static::_langs($ui), 'langs');
	}
}
