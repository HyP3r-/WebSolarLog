<?php
/**
 * RedBean Mysql Backup
 *
 * @file    RedBeanWSLBackup.php
 * @desc    Generates a backup file of your database
 * @author  Zewa666
 *
 */
class RedBean_WSLBackup implements RedBean_Plugin
{
  /**
   * Creates a file backup of all tables from the connected Database
   *
   * @param  string $outputFolder              The folder where to put the newly created backup-file
   * @param  string $backupName = 'auto'       The name of the new backup file
   */
  public static function performWSLBackup($outputFolder, $backupName = "auto")
  {
    /*if(!(R::getWriter() instanceof RedBean_QueryWriter_MySQL))
    {
      throw new Exception("This plugin only supports MySql.");
    }*/

    if(!file_exists($outputFolder))
    {
      throw new Exception("Outputfolder does not exist, please create it manually.");
    }

    $write = "";
    $tables = R::inspect();
    $chunkSize = 25000;
    foreach($tables as $table)
    {
		//echo $table."\r\n";
    	$write = null;
    	$handle = null;
    	 
      //get record count;
      $result = R::getAll('SELECT COUNT(*) FROM '.$table.'');

      //see how many times we need to iterate through the  
      $divided = round($result[0]['COUNT(*)']/$chunkSize,0);
      
      if($backupName == "auto"){
      	$filename = $outputFolder . '/db-backup-'.$table.'-'.time().'-'.(md5(implode(',',$tables))).'.sql';
      }else{
      	$filename = $outputFolder . '/' . $backupName.'-'.$table;
      }
      

      $fields = R::inspect($table);
      $num_fields = count($fields);
      
      $writeHeader = 'DROP TABLE '.$table.';';

      $row2 = R::getAll("select `sql` from sqlite_master WHERE tbl_name = '". $table ."';");
      
      $tableCreate = str_replace("AUTOINCREMENT", "AUTO_INCREMENT", $row2[0]['sql']);
      
      
      $writeHeader .= "\n\n".$tableCreate.";\n\n";
      
      
      if($divided<1){
      	$result = R::getAll('SELECT * FROM '.$table.'');
      	$write = $writeHeader;
      	foreach($result as $row)
      	{
      		$write .= 'INSERT INTO '.$table.' VALUES(';
      		$parts = array();
      	
      		foreach($fields as $key => $field)
      		{
      			if($row[$key] == null)
      				$parts[] = 'NULL';
      			else
      				$parts[] = '"'.$row[$key].'"';
      		}
      	
      		$write .=  implode(",", $parts) . ");\n";
      		$parts = null;
      	}
      	
      	$write .="\n\n\n";
      	
      	file_put_contents($filename, $write, FILE_APPEND | LOCK_EX);
      	
      }else{
      	$i=0;

      	
      	while ($i<$divided) {
      		$write=null;
      		$write= $writeHeader;
      		$where = null;
      		if($i == 0){
      			$where = '  limit '.$chunkSize;
      		}else{
      			$where = ' WHERE ID > '. $lastId . ' limit '.$chunkSize;
      		}
      		
      		$query = 'SELECT * FROM '.$table.' '.$where;
      		
      		$result = R::getAll($query);
      		
      		$lastRecord = end($result);
      		$lastId = $lastRecord['id'];
      		
      		foreach($result as $row)
      		{
      			$write .= 'INSERT INTO '.$table.' VALUES(';
      			$parts = array();
      		
      			foreach($fields as $key => $field)
      			{
      				if($row[$key] == null)
      					$parts[] = 'NULL';
      				else
      					$parts[] = '"'.$row[$key].'"';
      			}
      		
      			$write .=  implode(",", $parts) . ");\n";
      			$parts = null;
      		}

      		$write .= '// chunk:'.$i;
      		$i++;
      		
      		file_put_contents($filename, $write, FILE_APPEND | LOCK_EX);
      	}
      	
      }

      

      
      
      
      
    }
  }
}

// add plugin to RedBean facade
R::ext( 'performWSLBackup', function($outputFolder, $backupName = "auto") {
    return RedBean_WSLBackup::performWSLBackup($outputFolder, $backupName);
} 
);