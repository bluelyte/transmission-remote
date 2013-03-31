# bluelyte/transmission-remote

A PHP wrapper for the transmission-remote CLI utility.

DISCLAIMER: This project is not endorsed by, affiliated with, or intended to infringe upon the [Transmission project](http://www.transmissionbt.com) and is meant for non-commercial purposes (i.e. personal use) only.

# Install

The recommended method of installation is [through composer](http://getcomposer.org/).

```JSON
{
    "require": {
        "bluelyte/transmission-remote": "1.0.0"
    }
}
```

# Usage

```php
<?php

require __DIR__ . '/vendor/autoload.php';

// Configuration and initialization
$remote = new \Bluelyte\Transmission\Remote();
$remote->setEncryption(false); // not recommended - is true by default
$remote->setPort(12345); // uses standard BitTorrent port by default
$remote->setUpnp(true); // is false by default
$remote->setDownloadPath('/path/for/downloads');
$remote->start();

// Handling torrents
$remote->addTorrents('/path/to/file1.torrent');
$remote->addTorrents('/path/to/file2.torrent /path/to/file3.torrent');
$remote->addTorrents(array('/path/to/file4.torrent', '/path/to/file5.torrent'));
$remote->startTorrents();

// Debugging
var_dump($remote->getOutput(), $remote->getStatus());
```

## License

Released under the BSD License. See `LICENSE`.
