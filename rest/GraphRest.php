<?php
class GraphRest {
	private $graphService;
	private $config;
	
	/**
	 * Constructor
	 */
	function __construct() {
		// set config for this rest call
		$this->config = Session::getConfig();
		$this->graphService = new GraphService($this->config);
	}
	
	/**
	 * Destructor
	 */
	function __destruct() {
		$this->graphService = null;
	}

	public function DELETE($request, $options) {
		if($options[0]=="axe" && $options[1]>0){
			$graph = R::findOne('graph',' name = "daily" ');
			unset($graph->sharedAxe[$options[1]]);
			R::store($graph);
			return array('type'=>'axe','success'=>true);
		}
	}
	
	/**
	 * Rest functions 
	 */
	public function GET($request, $options) {
		if($options[0]=="daily"){
			$graphOptions['deviceNum'] = $options[3];
			$graphOptions['mode'] = $options[4];
			$graphOptions['date'] = $options[2];
			$graphOptions['type'] = $options[1];
			// move this config to others parts of WSL.
			$graphOptions['config'] = $this->config;
			
			$data = $this->graphService->loadGraph($graphOptions);
			return $data;
		}
		$option = "";
		if (count($options) > 0) {
			$option = (trim($options[0]) != "") ? $options[0] : $option;
		}
		$id = -1;
		if (count($options) > 0) {
			$id = (trim($options[0]) != "") ? strtolower($options[0]) : $type;
		}
		if ($id > 0) {
			$graphOptions['deviceNum'] = $id;
			$graphOptions['mode'] = 'edit';
			$graphOptions['type'] = '';
			$graphOptions['config'] = $this->config;
			$data = $this->graphService->loadGraph($graphOptions);

			return $data;
		}

	}
	
	/**
	 * Rest functions
	 */
	public function POST($request, $options) {
	if($request[0]=="Graph" && $request[1]=="saveSerie"){
			$graph = R::findOne('graph',' name = ?',array($_POST['graphName']));
			$links = $graph->ownGraph_series;
			foreach ($links as $link){
				if($link['id']==$_POST['id']){
					
					$json = json_decode($link->json);
					$json->yaxis = $_POST['yaxis'];
					
					$link->json = json_encode($json);
					$link->show = $_POST['serieHidden'];
					$link->disabled = $_POST['serieVisible'];
				}
			}
			R::store($graph);
			return true;
		}
		if($request[0]=="Graph" && $request[1]=="saveAxe"){
			echo 'saveAxe!!  ';
			$graph = R::findOne('graph',' name = ?',array($_POST['graphName']));
			$links = $graph->ownAxes_graph;
			foreach ($links as $link){
				if($link['id']==$_POST['id']){
					echo 'aaa  ';
					$json = json_decode($link->json);
					echo $json->label." ";
					$json->label = $_POST['label'];
					echo $json->label." ";
					$link->json = json_encode($json);
					/*
					$link->show = $_POST['serieHidden'];
					$link->disabled = $_POST['serieVisible'];*/
				}
			}
			R::store($graph);
			return true;
		}
	}
	
	public function getGraphs() {
		$result = array();
		foreach ($this->graphService->getAllGraphs() as $graph) {
			$result[] = array("id"=>$graph->id, "name"=>$graph->name);
		}
		return $result;
	}
	
	
}
?>