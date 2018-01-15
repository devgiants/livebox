<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 05/01/18
 * Time: 12:00
 */

namespace Devgiants\Service;

use Buzz\Browser;
use Buzz\Message\Request;
use Buzz\Util\Cookie;
use Buzz\Util\CookieJar;
use Buzz\Message\MessageInterface;
use Buzz\Util\Url;
use Symfony\Component\Console\Input\InputInterface;
use Devgiants\Exception\AuthenticationException;

class LiveboxTools {

	/**
	 * @var Browser
	 */
	protected $browser;

	/**
	 * @var string
	 */
	protected $currentToken;

	/**
	 * @var CookieJar
	 */
	protected $cookieJar;

	/**
	 * LiveboxTools constructor.
	 */
	public function __construct() {
		$this->browser = new Browser();
		$this->cookieJar = new CookieJar();
	}

	/**
	 * @return Browser
	 */
	public function getBrowser() {
		return $this->browser;
	}


	/**
	 * @param $host
	 * @param $username
	 * @param $password
	 *
	 * @return mixed
	 */
	public function authenticate( $host, $username, $password ) {

		// Get token response
		$response = $this->browser->post(
			"$host/ws",
			[
				'Content-Type'  => 'application/x-sah-ws-1-call+json; charset=UTF-8',
				'Authorization' => 'X-Sah-Login',
			],
			json_encode( [
				'service'    => 'sah.Device.Information',
				'method'     => 'createContext',
				'parameters' => [
					'applicationName' => 'so_sdkut',
					'username'        => $username,
					'password'        => $password,
				],
			] )

		);


		// Create sessid cookie
		$cookie = new Cookie();
		$cookie->fromSetCookieHeader( $response->getHeader( 'Set-Cookie' ), $host );

		// Add cookie to JAR
		$this->cookieJar->addCookie( $cookie );

		

		$json = json_decode( $response->getContent() );

		if ( ( $json->status === 0 ) && isset( $json->data->contextID ) ) {
			$this->currentToken = $json->data->contextID;

			return $json->data->contextID;
		} else {
			// TODO handle
			throw new AuthenticationException();
		}
	}


	/**
	 * @return string
	 */
	public function getCookieHeaderForRequest() {
		$cookieString = "Cookie: ";

		foreach($this->cookieJar->getCookies() as $cookie) {
			$cookieString .= "{$cookie->getName()}={$cookie->getValue()}; ";
		}


		return $cookieString;
	}

	/**
	 * @param $method
	 * @param $url
	 * @param array $parameters
	 *
	 * @return MessageInterface
	 */
	public function createRequest( $method, $url, $parameters = [] ) {
		// Create request from URL
		$request = new Request( $method );
		$request->fromUrl( new Url( $url ) );


		// Add headers
		$request->setHeaders( [
			'X-Context'           => $this->currentToken,
			'X-Prototype-Version' => '1.7',
			'Content-Type'        => 'application/x-sah-ws-1-call+json; charset=UTF-8',
			'Accept'              => 'text/javascript',
		] );

		// Add cookie header
		$request->addHeader( $this->getCookieHeaderForRequest() );

		// Set content
		$request->setContent( json_encode( $parameters ) );

		$response = $this->browser->send( $request );

		return $response;
	}

	/**
	 * @return \Buzz\Util\CookieJar
	 */
	public function getCookieJar() {
		return $this->cookieJar;
	}

	/**
	 * @param $host
	 */
	public function logout( $host ) {
		$response = $this->browser->post( "$host/logout" );
	}

}
