<?php
namespace Lib\Tools;
class BenchmarkTimerTool
{
    /**
     * Contains the markers.
     *
     * @var    array
     * @access private
     */
    var $markers = array();

    /**
     * Set "Start" marker.
     *
     * @see    setMarker(), stop()
     * @access public
     * @return void
     */
    function start()
    {
        $this->setMarker('Start');
    }

    /**
     * Set "Stop" marker.
     *
     * @see    setMarker(), start()
     * @access public
     * @return void
     */
    function stop()
    {
        $this->setMarker('Stop');
    }

    /**
     * Set marker.
     *
     * @param string $name Name of the marker to be set.
     *
     * @see    start(), stop()
     * @access public
     * @return void
     */
    function setMarker($name)
    {
        $this->markers[$name] = $this->_getMicrotime();
    }

    /**
     * Returns the time elapsed betweens two markers.
     *
     * @param string $start start marker, defaults to "Start"
     * @param string $end   end marker, defaults to "Stop"
     *
     * @return float | string time elapsed between $start and $end
     * @access public
     */
    function timeElapsed($start = 'Start', $end = 'Stop')
    {
        if ($end == 'Stop' && !isset($this->markers['Stop'])) {
            $this->markers['Stop'] = $this->_getMicrotime();
        }
        $end   = isset($this->markers[$end]) ? $this->markers[$end] : 0;
        $start = isset($this->markers[$start]) ? $this->markers[$start] : 0;

        if (extension_loaded('bcmath')) {
            return bcsub($end, $start, 6);
        } else {
            return $end - $start;
        }
    }

    function timeSaveFile($filename="benchmark_time", $start = 'Start', $end = 'Stop')
    {
        $total_time = $this->timeElapsed();
        $msg = $total_time. "\t" . $_SERVER['REQUEST_URI'];
        $this->logFile($filename, $msg);

        return $total_time;
    }
    function timeSaveMysql($tables = "logTimeProd", $start = 'Start', $end = 'Stop')
    {
        $total_time = $this->timeElapsed();
        $this->logMysql($total_time, $tables);

        return $total_time;
    }
    function logFile($filename, $msg)
    {
//        echo $filename;
        $fp = fopen($filename, "a+");
        $log = date('Y-m-d H:i:s'). "\t" .$msg. "\r\n";
        fwrite($fp,$log);
        fclose($fp);

    }
/*

Create Database & Table

CREATE DATABASE `log_wol`;

CREATE TABLE `logTimeProd` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `benchmark` DOUBLE DEFAULT '0',
  `url` VARCHAR(512) DEFAULT NULL,
  `referer` VARCHAR(512) DEFAULT NULL,
  `server_ip` VARCHAR(64) DEFAULT NULL,
  `remote_ip` VARCHAR(64) DEFAULT NULL,
  `ts_insert` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `logTimeLocal` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `benchmark` DOUBLE DEFAULT '0',
  `url` VARCHAR(512) DEFAULT NULL,
  `referer` VARCHAR(512) DEFAULT NULL,
  `server_ip` VARCHAR(64) DEFAULT NULL,
  `remote_ip` VARCHAR(64) DEFAULT NULL,
  `ts_insert` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=INNODB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

*/
    function logMysql($total_time, $tables)
    {
        $con=mysqli_connect("kpi", "wPrograme", "c80hRwuGZd", "log_wol");
        // Check connection
        if (isset($_SERVER["REQUEST_URI"])) {
            $url = $_SERVER['REQUEST_URI'];
        } else {
            $url = '';
        }
        if (mysqli_connect_errno()) {
            $msg = $total_time. "\t" . $url;
            //$this->logFile($filename, $msg);
        }
        if (isset($_SERVER["SERVER_ADDR"])) {
            $server_ip = $_SERVER["SERVER_ADDR"];
        } else {
            $server_ip = '';
        }
        if (isset($_SERVER["REMOTE_ADDR"])) {
            $remote_ip = $_SERVER["REMOTE_ADDR"];
        } else {
            $remote_ip = '';
        }
        if (isset($_SERVER["HTTP_REFERER"])) {
            $referer = $_SERVER["HTTP_REFERER"];
        } else {
            $referer = '';
        }

        $sql = "INSERT INTO `$tables`(`benchmark`,`url`,`server_ip`,`remote_ip`, `referer`) VALUES ( $total_time, '$url', '$server_ip', '$remote_ip', '$referer' )";
        mysqli_query($con, $sql);
        mysqli_close($con);
    }
    function _getMicrotime()
    {
        $microtime = explode(' ', microtime());
        return $microtime[1] . substr($microtime[0], 1);
    }
}