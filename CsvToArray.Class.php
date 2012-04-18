<?php
 /**
 * Zend Framework
 *
 * LICENSE
 *
 * Arquivo de livre reprodu��o
 * 
 * Utiliza��o:
 * 
 * echo '<pre>';
 * print_r(CsvToArray('teste.csv'));
 * echo '</pre>';
 *
 * 
 * 
 * @category   CSV
 * @package    CsvToArray
 * @copyright  Copyleft (c) 2009-2010 . (http://www.pontophp.com.br)
 * @version    1.1
 */
 final class CsvToArray {

 	/**
 	 * Fun��o est�tica principal. O par�metro $delimiter n�o � obrigat�rio, apenas se for utilizado outro tipo de caractere, por exemplo a v�rgula (,).
 	 *
 	 * @param string $file
 	 * @param char $delimiter
 	 * @return array
 	 */
 	public static function open($file, $delimiter = ';') {
 		return self::csvArray($file, $delimiter);
 	}
 	
 	private static function csvArray($file, $delimiter) {
 		$result = Array();
 		$size = filesize($file) + 1;
 		$file = fopen($file, 'r');
 		$keys = fgetcsv($file, $size, $delimiter);
 		
 		require_once 'ProgressBar.class.php';

		echo CARREGANDO;
		$p = new ProgressBar();
		echo '<div style="width: 400px;">' . "\r\n";
		$p->render();
		echo '</div>' . "\r\n";
 		
 		while ($row = fgetcsv($file, $size, $delimiter)) {
 			for ($i = 0; $i < count($row); $i++)
 				if (array_key_exists($i, $keys))
 					$row[$keys[$i]] = $row[$i];
 			$result[] = $row;
 		}
 		
 		$p->setProgressBarProgress(100);
 		
 		fclose($file);
 		return $result;
 	}
}
?>
