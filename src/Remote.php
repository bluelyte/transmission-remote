<?php

namespace Bluelyte\Transmission;

/**
 * A wrapper for the transmission-remote CLI utility.
 *
 * @link http://blog.zloether.com/2009/03/command-line-bittorrent-with.html
 */
class Remote
{
    /**
     * Flag for whether peers using encryption should be required or only 
     * preferred
     * @var boolean TRUE to require encryption, FALSE otherwise
     */
    protected $encryption = true;

    /**
     * Port on which to operate the daemon
     * @var string
     */
    protected $port = '58261';

    /**
     * Flag for whether to enable UPnP port mapping
     * @var boolean TRUE to enable port mapping, FALSE otherwise
     */
    protected $upnp = false;

    /**
     * Path at which to store downloaded files
     * @var string
     */
    protected $downloadPath = null;

    /**
     * Output of the last executed daemon command
     * @var string
     */
    protected $output = null;

    /**
     * Exit status of the last executed daemon command
     * @var int
     */
    protected $status = 0;

    /**
     * Flag indicating whether the daemon has been started
     * @var boolean
     */
    protected $started = false;

    /**
     * Sets a flag for whether peers using encryption should be required or only 
     * preferred.
     *
     * @param boolean $encryption TRUE to require encryption, FALSE otherwise
     */
    public function setEncryption($encryption)
    {
        $this->encryption = (boolean) $encryption;
    }

    /**
     * Sets the port on which to operate the daemon.
     *
     * @param string $port
     */
    public function setPort($port)
    {
        if (!ctype_digit($port)) {
            trigger_error('Port must be a positive integer: ' . var_export($port, true), E_USER_ERROR);
        }
        $this->port = $port;
    }

    /**
     * Sets a flag for whether to enable UPnP port mapping.
     *
     * @param boolean TRUE to enable port mapping, FALSE otherwise
     */
    public function setUpnp($upnp)
    {
        $this->upnp = (boolean) $upnp;
    }

    /**
     * Sets the path at which to store downloaded files.
     *
     * @param string $downloadPath
     */
    public function setDownloadPath($downloadPath)
    {
        if (!is_dir($downloadPath) || !is_writable($downloadPath)) {
            trigger_error('Cannot write to directory: ' . $downloadPath, E_USER_ERROR);
        }
        $this->downloadPath = $downloadPath;
    }

    /**
     * Execute a shell command.
     *
     * @param string $command
     */
    protected function execute($command)
    {
        $process = proc_open($command, array(1 => array('pipe', 'w'), 2 => array('pipe' => 'w')), $pipes);
        fclose($pipes[2]);
        $this->output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $status = proc_get_status($process);
        $this->status = $status['exitcode'];
        proc_close($process);
    }

    /**
     * Returns the output of the last daemon command.
     *
     * @return string
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Returns the exit status of the last daemon command.
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Starts the daemon.
     */
    public function start()
    {
        if ($this->started) {
            return;
        }
        
        $command = 'transmission-remote -C'
            . ' -' . ($this->encryption ? 'er' : 'ep')
            . ' -p ' . $this->port
            . ' -' . ($this->upnp ? 'm' : 'M')
            . ($this->downloadPath ? ' -w ' . $this->downloadPath : '');
        $this->execute($command);

        $this->execute('transmission-remote -l');
        if ($this->getStatus() == 1) {
            $this->execute('transmission-daemon');
        }

        $this->started = true;
    }

    /**
     * Adds a torrent to download.
     *
     * @param string|array $path Space-delimited list or array of paths to 
     *        one or more .torrent files
     */
    public function addTorrents($paths)
    {
        if (is_array($paths)) {
            $paths = implode(' ', $paths);
        }
        $this->execute('transmission-remote -a ' . $paths);
    }

    /**
     * Starts downloading all added torrents.
     */
    public function startTorrents()
    {
        $this->execute('transmission-remote -tall --start');
    }
}
