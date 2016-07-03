<?php
class SystemPhotosService {
	
	function __construct() {
		$this->config = Session::getConfig();
	}


	/**
	 * Load an fotos
	 * @param int $id
	 * @return fotos
	 */
	public function loadSystemPhotos() {
		$photoDir = 'systemPhotos/';
		$photos = scandir($photoDir);
		
		foreach ($photos as $photo){
			$path_parts = pathinfo($photo);
			if(in_array(strtolower($path_parts['extension']),array('jpg','jpeg','png'))){
				$path_parts['dirname'] = $photoDir;
				$path_parts['fileTitle'] = str_replace('-', ' ', $path_parts['filename']);
				$validImages['systemPhotos'][] = $path_parts;
			}
		}
		$countPhotos = count($validImages['systemPhotos']);
		
		$validImages['photoCount'] = (count(Photos)==0) ? 0 : $countPhotos;  
		$validImages['photoDir'] = $photoDir;
		
		return $validImages;

	}
}
?>