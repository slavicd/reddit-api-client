Composer library for posting Reddit links to the developer account programmatically.

https://www.reddit.com/dev/api/

## Usage

* create an app on Reddit
* `composer require slavicd/reddit-api-client:*@dev`

```php
<?php
require 'vendor/autoload.php';
$rdtClient = new Entropi\RedditClient\Client($config);  // see code for required $config keys
$rdtClient->submit('technology', 'Post about technology', 'self', null, 'Hello world!');
```


## Contributing

Accompany your PRs with relevant tests.  
