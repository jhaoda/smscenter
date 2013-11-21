<?php

/**
 * Библиотека для работы с сервисом smsc.ru (SMS-Центр)
 *
 * Функции:
 *	— отправка сообщений
 *	— проверка статуса сообщений
 *	— получение стоимости рассылки
 *	— проверка баланса
 *	— получение информации об операторе
 *
 * Функциональность параметра <b>list<b> пока не реализована.
 *
 * Настройки передаются при создании экзепляра класса в виде массива.
 * Если формат ответа сервера = FMT_JSON, то ответ сервера принудительно конвертируется в utf-8.
 *
 * Примеры использования:
 * <pre>
 *	$smsc = new SMSCenter('ivan', md5('ivanovich'), [
 *		'charset' => SMSCenter::CHARSET_UTF8
 *	]);
 *
 *	// Отправка сообщения
 *	$smsc->send('7991111111', 'Превед, медведы!', 'SuperIvan');
 *
 *	// Отправка сообщения на 2 номера
 *	$smsc->send(array('+7(999)1111111', '+7(999)222-22-22'), 'Превед, медведы! Одно сообщение на 2 номера.', 'SuperIvan');
 *
 *	// Получение баланса
 *	echo $smsc->getBalance(), ' руб.'; // "72.2 руб." при FMT_JSON
 *
 *	// Получение информации об операторе
 *	$smsc->getOperatorInfo('7991111111');
 *
 *	// Получения статуса сообщения
 *	$smsc->getStatus('+7991111111', 6, SMSCenter::STATUS_INFO_EXT);
 *
 *	// Получение стоимости рассылки
 *	$smsc->getCost('+7991111111', 'Начало около 251 млн лет, конец — 201 млн лет назад, длительность около 50 млн лет.');
 *
 *	// Проверка тарифной зоны
 *	if ($sms->getChargingZone('+7(999)1111111') == self::ZONE_RU) {...}
 * </pre>
 *
 * @version 2.0.0-dev
 * @author JhaoDa <jhaoda@gmail.com>
 * @link https://github.com/jhaoda/SMSCenter
 * @license http://www.apache.org/licenses/LICENSE-2.0
 *
 * Copyright 2013 JhaoDa
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace JhaoDa\SMSCenter;

class SMSCenter implements \ArrayAccess {
	const VERSION = '2.0.0-dev';

	const MSG_SMS   = 0;
	const MSG_FLASH = 1;
	const MSG_WAP   = 2;
	const MSG_HLR   = 3;
	const MSG_BIN   = 4;
	const MSG_HEX   = 5;
	const MSG_PING  = 6;

	const COST_NO      = 0;
	const COST_ONLY    = 1;
	const COST_TOTAL   = 2;
	const COST_BALANCE = 3;

	const TRANSLIT_NONE = 0;
	const TRANSLIT_YES  = 1;
	const TRANSLIT_ALT  = 2;

	const FMT_PLAIN     = 0;
	const FMT_PLAIN_ALT = 1;
	const FMT_XML       = 2;
	const FMT_JSON      = 3;

	const STATUS_PLAIN    = 0;
	const STATUS_INFO     = 1;
	const STATUS_INFO_EXT = 2;

	const CHARSET_UTF8 = 'utf-8';
	const CHARSET_KOI8 = 'koi8-r';
	const CHARSET_1251 = 'windows-1251';

	const ZONE_RU  = 10;
	const ZONE_UA  = 20;
	const ZONE_SNG = 30;
	const ZONE_1   = 1;
	const ZONE_2   = 2;
	const ZONE_3   = 3;

	private $login;
	private $password;
	private $useSSL;
	private $options = [];
	private $types = [0 => '', 1 => 'flash=1', 2 => 'push=1', 3 => 'hlr=1', 4 => 'bin=1', 5 => 'bin=2', 6 =>'ping=1'];

	private static $curl = null;

	/**
	 * Инициализация.
	 *
	 * @access public
	 *
	 * @param string $login    логин
	 * @param string $password пароль или MD5-хэш пароля
	 * @param bool   $useSSL   использовать HTTPS
	 * @param array  $options  прочие параметры
	 *
	 * @return \JhaoDa\SMSCenter\SMSCenter
	 */
	public function __construct($login, $password, $useSSL = false, array $options = []) {
		$this->login = $login;
		$this->password = $password;
		$this->useSSL = $useSSL;

		$default = [
			'charset' => self::CHARSET_UTF8,
			'fmt'     => self::FMT_JSON,
		];

		$this->options = array_merge($default, $options);
	}

	/**
	 * Отправить сообщение.
	 *
	 * @access public
	 *
	 * @param string|array $phones  Номера телефонов
	 * @param string       $message Тест сообщения
	 * @param string       $sender  Имя отправителя
	 * @param array        $options Дополнительные параметры
	 *
	 * @throws \InvalidArgumentException если список телефонов пуст или длина сообщения больше 800 символов
	 * @return bool|string|\stdClass Результат выполнения запроса в виде строки, объекта (FMT_JSON) или false в случае ошибки.
	 */
	public function send($phones, $message, $sender = null, array $options = []) {
		if (empty($phones)) {
			throw new \InvalidArgumentException("The 'phones' parameter is empty.");
		} else {
			if (is_array($phones)) {
				$phones = array_map(__CLASS__.'::clearPhone', $phones);
				$phones = implode(';', $phones);
			} else {
				$phones = self::clearPhone($phones);
			}
		}

		if ($message !== null && empty($message)) {
			throw new \InvalidArgumentException('The message is empty.');
		} elseif (mb_strlen($message, 'UTF-8') > 800) {
			throw new \InvalidArgumentException('The maximum length of a message is 800 symbols.');
		}

		$options['phones'] = $phones;
		$options['mes']    = $message;

		if ($sender !== null) {
			$options['sender'] = $sender;
		}

		return $this->sendRequest('send', $options);
	}

	/**
	 * Отправить разные сообщения на несколько номеров.
	 *
	 * @access public
	 *
	 * @param array  $list    Массив [номер => сообщение] или [номер, сообщение]
	 * @param string $sender  Имя отправителя
	 * @param array  $options Дополнительные параметры
	 *
	 * @return bool|string|\stdClass Результат выполнения запроса в виде строки, объекта (FMT_JSON) или false в случае ошибки.
	 */
	public function sendMulti(array $list, $sender = null, array $options = []) {
		foreach ($list as $key => $value) {
			if (is_array($value)) {
				$options['list'][] = self::clearPhone($value[0]) . ':' . str_replace("\n", '\n', $value[1]);
			} else {
				$options['list'][] = self::clearPhone($key) . ':' . str_replace("\n", '\n', $value);
			}
		}

		$options['list'] = implode("\n", $options['list']);

		if ($sender !== null) {
			$options['sender'] = $sender;
		}

		return $this->sendRequest('send', $options);
	}

	/**
	 * Проверка номеров на доступность в реальном времени.
	 *
	 * @access public
	 *
	 * @param string|array $phones Номера телефонов
	 *
	 * @return bool|string|\stdClass Результат выполнения запроса в виде строки, объекта (FMT_JSON) или false в случае ошибки.
	 */
	public function pingPhone($phones) {
		return $this->send($phones, null, null, ['type' => self::MSG_PING]);
	}

	/**
	 * Получение стоимости рассылки.
	 *
	 * @access public
	 *
	 * @param string|array $phones Номера телефонов
	 * @param string       $message Тест сообщения
	 * @param array        $options Дополнительные опции
	 *
	 * @return bool|string|\stdClass Стоимость рассылки в виде строки, объекта (FMT_JSON) или FALSE в случае ошибки.
	 */
	public function getCost($phones, $message, array $options = []) {
		$options['cost'] = self::COST_ONLY;

		return $this->send($phones, $message, null, $options);
	}

	/**
	 * Получение стоимости рассылки разные сообщения на несколько номеров.
	 *
	 * @access public
	 *
	 * @param array $list    Массив [номер => сообщение] или [номер, сообщение]
	 * @param array $options Дополнительные опции
	 *
	 * @return bool|string|\stdClass Стоимость рассылки в виде строки, объекта (FMT_JSON) или FALSE в случае ошибки.
	 */
	public function getCostMulti(array $list, array $options = []) {
		$options['cost'] = self::COST_ONLY;

		return $this->sendMulti($list, null, $options);
	}

	/**
	 * Получение статуса сообщения.
	 *
	 * @access public
	 *
	 * @param string     $phone Номер телефона
	 * @param int|string $id    Идентификатор сообщения
	 * @param int        $mode  Вид ответа: обычный, полный, расширеный
	 *
	 * @return bool|string|\stdClass Статус сообщения в виде строки, объекта (FMT_JSON) или false в случае ошибки.
	 */
	public function getStatus($phone, $id, $mode = self::STATUS_PLAIN) {
		return $this->sendRequest('status', [
			'phone' => $phone,
			'id'    => (int)$id,
			'all'   => (int)$mode,
		]);
	}

	/**
	 * Получение информации об операторе: название и регион регистрации номера абонента.
	 *
	 * @access public
	 *
	 * @param string $phone Номер телефона
	 *
	 * @return bool|string|\stdClass Информация об операторе в виде строки, объекта (FMT_JSON) или false в случае ошибки.
	 */
	public function getOperatorInfo($phone) {
		return $this->sendRequest('info', [
			'get_operator' => '1',
			'phone'        => $phone
		]);
	}

	/**
	 * Запрос баланса.
	 *
	 * @access public
	 *
	 * @param int $format Формат ответа сервера (self::FMT_JSON)
	 *
	 * @return string Баланс в виде строки или false в случае ошибки.
	 */
	public function getBalance($format = self::FMT_JSON) {
		$ret = $this->sendRequest('balance', ['fmt' => $format]);

		if ($format == self::FMT_JSON) {
			return $ret->balance;
		} elseif ($format == self::FMT_XML) {
			return preg_replace('~</*balance>~', '', $ret);
		} else {
			return $ret;
		}
	}

	/**
	 * Определение тарифной зоны.
	 *
	 * @param string $phone Номер телефона
	 *
	 * @return int Номер тарифной зоны (константы self::ZONE_*)
	 */
	public function getChargingZone($phone) {
		$patterns = [
			self::ZONE_SNG => '~^\+?(7940|374|375|995|77|996|370|992|993|998)~',
			self::ZONE_RU  => '~^\+?(79|73|74|78)~',
			self::ZONE_UA  => '~^\+?380~',
			self::ZONE_1   => '~^\+?(994|213|244|376|54|93|880|973|591|387|58|84|241|233|502|852|299|20|972|91|92|62|962|964|98|353|354|855|237|1|254|357|57|242|506|965|856|231|423|352|261|389|60|960|356|52|976|971|595|503|966|381|65|421|386|66|255|216|598|63|385|382|56|94|593|372|27|1876|81)~',
			self::ZONE_2   => '~^\+?(44|359|30|45|86|53|371|373|48|886|358|420|82)~'
		];

		$phone = $this->clearPhone($phone);

		foreach($patterns as $key => $value) {
			if (preg_match($value, $phone)) {
				return $key;
			}
		}

		return self::ZONE_3;
	}

	/**
	 * Самая умная функция.
	 *
	 * @access private
	 *
	 * @param string $resource
	 * @param array  $options
	 *
	 * @throws \InvalidArgumentException
	 *
	 * @return bool|string|\stdClass
	 */
	private function sendRequest($resource, array $options) {
		$options = array_merge($this->options, $options);

		if (in_array($resource, ['status', 'info'])) {
			if (isset($options['phone']) && !empty($options['phone'])) {
				$options['phone'] = self::clearPhone($options['phone']);
			} else {
				throw new \InvalidArgumentException("The 'phone' parameter is empty.");
			}
		}

		$params = [
			'login='.urlencode($this->login),
			'psw='.urlencode($this->password)
		];

		foreach ($options as $key => $value) {
			switch ($key) {
				case 'type':
					$value = (int)$value;
					if ($value > 0 && $value < 7) {
						$params[] = $this->types[$value];
					}
					break;
				default:
					if (!empty($value)) {
						$params[] = $key . '=' . urlencode($value);
					}
			}
		}

		$i = 0;
		do {
			(!$i) || sleep(2);
			$ret = $this->execRequest($resource, $params);
		} while ($ret == '' && ++$i < 3);

		if (($resource == 'info' || $resource == 'status') && $options['fmt'] == self::FMT_JSON) {
			if ($options['charset'] == self::CHARSET_1251) {
				$ret = mb_convert_encoding($ret, 'UTF-8', 'WINDOWS-1251');
			} elseif ($options['charset'] == self::CHARSET_KOI8) {
				$ret = mb_convert_encoding($ret, 'UTF-8', 'KOI8-R');
			}
		}

		if (!empty($ret)) {
			return $options['fmt'] == self::FMT_JSON ? json_decode($ret) : $ret;
		} else {
			return false;
		}
	}

	/**
	 * Непосредственно выполнение запроса.
	 *
	 * @access private
	 *
	 * @param string $resource
	 * @param array  $params
	 *
	 * @return string Ответ сервера
	 */
	private function execRequest($resource, array $params) {
		$url = ($this->useSSL ? 'https' : 'http') . '://smsc.ru/sys/'.$resource.'.php';
		$post = implode('&', $params);

		if (function_exists('curl_init')) {

		} else {
			$context = stream_context_create([
				'http' => [
					'method' => 'POST',
					'header' => "Content-type: application/x-www-form-urlencoded\r\n",
					'content' => $post,
					'timeout' => 15,
				],
			]);

			$response = file_get_contents($url, false, $context);
		}


//		$isPOST = ($this['method'] == self::METHOD_POST) || (strlen($request) > 2000);
//
//		if (function_exists('curl_init')) {
//			// пробуем через curl
//			if (!self::$curl) {
//				self::$curl = curl_init();
//				curl_setopt(self::$curl, CURLOPT_RETURNTRANSFER, true);
//				curl_setopt(self::$curl, CURLOPT_CONNECTTIMEOUT, 5);
//				curl_setopt(self::$curl, CURLOPT_TIMEOUT, 5);
//				curl_setopt(self::$curl, CURLOPT_SSL_VERIFYPEER, 0);
//			}
//
//			if ($isPOST) {
//				list($url, $query) = explode('?', $request, 2);
//				curl_setopt(self::$curl, CURLOPT_POST, true);
//				curl_setopt(self::$curl, CURLOPT_POSTFIELDS, $query);
//				curl_setopt(self::$curl, CURLOPT_URL, $url);
//			} else {
//				curl_setopt(self::$curl, CURLOPT_URL, $request);
//			}
//
//			$ret = curl_exec(self::$curl);
//		} elseif (function_exists('fsockopen')) {
//			$m = parse_url($request);
//
//			if ($this['mode'] == self::SCHEME_HTTPS) {
//				if (extension_loaded('openssl')) {
//					$fp = fsockopen('ssl://'.$m['host'], 443, $errno, $errstr, 10);
//				} else {
//					throw new \RuntimeException('Can not perform HTTPS request. OpenSSL extension not loaded.');
//				}
//			} else {
//				$fp = fsockopen($m['host'], 80, $errno, $errstr, 10);
//			}
//
//			if ($fp) {
//				stream_set_timeout($fp, 2);
//
//				fwrite($fp, ($isPOST ? "POST $m[path]" : "GET $m[path]?$m[query]").
//						" HTTP/1.1\r\nHost: smsc.ru\r\nUser-Agent: PHP".
//						($isPOST ? "\r\nContent-Type: application/x-www-form-urlencoded\r\nContent-Length: ".strlen($m['query']) : "").
//						"\r\nConnection: Close\r\n\r\n".($isPOST ? $m['query'] : ""));
//
//				while (!feof($fp))
//					$ret .= fgets($fp, 1024);
//
//				list(, $ret) = explode("\r\n\r\n", $ret, 2);
//
//				fclose($fp);
//			} else {
//				throw new \RuntimeException($errstr, $errno);
//			}
//		} else {
//			if ($isPOST) {
//				throw new \Exception('file_get_contents can not send data over POST.');
//			}
//
//			$ret = file_get_contents($request);
//		}

		return $response;
	}

	/**
	 * Удаляет из номера любые символы, кроме цифр.
	 *
	 * @static
	 * @access public
	 *
	 * @param $phone
	 *
	 * @return string
	 */
	public static function clearPhone($phone) {
		return preg_replace('~[^\d+]~', '', $phone);
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetSet($offset, $value) {
		$this->options[$offset] = $value;
	}

	/**
	 * {@inheritdoc}
	 * @throws \InvalidArgumentException if the offset is not exists
	 */
	public function offsetGet($offset) {
		if (!array_key_exists($offset, $this->options)) {
			throw new \InvalidArgumentException(sprintf("'Identifier SMSCenter.options['%s'] is not defined.'", $offset));
		}
		return $this->options[$offset];
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetExists($offset) {
		return array_key_exists($offset, $this->options);
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetUnset($offset) {
		unset($this->options[$offset]);
	}
}