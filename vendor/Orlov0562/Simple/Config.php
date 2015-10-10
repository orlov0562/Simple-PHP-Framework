<?php
	namespace Orlov0562\Simple;
	
	class Config {
		
		private static $resultsCache = [];
		private static $configsCache = [];
		private static $configDir = './';
		public static function setConfigDir($dir) {
			if (!is_dir($dir)) throw new \Exception('Can\'t find specified configs dir "'.$dir.'"');
			self::$configDir = $dir;
		}
		
		public static function get($path) {
			if (isset($resultsCache[$path])) return $resultsCache[$path];
		
			$parts = self::getParts($path);
			$filepath = self::getFilePath($parts);
			
			if (isset($configsCache[$filepath])) {
				$val = $configsCache[$filepath];
			} else {
				if (!file_exists($filepath)) throw new \Exception('Config file for path "'.$path.'" not found');
				$val = include $filepath;
				$configsCache[$filepath] = $val;
			}
			
			if (count($parts)>1) {
				for ($i=1; $i<count($parts); $i++) {
					if (is_array($val) AND isset($val[$parts[$i]])) {
						$val = $val[$parts[$i]];
					} else {
						throw new \Exception('Config path "'.$path.'" not found');			
					}
				}
			}
			
			$resultsCache[$path] = $val;
			
			return $val;	
		}
		
		private static function getParts($path, $delimiter='.') {
			return explode($delimiter, $path);
		}
		
		private static function getFilePath(array $parts) {
			return self::$configDir.$parts[0].'.php';
		}
	}
