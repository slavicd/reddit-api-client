<?php


namespace Entropi\RedditClient;

use GuzzleHttp\Client as HttpClient;

class Client
{
    const TOKEN_REQUEST_URL = 'https://www.reddit.com/api/v1/access_token';
    const API_BASE_URL = 'https://oauth.reddit.com/api/';
    const API_SUBMIT = 'submit';

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
     * Submit a link or a text to Reddit
     *
     * @see https://www.reddit.com/dev/api#POST_api_submit
     * @param string $sr subreddit
     * @param string $title
     * @param string $kind link, self
     * @return the url of the created
     */
    public function submit($sr, $title, $kind, $url=null, $text=null)
    {
        switch ($kind) {
            case 'link':
                if ($text) {
                    throw new \InvalidArgumentException('Do not submit a $text when $kind is "link".');
                }
                break;
            case 'self':
                if ($url) {
                    throw new \InvalidArgumentException('Do not submit a $url when $kind is "self".');
                }
                break;
            default: throw new \InvalidArgumentException('kind can only be "link" or "self".');
        }

        if (!$this->accessToken) {
            $this->obtainAccessToken();
        }

        $resp = $this->httpClient->post(self::API_BASE_URL . self::API_SUBMIT, [
            'headers' => [
                'Authorization' => 'bearer ' . $this->accessToken->access_token,
            ],
            'form_params' => [
                'sr'    => $sr,
                'title' => $title,
                'kind'  => $kind,
                'url'   => $url,
                'text'  => $text,
            ],
        ]);

        $simpleResp = json_decode((string) $resp->getBody());

        if (!property_exists($simpleResp, 'success') || $simpleResp->success==false) {
            throw new \RuntimeException("Could not submit: " . (string) $resp->getBody());
        }

        switch ($kind) {
            case 'link': return $simpleResp->jquery[16][3][0];
            case 'self': return $simpleResp->jquery[10][3][0];
        }
    }
}