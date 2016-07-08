<?php
header('Content-Type: text/html; charset= utf-8');

abstract class Logger {
    protected $text;

    public function log($entry) {
        //метод serialize обрабатывает все типы данных, кроме resourse и преобразует в строку
        $this->text = date("[Y-m-d H:i:s] ").serialize($entry);
    }

    abstract public function write();
}

class fileLoger extends Logger {
    private $path;

    public function __construct($path)
    {
        if (!file_exists($path)) {
            throw new Exception('File not exists: ' . $path);
        }
        $this->path = $path;
    }


    public function write() {
/*file_put_contents пишет строку в файл
 * Функция идентична fopen(), fwrite() и fclose()
 * Параметры:
 * filename ($this->path) - Путь к записываемому файлу.
 * data ($this->text) - Записываемые данные. Может быть string, array или ресурсом stream.
 * flags - FILE_APPEND - Если файл filename уже существует, данные будут дописаны в конец файла вместо того, чтобы его перезаписать.
 * Функция возвращает количество записанных байт в файл, или FALSE в случае ошибки.
 */
        return file_put_contents($this->path, $this->text . "\n", FILE_APPEND);
    }
}


class DBLoger extends Logger {
    private $mysqli;

    public function __construct($localhost, $user, $pass, $dbname ) {
        $this->mysqli = new mysqli($localhost, $user, $pass, $dbname);
        if( $this->mysqli->connect_errno ) {
            throw new Exception('DB error: ' . $this->mysqli->connect_error);
        }
    }

    public function write() {
        $this->mysqli->query("insert into log (log) values('$this->text')");
        if ($this->mysqli->errno) {
            die('INSERT Error (' . $this->mysqli->errno . ') ' . $this->mysqli->error);
        }
        $this->mysqli->close();
    }
}

class stdoutLogger extends Logger {
    public function write() {
        $STDOUT = fopen('php://stdout', 'w');
        fwrite($STDOUT, $this->text);
    }
}
/*
$fileLogger = new fileLoger('error.log');
$fileLogger->log('msg1');
$fileLogger->write();

$mysqlLogger = new DBLoger('localhost','root', '', 'log');
$mysqlLogger->log('msg2');
$mysqlLogger->write();

$mysqlLogger = new stdoutLogger();
$mysqlLogger->log('msg3');
$mysqlLogger->write();

*/
