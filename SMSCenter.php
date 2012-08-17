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
 *	$smsc = new SMSCenter(array(
 *		'login'	   => 'ivan',
 *		'password' => md5('ivanovich'),
 *		'charset' => SMSCenter::CHARSET_UTF8
 *	));
 *
 *	// Отправка сообщения
 *	$smsc->send('+7991111111', 'Превед, медведы!', 'SuperIvan');
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
 * @version 0.1
 * @author JhaoDa <jhaoda@gmail.com>
 * @license Да хоть как используйте. Упомянете меня — и на том спасибо.
 */
class SMSCenter implements ArrayAccess {
	private $options = array();
	private $types = array(0 => '', 1 => 'flash=1', 2 => 'push=1', 3 => 'hlr=1', 4 => 'bin=1', 5 => 'bin=2', 6 =>'ping=1');

	private static $curl = NULL;

	const SCHEME_HTTP  = 1;
	const SCHEME_HTTPS = 2;

	const METHOD_GET  = 1;
	const METHOD_POST = 2;

	const MSG_SMS	= 0;
	const MSG_FLASH	= 1;
	const MSG_WAP	= 2;
	const MSG_HLR	= 3;
	const MSG_BIN	= 4;
	const MSG_HEX	= 5;
	const MSG_PING	= 6;

	const COST_NO		= 0;
	const COST_ONLY		= 1;
	const COST_TOTAL	= 2;
	const COST_BALANCE	= 3;

	const TRANSLIT_NONE	= 0;
	const TRANSLIT_YES	= 1;
	const TRANSLIT_ALT	= 2;

	const FMT_PLAIN		 = 0;
	const FMT_PLAIN_ALT	 = 1;
	const FMT_XML		 = 2;
	const FMT_JSON		 = 3;

	const STATUS_PLAIN		= 0;
	const STATUS_INFO		= 1;
	const STATUS_INFO_EXT	= 2;

	const CHARSET_UTF8 = 'utf-8';
	const CHARSET_KOI8 = 'koi8-r';
	const CHARSET_1251 = 'windows-1251';

	const ZONE_RU	= 10;
	const ZONE_UA	= 20;
	const ZONE_SNG	= 30;
	const ZONE_1	= 1;
	const ZONE_2	= 2;
	const ZONE_3	= 3;

	/**
	 * Инициализация.
	 *
	 * Допустимые ключи следующие (в скобках значения по-умолчанию):
	 * <pre>
	 * $default = array(
	 *		'login',	// логин
	 *		'password',	// пароль или MD5-хэш пароля
	 *		'translit',	// кодировать ли сообщении в транслит (self::TRANSLIT_NONE)
	 *		'charset',	// кодировка запроса и ответа (self::CHARSET_UTF8)
	 *		'method',	// метод передачи - GET или POST (self::METHOD_POST)
	 *		'scheme',	// HTTP/HTTPS режим (self::SCHEME_HTTP), для HTTPS в Windows требуется включить расширение OpenSSL
	 *		'fmt',		// формат ответа сервера (self::FMT_JSON)
	 *		'type',		// тип сообщения (self::MSG_SMS), замена push, ping, hlr и прочих
	 *		'cost',		// запрашивать ли стоимость (self::COST_NO)
	 *		'time',		// время отправки сообщения (NULL)
	 *		'tz',		// часовой пояс параметра time (NULL)
	 *		'id',		// идентификатор сообщения (NULL)
	 *		'period',	// (NULL)
	 *		'freq',		// (NULL)
	 *		'maxsms',	// (NULL)
	 *		'err'		// (NULL)
	 * );
	 * </pre>
	 *
	 * @access public
	 * @param array $options Настройки
	 * @return void
	 */
	public function __construct($options = array()) {
		$default = array(
			'login'		=> '',
			'password'	=> '',
			'method'	=> self::METHOD_POST,
			'mode'		=> self::SCHEME_HTTP,
			'fmt'		=> self::FMT_JSON,
		);

		$this->options = array_merge($default, $options);
	}

	/**
	 * Отправить сообщение.
	 *
	 * @access public
	 * @param string|array $phones Номера телефонов
	 * @param string $message Тест сообщения
	 * @param array $options Дополнительные параметры
	 * @return bool|string|stdClass Результат выполнения запроса в виде строки, объекта (FMT_JSON) или FALSE в случае ошибки.
	 */
	public function send($phones, $message, $sender, $options = array()) {
		$options['phones'] = $phones;
		if ($message !== NULL ) $options['mes'] = $message;
		if ($sender !== NULL ) $options['sender'] = $sender;

		return $this->sendCmd('send', $options);
	}

	/**
	 * Проверка номеров на доступность в реальном времени.
	 *
	 * @access public
	 * @param string|array $phones Номера телефонов
	 * @return bool|string|stdClass Результат выполнения запроса в виде строки, объекта (FMT_JSON) или FALSE в случае ошибки.
	 */
	public function pingPhone($phones) {
		return $this->send($phones, NULL, NULL, array('type' => self::MSG_PING));
	}

	/**
	 * Получение стоимости рассылки.
	 *
	 * @access public
	 * @param string|array $phones Номера телефонов
	 * @param string $message Тест сообщения
	 * @param array $options Дополнительные опции
	 * @return bool|string|stdClass Стоимость рассылки в виде строки, объекта (FMT_JSON) или FALSE в случае ошибки.
	 */
	public function getCost($phones, $message, $options = array()) {
		$options['cost'] = self::COST_ONLY;

		return $this->send($phones, $message, NULL, $options);
	}

	/**
	 * Получение статуса сообщения.
	 *
	 * @access public
	 * @param type $phone Номер телефона
	 * @param type $id Идентификатор сообщения
	 * @param type $mode Вид ответа: обычный полный, расширеный
	 * @return bool|string|stdClass Статус сообщения в виде строки, объекта (FMT_JSON) или FALSE в случае ошибки.
	 */
	public function getStatus($phone, $id, $mode = self::STATUS_PLAIN) {
		return $this->sendCmd('status', array(
			'phone' => $phone,
			'id' => (int)$id,
			'all' => (int)$mode,
		));
	}

	/**
	 * Получение информации об операторе: название и регион регистрации номера абонента.
	 *
	 * @access public
	 * @param type $phone Номер телефона
	 * @return bool|string|stdClass Информация об операторе в виде строки, объекта (FMT_JSON) или FALSE в случае ошибки.
	 */
	public function getOperatorInfo($phone) {
		return $this->sendCmd('info', array(
			'get_operator' => '1',
			'phone' => $phone
		));
	}

	/**
	 * Запрос баланса.
	 *
	 * @access public
	 * @param integer $format формат
	 * @return string|stdClass Баланс в виде строки, объекта (FMT_JSON) или FALSE в случае ошибки.
	 */
	public function getBalance() {
		return ($this['fmt'] == self::FMT_JSON) ? $this->sendCmd('balance')->balance : $this->sendCmd('balance');
	}

	/**
	 * Определение тарифной зоны.
	 *
	 * @param string $phone Номер телефона
	 * @return int Номер тарифной зоны (константы self::ZONE_*)
	 */
	public function getChargingZone($phone) {
		$patterns = array(
			self::ZONE_RU => '~^\+?(79|73|74|78)~', self::ZONE_UA => '~^\+?380~', self::ZONE_SNG => '~^\+?(7940|374|375|995|77|996|370|992|993|998)~',
			self::ZONE_1 => '~^\+?(994|213|244|376|54|93|880|973|591|387|58|84|241|233|502|852|299|20|972|91|92|62|962|964|98|353|354|855|237|1|254|357|57|242|506|965|856|231|423|352|261|389|60|960|356|52|976|971|595|503|966|381|65|421|386|66|255|216|598|63|385|382|56|94|593|372|27|1876|81)~',
			self::ZONE_2 => '~^\+?(44|359|30|45|86|53|371|373|48|886|358|420|82)~'
		);

		$phone = $this->clearPhone($phone);

		foreach($patterns as $key => $value) {
			if (preg_match($value, $phone)) return $key;
		}

		return self::ZONE_3;
	}

	/**
	 * Самая умная функция.
	 *
	 * @access private
	 * @param string $cmd
	 * @param array $options
	 * @return bool|string|stdClass
	 * @throws InvalidArgumentException
	 */
	private function sendCmd($cmd, $options = array()) {
		$options = array_merge($this->options, $options);

		if ($cmd == 'send') {
			if (!empty($options['phones'])) {
				if (is_array($options['phones'])) {
					$options['phones'] = array_map(array($this, 'clearPhone'), $options['phones']);
					$options['phones'] = implode(';', $options['phones']);
				} else {
					$options['phones'] = $this->clearPhone($options['phones']);
				}
			} else {
				throw new InvalidArgumentException('Phones is empty.');
			}
		} else {
			if (isset($options['phone']))
				$options['phone'] = $this->clearPhone($options['phone']);
		}

		unset($options['password'], $options['login']);
		$data = array('login='.urlencode($this['login']), 'psw='.urlencode($this['password']));

		foreach ($options as $key => $value) {
			switch ($key) {
				case 'mode': case 'method': break;
				case 'mes': case 'phone': case 'phones':
					if (!empty($value)) $data[] = $key.'='.urlencode($value);
				case 'type':
					if ((int)$value > 0 && (int)$value < 7) $data[] = $this->types[(int)$value]; break;
				default:
					if (!empty($value)) $data[] = $key.'='.$value;
			}
		}

		$url = (($this['mode'] === self::SCHEME_HTTPS) ? 'https' : 'http').'://smsc.ru/sys/'.$cmd.'.php?';

		$i = 0;
		do {
			if ($i) sleep(2);
			$ret = $this->exec($url.implode('&', $data));
		} while ($ret == '' && ++$i < 3);

		if (($cmd == 'info' || $cmd == 'status') && $this['fmt'] == self::FMT_JSON) {
			if ($this['charset'] == self::CHARSET_1251)
				$ret = mb_convert_encoding($ret, 'UTF-8', 'WINDOWS-1251');
			else if ($this['charset'] == self::CHARSET_KOI8)
				$ret = mb_convert_encoding($ret, 'UTF-8', 'KOI8-R');
		}

		return (empty($ret)) ? FALSE : ($this['fmt'] == self::FMT_JSON) ? $ret = json_decode($ret) : $ret;
	}

	/**
	 * Непосредственно выполнение запроса.
	 *
	 * @access private
	 * @param string $request
	 * @return string Ответ сервера
	 * @throws RuntimeException
	 * @throws Exception
	 */
	private function exec($request) {
		$ret = NULL;

		$isPOST = $this['method'] == self::METHOD_POST || strlen($request) > 2000;

		if (function_exists('curl_init')) {
			if (!self::$curl) {
				self::$curl = curl_init();
				curl_setopt(self::$curl, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt(self::$curl, CURLOPT_CONNECTTIMEOUT, 10);
				curl_setopt(self::$curl, CURLOPT_TIMEOUT, 10);
				curl_setopt(self::$curl, CURLOPT_SSL_VERIFYPEER, 0);
			}

			if ($isPOST) {
				list($url, $query) = explode('?', $request, 2);
				curl_setopt(self::$curl, CURLOPT_POST, TRUE);
				curl_setopt(self::$curl, CURLOPT_POSTFIELDS, $query);
				curl_setopt(self::$curl, CURLOPT_URL, $url);
			} else {
				curl_setopt(self::$curl, CURLOPT_URL, $request);
			}

			$ret = curl_exec(self::$curl);
		} else if (function_exists('fsockopen')) {
			$m = parse_url($request);

			if ($this['mode'] == self::SCHEME_HTTPS) {
				if (extension_loaded('openssl'))
					$fp = fsockopen('ssl://'.$m['host'], 443, $errno, $errstr, 10);
				else
					throw new RuntimeException('Can not perform HTTPS request. OpenSSL extension not loaded.');
			} else {
				$fp = fsockopen($m['host'], 80, $errno, $errstr, 10);
			}

			if ($fp) {
				stream_set_timeout($fp, 2);

				fwrite($fp, ($isPOST ? "POST $m[path]" : "GET $m[path]?$m[query]").
						" HTTP/1.1\r\nHost: smsc.ru\r\nUser-Agent: PHP".
						($isPOST ? "\r\nContent-Type: application/x-www-form-urlencoded\r\nContent-Length: ".strlen($m['query']) : "").
						"\r\nConnection: Close\r\n\r\n".($isPOST ? $m['query'] : ""));

				while (!feof($fp))
					$ret .= fgets($fp, 1024);

				list(, $ret) = explode("\r\n\r\n", $ret, 2);

				fclose($fp);
			} else {
				throw new RuntimeException($errstr, $errno);
			}
		} else {
			if ($isPOST)
				throw new Exception('file_get_contents can not send data over POST.');

			$ret = file_get_contents($request);
		}

		return $ret;
	}

	private function clearPhone($phone) {
		return preg_replace('~[^\d+]~', '', $phone);
	}

//	private function validatePhone($phone) {
//		//TODO: Add validation
//	}
//*********************************************************************************************************************

	/**
	 * Sets a parameter.
	 *
	 * @param string $id    The unique identifier for the parameter
	 * @param mixed  $value The value of the parameter
	 */
	public function offsetSet($id, $value) {
		$this->options[$id] = $value;
	}

	/**
	 * Gets a parameter.
	 *
	 * @param string $id The unique identifier for the parameter
	 * @return mixed The value of the parameter
	 * @throws InvalidArgumentException if the identifier is not defined
	 */
	public function offsetGet($id) {
		if (!array_key_exists($id, $this->options))
			throw new InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $id));
		return $this->options[$id];
	}

	/**
	 * Checks if a parameter is set.
	 *
	 * @param string $id The unique identifier for the parameter
	 * @return Boolean
	 */
	public function offsetExists($id) {
		return array_key_exists($id, $this->options);
	}

	/**
	 * Unsets a parameter.
	 *
	 * @param string $id The unique identifier for the parameter
	 */
	public function offsetUnset($id) {
		unset($this->options[$id]);
	}
}

?>