<?php

/**
 * Description of NiceHash
 *
 * @author arek
 */
class NiceHash
{

	const URL_API_WORKERS = 'https://api.nicehash.com/api?method=stats.provider.workers';
	const URL_API_STATS_PROVIDER = 'https://api.nicehash.com/api?method=stats.provider';
	const URL_API_STATS_PROVIDER_EX = 'https://api.nicehash.com/api?method=stats.provider.ex';
	const URL_API_STATS_GLOBAL_24H = 'https://api.nicehash.com/api?method=stats.global.24h';
	const URL_API_SIMPLEMULTIALGO_INFO = 'https://api.nicehash.com/api?method=simplemultialgo.info';
	const LOCATION = ['EU', 'US', 'HK', 'JP'];

	private $id;

	/**
	 * HttpHelper
	 * @var iHttpHelper
	 */
	private $http;

	/**
	 * Log on
	 * @var boolean
	 */
	private $isLogOn = true;

	/**
	 * @var array
	 */
	private $algoInfo;

	public function getAlogInfoByIdx($idx)
	{
		if (!is_array($this->algoInfo)) {
			$this->simplemultialgoInfo();
		}

		if (!isset($this->algoInfo[$idx])) {
			return false;
		}

		return $this->algoInfo[$idx];
	}

	public function setId($val)
	{
		$this->id = $val;
	}

	public function getId()
	{
		return $this->id;
	}

	public function setLogOn($val)
	{
		$this->isLogOn = $val;
	}

	public function isLogOn()
	{
		return $this->isLogOn;
	}

	public function __construct($id)
	{
		$this->setId($id);
		$this->http = new HttpHelper();
	}

	public function workers($algo = NULL)
	{
		$url = static::URL_API_WORKERS . '&addr=' . $this->getId();
		if (!empty($algo)) {
			$url .= $url . '&algo=' . $algo;
		}
		return $this->getDataFromUrl($url);
	}

	public function statsProvider()
	{
		$url = static::URL_API_STATS_PROVIDER . '&addr=' . $this->getId();
		return $this->getDataFromUrl($url);
	}

	public function statsProviderEx()
	{
		$url = static::URL_API_STATS_PROVIDER_EX . '&addr=' . $this->getId();
		return $this->getDataFromUrl($url);
	}

	public function statsGlobal24h()
	{
		$url = static::URL_API_STATS_GLOBAL_24H;
		return $this->getDataFromUrl($url);
	}

	public function simplemultialgoInfo()
	{
		$url = static::URL_API_SIMPLEMULTIALGO_INFO;
		$arr = $this->getDataFromUrl($url);

		$this->algoInfo = [];
		foreach ($arr['result']['simplemultialgo'] as $row) {
			$this->algoInfo[$row['algo']] = $row;
		}

		return $arr;
	}

	public function renderWorkers($algo = NULL)
	{
		$workers = $this->workers($algo);

		foreach ($workers['result']['workers'] as $worker) {
			$name = $worker[0];
			$speed = $worker[1];
			$speed_a = isset($speed['a']) ? $speed['a'] : '<null>';
			$speed_rs = isset($speed['rs']) ? $speed['rs'] : '<null>';
			$timeConnectedInMinutes = $worker[2] . ' min';
			$xnsub = $worker[3] ? 'xnsub: yes' : 'xnsub: no';
			$difficulty = $worker[4];
			//connected to location (0 for EU, 1 for US, 2 for HK and 3 for JP)
			$connectedToLocation = $worker[5];
			$connectedToLocationCC = static::LOCATION[$connectedToLocation];
			echo "$name\t$timeConnectedInMinutes\t$xnsub\t$difficulty\t$connectedToLocationCC\t$speed_a\r\n";
		}
	}

	public function renderStatsProvider()
	{
		$statsProvider = $this->statsProvider();

		foreach ($statsProvider['result']['stats'] as $stats) {
			$balance = $stats['balance'];
			$rejectedSpeed = $stats['rejected_speed'];
			$algo = $stats['algo'];
			$acceptedSpeed = $stats['accepted_speed'];

			echo "$balance\t$rejectedSpeed\t$algo\t$acceptedSpeed\r\n";
		}

		foreach ($statsProvider['result']['payments'] as $payments) {
			$amount = $payments['amount'];
			$time = $payments['time'];
			echo "$amount\t$time\r\n";
		}
	}

	public function renderStatsProviderEx()
	{
		$statsProviderEx = $this->statsProviderEx();
		foreach ($statsProviderEx['result']['current'] as $current) {
			/* $balance = $stats['balance'];
			  $rejectedSpeed = $stats['rejected_speed'];
			  $algo = $stats['algo'];
			  $acceptedSpeed = $stats['accepted_speed'];

			  echo "$balance\t$rejectedSpeed\t$algo\t$acceptedSpeed\r\n"; */
		}
	}

	public function renderStatsGlobal24h()
	{
		$res = $this->statsGlobal24h();
		foreach ($res['result']['stats'] as $row) {
			$price = $row['price'];
			$algo = $row['algo'];
			$algoName = $this->getAlogInfoByIdx($row['algo'])['name'];
			$speed = $row['speed'];

			echo "$price\t$algo\t$speed\t$algoName\r\n";
		}
	}

	public function renderSimplemultialgoInfo()
	{
		$res = $this->simplemultialgoInfo();
		foreach ($res['result']['simplemultialgo'] as $row) {
			$paying = $row['paying'];
			$port = $row['port'];
			$name = $row['name'];
			$algo = $row['algo'];

			echo "$name\t$algo\t$port\t$paying\r\n";
		}
	}

	private function getDataFromUrl($url)
	{
		$str = $this->http->httpGet($url);
		$arr = json_decode($str, true);
		$this->log($arr);

		if (!is_array($arr)) {
			throw new Exception('Error: ' . $url);
		}

		if (isset($arr['result']['error'])) {
			throw new Exception('Error: ' . $arr['result']['error']);
		}
		return $arr;
	}

	/**
	 * Logs
	 */
	private function log($val)
	{
		if (!$this->isLogOn())
			return;

		echo '<pre>';
		var_dump($val);
		echo '</pre>';
	}
}
