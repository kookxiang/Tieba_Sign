<?php
if (!defined('IN_KKFRAME')) exit();
class kerror {
	public static function system_error($message, $show = true, $halt = true) {
		list($showtrace, $logtrace) = kerror::debug_backtrace();
		if ($show) {
			kerror::show_error('system', "<li>$message</li>", $showtrace, 0);
		}
		if ($halt) {
			exit();
		} else {
			return $message;
		}
	}
	public static function debug_backtrace() {
		$skipfunc[] = 'kerror->debug_backtrace';
		$skipfunc[] = 'kerror->db_error';
		$skipfunc[] = 'kerror->template_error';
		$skipfunc[] = 'kerror->system_error';
		$skipfunc[] = 'db_mysql->halt';
		$skipfunc[] = 'db_mysql->query';
		$skipfunc[] = 'DB::_execute';
		$show = $log = '';
		$debug_backtrace = debug_backtrace();
		krsort($debug_backtrace);
		foreach ($debug_backtrace as $k => $error) {
			$file = str_replace(ROOT, '', $error['file']);
			$func = isset($error['class']) ? $error['class'] : '';
			$func .= isset($error['type']) ? $error['type'] : '';
			$func .= isset($error['function']) ? $error['function'] : '';
			if (in_array($func, $skipfunc)) {
				break;
			}
			$error[line] = sprintf('%04d', $error['line']);
			$show .= "<li>[Line: $error[line]]".$file."($func)</li>";
			$log .= !empty($log) ? '->' : '';
			$file.':'.$error['line'];
			$log .= $file.':'.$error['line'];
		}
		return array($show, $log);
	}
	public static function db_error($message, $sql) {
		global $_G;
		list($showtrace, $logtrace) = kerror::debug_backtrace();
		$db = &DB::object();
		$dberrno = $db->errno();
		$dberror = str_replace($db->tablepre, '', $db->error());
		$sql = htmlspecialchars(str_replace($db->tablepre, '', $sql));
		$msg = '<li>'.$message.'</li>';
		$msg .= $dberrno ? '<li>['.$dberrno.'] '.$dberror.'</li>' : '';
		$msg .= $sql ? '<li>[Query] '.$sql.'</li>' : '';
		kerror::show_error('db', $msg, $showtrace, false);
		exit();
	}
	public static function exception_error($exception) {
		if ($exception instanceof DbException) {
			$type = 'db';
		} else {
			$type = 'system';
		}
		if ($type == 'db') {
			$errormsg = '('.$exception->getCode().') ';
			$errormsg .= self::sql_clear($exception->getMessage());
			if ($exception->getSql()) {
				$errormsg .= '<div class="sql">';
				$errormsg .= self::sql_clear($exception->getSql());
				$errormsg .= '</div>';
			}
		} else {
			$errormsg = $exception->getMessage();
		}
		$trace = $exception->getTrace();
		krsort($trace);
		$trace[] = array('file' => $exception->getFile(), 'line' => $exception->getLine(), 'function' => 'ErrorHandler');
		$phpmsg = array();
		foreach ($trace as $error) {
			if (!empty($error['function'])) {
				$fun = '';
				if (!empty($error['class'])) {
					$fun .= $error['class'].$error['type'];
				}
				$fun .= $error['function'].'(';
				if (!empty($error['args'])) {
					$mark = '';
					foreach($error['args'] as $arg) {
						$fun .= $mark;
						if (is_array($arg)) {
							$fun .= 'Array';
						} elseif (is_bool($arg)) {
							$fun .= $arg ? 'true' : 'false';
						} elseif (is_int($arg)) {
							$fun .= (defined('DEBUG_FLAG') && DEBUG_FLAG) ? $arg : '%d';
						} elseif (is_float($arg)) {
							$fun .= (defined('DEBUG_FLAG') && DEBUG_FLAG) ? $arg : '%f';
						} else {
							$fun .= (defined('DEBUG_FLAG') && DEBUG_FLAG) ? '\''.htmlspecialchars(substr(self::clear($arg), 0, 10)).(strlen($arg) > 10 ? ' ...' : '').'\'' : '%s';
						}
						$mark = ', ';
					}
				}
				$fun .= ')';
				$error['function'] = $fun;
			}
			$phpmsg[] = array('file' => str_replace(array(ROOT, '\\'), array('', '/'), $error['file']), 'line' => $error['line'], 'function' => $error['function'],);
		}
		self::show_error($type, $errormsg, $phpmsg);
		exit();
	}
	public static function show_error($type, $errormsg, $phpmsg = '', $exit = true) {
		ob_end_clean();
		ob_start();
		$host = $_SERVER['HTTP_HOST'];
		$title = $type == 'db' ? 'Database' : 'System';
		echo <<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html><head><title>$host - $title Error</title><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /><meta name="ROBOTS" content="NOINDEX,NOFOLLOW,NOARCHIVE" /><style type="text/css"><!-- body { background-color: white; color: black; font: 9pt/11pt verdana, arial, sans-serif;} #container { width: 1024px; } #message { width: 1024px; color: black; } .red {color: red;} a:link { font: 9pt/11pt verdana, arial, sans-serif; color: red; } a:visited { font: 9pt/11pt verdana, arial, sans-serif; color: #4e4e4e; } h1 { color: #FF0000; font: 18pt "Verdana"; margin-bottom: 0.5em;} .bg1{ background-color: #FFFFCC;} .bg2{ background-color: #EEEEEE;} .table {background: #AAAAAA; font: 11pt Menlo,Consolas,"Lucida Console"} .info { background: none repeat scroll 0 0 #F3F3F3; border: 0px solid #aaaaaa; border-radius: 10px 10px 10px 10px; color: #000000; font-size: 11pt; line-height: 160%; margin-bottom: 1em; padding: 1em; } .help { background: #F3F3F3; border-radius: 10px 10px 10px 10px; font: 12px verdana, arial, sans-serif; text-align: center; line-height: 160%; padding: 1em; } .sql { background: none repeat scroll 0 0 #FFFFCC; border: 1px solid #aaaaaa; color: #000000; font: arial, sans-serif; font-size: 9pt; line-height: 160%; margin-top: 1em; padding: 4px; } --></style></head><body><div id="container"><h1>KK Tieba Signer $title Error</h1><div class='info'>$errormsg</div>
EOT;
		if (is_array($phpmsg) && !empty($phpmsg)) {
			echo '<div class="info">';
			echo '<p><strong>PHP Debug</strong></p>';
			echo '<table cellpadding="5" cellspacing="1" width="100%" class="table">';
			echo '<tr class="bg2"><td>No.</td><td>File</td><td>Line</td><td>Code</td></tr>';
			foreach($phpmsg as $k => $msg) {
				$k++;
				echo '<tr class="bg1">';
				echo '<td>'.$k.'</td>';
				echo '<td>'.$msg['file'].'</td>';
				echo '<td>'.$msg['line'].'</td>';
				echo '<td>'.$msg['function'].'</td>';
				echo '</tr>';
			}
			echo '</table></div>';
		}
		echo '</div></body></html>';
		$exit && exit();
	}
	public static function clear($message) {
		return str_replace(array("\t", "\r", "\n"), " ", $message);
	}
	public static function sql_clear($message) {
		$message = self::clear($message);
		$message = str_replace(DB::object()->tablepre, '', $message);
		$message = htmlspecialchars($message);
		return $message;
	}
}
