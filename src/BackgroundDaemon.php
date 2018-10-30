<?php
/*
 * This file is part of Library Framework.
 *
 * (c) Thorpe Lee(Gwangbok Lee) <koangbok@gmail.com>
 *
 * For the full copyright and license information, please view
 * the license that is located at the bottom of this file.
 */

namespace CafeLatte\Libraries;


class BackgroundDaemon
{
    /**
     * run Background
     *
     * @param string $command
     * @param int $wait
     */
    public static function doStart(string $command, int $wait = 60)
    {
        $pid = pcntl_fork();
        if ($pid == -1) {
            echo "Process Fail \n";
        } else if ($pid) {
            echo "Parent Finish \n";
            exit();
        } else {
            echo "Child Start \n";
            while (TRUE) {
                exec($command);
                sleep($wait);
            }
        }
    }
}