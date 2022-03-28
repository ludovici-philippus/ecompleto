<?php 
	class SQL{
		public static $pdo;
		public static function connect(){
			if(self::$pdo == null){
				self::$pdo = new PDO("pgsql:host=localhost;port=5432;dbname=postgres;user=postgres;password=100303");
			}
			return self::$pdo;
		}
	}
?>
