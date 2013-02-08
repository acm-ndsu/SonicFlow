Grooveshark unofficial PHP SDK (v.0.0.1)
==========================

Despite having an extensive API, it is not publicly open: 
"Our content providers are very strict about the clientele that we can provide access to."

This class simply uses what web application itself uses to search and play songs.  


Usage
-----

The minimal you'll need to have is:

```php
include("grooveshark.class.php");

$gs = new GrooveShark();
$url = 'http://grooveshark.com/#/s/Cookies+With+A+Smile/3EXEk7?src=5';
$song = $gs->getSongByUrl($url);

$data = $gs->getSongById($song['SongID']);

print_r($data);
```


TODO
-----

1. PHPDoc for all api functions
2. Add API functions for Login, Searching, Radio ...

Notes
-----

Grooveshark changes the keys from time to time. I will try to update them as quickly as possible.
The script get_keys.php will download the swf files and js files needed and attempt to recover the new keys.

swfdump is required for this application. 


```php
$ php get_keys.php 
____________________
Token Key: paperPlates
Client: htmlshark
Client Revision: 20110906
Rev Token: reallyHotSauce
```

