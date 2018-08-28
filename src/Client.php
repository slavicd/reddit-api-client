<?php


namespace Entropi\RedditClient;

use GuzzleHttp\Client as HttpClient;

class Client
{
    const TOKEN_REQUEST_URL = 'https://www.reddit.com/api/v1/access_token';
    const API_BASE_URL = 'https://oauth.reddit.com/api/v1/';

    protected $httpClient;
    protected $accessToken;  // { access_token, expires_in, scope, token_type }
    protected $config;

    /**
     * @param array $config
     */
    public function __construct($config)
    {
        $requiredConfig = ['username', 'password', 'app_id', 'app_secret'];

        if ($diff = array_diff($requiredConfig, array_keys($config))) {
            throw new \InvalidArgumentException('Missing config keys: ' . implode(', ', $diff) . ' in ' . self::class);
        }

        $this->config = $config;

        $htConf = [
            'base_uri' => self::API_BASE_URL,
            'headers' => [
                'User-Agent' => sprintf('linux:%s:v0.1.0 (by /u/%s)', $config['app_id'], $config['username']),
            ],
        ];

        $this->httpClient = new HttpClient($htConf);
    }

    public function obtainAccessToken()
    {
        $resp = $this->httpClient->post(self::TOKEN_REQUEST_URL, [
            'form_params' => [
                'grant_type'    => 'password',
                'username'      => $this->config['username'],
                'password'       => $this->config['password'],
            ],
            'auth' => [$this->config['app_id'], $this->config['app_secret']],
        ]);

        $simpleResp = json_decode((string)$resp->getBody());

        if (!property_exists($simpleResp, 'access_token')) {
            throw new \RuntimeException("Did not receive a token from Reddit.");
        }

        return $this->accessToken = $simpleResp;
    }


    /**
     * Submit a link to Reddit
     *
     * @see https://www.reddit.com/dev/api#POST_api_submit
     * @param string $sr subreddit
     * @param string $title
     * @param string $kind link, self
     */
    public function submit($sr, $title, $kind, $url=null, $text=null)
    {
        if (!$this->accessToken) {
            $this->obtainAccessToken();
        }

        $resp = $this->httpClient->post(self::API_BASE_URL . 'submit', [
            'headers' => [
                'Authorization' => 'bearer ' . $this->accessToken->access_token,
            ],
            'form_params' => [
                'sr' => $sr,
                'title' => $title,
                'kind' => $kind,
                'url' => $url,
                'text' => $text,
            ],
        ]);

        var_dump((string) $resp->getBody());
    }
}