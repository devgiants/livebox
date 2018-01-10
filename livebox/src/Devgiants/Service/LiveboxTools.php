<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 05/01/18
 * Time: 12:00
 */

namespace Devgiants\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\SessionCookieJar;
use GuzzleHttp\Cookie\SetCookie;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;

class LiveboxTools {

	/**
	 * @var Client
	 */
	protected $client;
	/**
	 * @var SessionCookieJar
	 */
	protected $cookieJar;

	/**
	 * LiveboxTools constructor.
	 */
	public function __construct() {
		$this->cookieJar = new SessionCookieJar( 'SESSION_STORAGE', true );

		$this->client = new Client( [
			'cookies' => $this->cookieJar
		] );
	}

	/**
	 * @return SessionCookieJar
	 */
	public function getCookieJar() {
		return $this->cookieJar;
	}

	/**
	 * @return Client
	 */
	public function getClient() {
		return $this->client;
	}

	/**
	 * @param $host
	 * @param $username
	 * @param $password
	 *
	 * @return mixed
	 */
	public function authenticate( $host, $username, $password ) {

//		$cookieJar = $this->getClient()->getConfig('cookies');
//		var_dump($cookieJar->toArray());

		$response = $this->client->post( "$host/ws", [
			RequestOptions::HEADERS => [
				'Content-Type'  => 'application/x-sah-ws-1-call+json; charset=UTF-8',
				'Authorization' => 'X-Sah-Login'
			],
			RequestOptions::JSON    => [
				'service'    => 'sah.Device.Information',
				'method'     => 'createContext',
				'parameters' => [
					'applicationName' => 'so_sdkut',
					'username'        => $username,
					'password'        => $password
				]
			]
		] );


		foreach($response->getHeader('Set-Cookie') as $cookieString) {
			$cookie = SetCookie::fromString($cookieString);
			$cookie->setDomain('192.168.1.1');
//			var_dump( $this->cookieJar->setCookie($cookie));
			$this->cookieJar->save();
		}
//		echo($this->cookieJar->count());

//		var_dump( $this->cookieJar->toArray() );
//		die();

		$json = \GuzzleHttp\json_decode( $response->getBody()->getContents() );

		if ( ( $json->status === 0 ) && isset( $json->data->contextID ) ) {
			return $json->data->contextID;
		} else {
			// TODO handle
			throw new \AuthenticationException();
		}
	}

	/**
	 * @param $host
	 */
	public function logout( $host ) {
		$response = $this->client->post( "$host/logout" );
	}

}