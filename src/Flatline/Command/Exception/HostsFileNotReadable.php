<?php

namespace Flatline\Command\Exception;


class HostsFileNotReadable extends \Exception
{
    public function __construct($message = "Can't read hosts file! Run the command as root.", $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}