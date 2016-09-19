###User email retrieval via Facebook, Google and Twitter OAuth

####Project goal

To offer minimal authentication flows for the above three providers and retrieval of user email addresses for use in part of a more complete login system.

Each provider subclass should be as compact as possible, without compromising on legibility.

####Example usage

```php
require 'OpenC/autoload.php';

use OpenC\Provider_email_facebook;
use OpenC\Provider_email_google;
use OpenC\Provider_email_twitter;

session_start();
if (isset($_GET['facebook'])) {
	 $facebook = new Provider_email_facebook();
	 echo $facebook->retrieve_email();
}
if (isset($_GET['google'])) {
	 $google = new Provider_email_google();
	 echo $google->retrieve_email();
}
if (isset($_GET['twitter'])) {
	 $twitter = new Provider_email_twitter();
	 echo $twitter->retrieve_email();
}
```

&nbsp;

####Note
To avoid accidental commits of authentication credentials, contributors are encouraged to use a pre-commit hook something like the following:
	
```bash
#!/bin/sh
find . -exec ls '{}' \;
find ./OpenC/ -maxdepth 1 -name '*.php' -exec perl -pi -e "if (m/^\s+'client_id'|'consumer_key'|'client_secret'|'redirect_uri'/) { s/=>\s?'.*?'/=> ''/ }" {} \;
```
