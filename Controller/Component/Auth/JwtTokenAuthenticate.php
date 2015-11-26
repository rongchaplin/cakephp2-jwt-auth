<?php
App::uses('BaseAuthenticate', 'Controller/Component/Auth');

/**
 * An authentication adapter for AuthComponent.  Provides the ability to authenticate using a Json Web Token
 *
 * {{{
 *	$this->Auth->authenticate = array(
 *		'Authenticate.Token' => array(
 *			'fields' => array(
 *				'username' => 'username',
 *				'password' => 'password',
 *				'token' => 'public_key',
 *			),
 *			'parameter' => '_token',
 *			'header' => 'X-MyApiTokenHeader',
 *			'userModel' => 'User',
 *			'scope' => array('User.active' => 1)
 *		)
 *	)
 * }}}
 *
 * @author Ceeram, Florian Krämer, Ronald Chaplin
 * @copyright Ceeram, Florian Krämer, Ronald Chaplin
 * @license MIT
 * @link https://github.com/ceeram/Authenticate
 */

class JwtTokenAuthenticate extends BaseAuthenticate
{

/**
 * Settings for this object.
 *
 * - `fields` The fields to use to identify a user by.
 * - `parameter` The url parameter name of the token.
 * - `header` The token header value.
 * - `userModel` The model name of the User, defaults to User.
 * - `scope` Additional conditions to use when looking up and authenticating users,
 *    i.e. `array('User.is_active' => 1).`
 * - `recursive` The value of the recursive key passed to find(). Defaults to 0.
 * - `contain` Extra models to contain and store in session.
 * - `pepper` The public pepper that clients would use to encrypt their token payload
 *
 * @var array
 */
	public $settings = array(
		'fields' => array(
			'username' => 'username',
			'token' => 'token'
		),
		'parameter' => '_token',
		'header' => 'X_JSON_WEB_TOKEN',
		'userModel' => 'User',
		'scope' => array(),
		'recursive' => 0,
		'contain' => null,
		'pepper' => '123'
	);

/**
 * Constructor
 *
 * @param ComponentCollection $collection The Component collection used on this request.
 * @param array $settings Array of settings to use.
 * @throws CakeException
 */
	public function __construct(ComponentCollection $collection, $settings) {
		parent::__construct($collection, $settings);
		if (empty($this->settings['parameter']) && empty($this->settings['header'])) {
			throw new CakeException(__d('jwt_auth', 'You need to specify token parameter and/or header'));
		}
	}

/**
 * Unused since this a stateless authentication
 *
 * @param CakeRequest $request The request object
 * @param CakeResponse $response response object.
 * @return mixed.  Always false
 */
	public function authenticate(CakeRequest $request, CakeResponse $response) {
		return false;
	}

/**
 * Get token information from the request.
 *
 * @param CakeRequest $request Request object.
 * @return mixed Either false or an array of user information
 */
	public function getUser(CakeRequest $request) {
		$token = $this->_getToken($request);
		if ($token) {
			return $this->_findUser($token);
		}
		return false;
	}

	/**
	 * @param CakeRequest $request
	 * @return mixed
	 */
	private function _getToken(CakeRequest $request)
	{
		if (!empty($this->settings['header'])) {
			$token = $request->header($this->settings['header']);
			if ($token) {
				return $token;
			}
		}

		if (!empty($this->settings['parameter']) && !empty($request->query[$this->settings['parameter']])) {
			return $request->query[$this->settings['parameter']];
		}
		return false;

	}

/**
 * Find a user record.
 *
 * @param string $token
 * @param string $password
 * @return Mixed Either false on failure, or an array of user data.
 */
	public function _findUser($token, $password = null)
	{
		$token = JWT::decode($token, $this->settings['pepper'], array('HS256'));

		if (isset($token->record)) {
			// Trick to convert object of stdClass to array. Typecasting to
			// array doesn't convert property values which are themselves objects.
			return json_decode(json_encode($token->record), true);
		}
		$userModel = $this->settings['userModel'];
		list($plugin, $model) = pluginSplit($userModel);

		$fields = $this->settings['fields'];
		$conditions = array(
			$model . '.' . $fields['username'] => $token->user->name,
			$model . '.' . $fields['token'] => $token->user->token
		);

		if (!empty($this->settings['scope'])) {
			$conditions = array_merge($conditions, $this->settings['scope']);
		}
		$result = ClassRegistry::init($userModel)->find('first', array(
			'conditions' => $conditions,
			'recursive' => (int)$this->settings['recursive'],
			'contain' => $this->settings['contain'],
		));

		if (empty($result) || empty($result[$model])) {
			return false;
		}

		$user = $result[$model];
		unset($result[$model]);

		return array_merge($user, $result);
	}
}
